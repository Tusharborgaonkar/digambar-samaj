<?php
$sql = file_get_contents('c:/xampp/htdocs/digambar-samaj/database.sql');
$parts = explode("CREATE TABLE IF NOT EXISTS", $sql);
array_shift($parts); // Remove the first chunk before the first table

$output = "-- =============================================================================\n";
$output .= "-- FULL PRODUCTION SYNC MIGRATIONS (Run these if updating an existing database)\n";
$output .= "-- =============================================================================\n";
$output .= "-- Note: MariaDB supports 'IF NOT EXISTS' for ADD COLUMN. \n";
$output .= "-- This block ensures every single column across all tables exists.\n\n";

foreach ($parts as $part) {
    // Extract table name
    if (preg_match('/^\s*`?(\w+)`?\s*\((.*?)\)(?:\s*ENGINE.*)?;$/ism', $part, $matches)) {
        $table = $matches[1];
        $columnsRaw = $matches[2];
        
        $lines = explode("\n", $columnsRaw);
        $alterLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '--') === 0) continue;
            if (preg_match('/^(PRIMARY KEY|FOREIGN KEY|INDEX|UNIQUE KEY|KEY|UNIQUE INDEX|\))/i', $line)) continue;
            
            $line = rtrim($line, ',');
            if ($line !== '') {
                $alterLines[] = "    ADD COLUMN IF NOT EXISTS " . $line;
            }
        }
        
        if (!empty($alterLines)) {
            $output .= "ALTER TABLE `$table` \n" . implode(",\n", $alterLines) . ";\n\n";
        }
    }
}
file_put_contents('c:/xampp/htdocs/digambar-samaj/migrations_generated.sql', $output);
echo "Done";
?>
