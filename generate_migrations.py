import re

with open('c:/xampp/htdocs/digambar-samaj/database.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

# Find all CREATE TABLE IF NOT EXISTS blocks
# This regex searches for the table name, and everything inside the outer parentheses
pattern = re.compile(r'CREATE TABLE IF NOT EXISTS\s+`?(\w+)`?\s*\((.*?)\)(?:\s*ENGINE.*?)?;', re.DOTALL | re.IGNORECASE)

output = "-- =============================================================================\n"
output += "-- FULL PRODUCTION SYNC MIGRATIONS (Run these if updating an existing database)\n"
output += "-- =============================================================================\n"
output += "-- Note: MariaDB supports 'IF NOT EXISTS' for ADD COLUMN. \n"
output += "-- This block ensures every single column across all tables exists.\n\n"

for match in pattern.finditer(sql):
    table_name = match.group(1)
    columns_raw = match.group(2)
    
    # Simple line-by-line parsing
    lines = columns_raw.split('\n')
    alter_lines = []
    
    for line in lines:
        line = line.strip()
        if not line or line.startswith('--'):
            continue
        
        # Skip constraints
        if re.match(r'^(PRIMARY KEY|FOREIGN KEY|INDEX|UNIQUE KEY|KEY|UNIQUE INDEX|\))', line, re.IGNORECASE):
            continue
            
        # Remove trailing comma
        if line.endswith(','):
            line = line[:-1].strip()
            
        if line:
            alter_lines.append(f"    ADD COLUMN IF NOT EXISTS {line}")

    if alter_lines:
        output += f"ALTER TABLE `{table_name}` \n" + ",\n".join(alter_lines) + ";\n\n"

with open('c:/xampp/htdocs/digambar-samaj/migrations_generated.sql', 'w', encoding='utf-8') as f:
    f.write(output)

print("Done")
