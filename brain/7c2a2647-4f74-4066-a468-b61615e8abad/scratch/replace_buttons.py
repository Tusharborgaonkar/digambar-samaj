import os
import re

files = [
    'admin/members.php',
    'admin/members-approval.php',
    'admin/members-rejected.php',
    'admin/members-blocked.php'
]

actions = {
    'approve': ("Approve Profile?", "You are about to approve this profile.", "success", "Yes, approve it!"),
    'hold': ("Hold Profile?", "You are about to put this profile on hold.", "warning", "Yes, hold it!"),
    'reject': ("Deny Profile?", "You are about to reject this profile.", "warning", "Yes, reject it!"),
    'block': ("Block User?", "You are about to block this user.", "error", "Yes, block them!")
}

for filepath in files:
    if not os.path.exists(filepath):
        continue
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    for val, (title, text, icon, confirm_text) in actions.items():
        # Match `<button type="submit" name="action" value="ACTION" class="...">...<i class="..."></i></button>`
        # We need to inject `onclick="..."` just before the `>` of the opening `<button>` tag, if it doesn't have it already.
        
        # Regex to find the button without onclick
        pattern = re.compile(rf'(<button type="submit" name="action" value="{val}" class="[^"]*" title="[^"]*")(>)')
        
        onclick_code = f""" onclick="event.preventDefault(); Swal.fire({{title: '{title}', text: '{text}', icon: '{icon}', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: '{confirm_text}'}}).then((result) => {{ if (result.isConfirmed) {{ const input = document.createElement('input'); input.type = 'hidden'; input.name = this.name; input.value = this.value; this.form.appendChild(input); this.form.submit(); }} }});\""""
        
        content = pattern.sub(rf'\1{onclick_code}\2', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
