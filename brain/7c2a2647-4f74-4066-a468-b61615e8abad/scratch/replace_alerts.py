import re
files = ['registration.php', 'my-profile.php']
for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    # Replace alert('...') with Swal.fire(...)
    content = re.sub(r"alert\((['\"])(.*?)\1\);", r"Swal.fire({icon: 'warning', title: 'Attention', text: \1\2\1});", content)
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
