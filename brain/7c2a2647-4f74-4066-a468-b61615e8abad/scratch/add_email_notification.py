import os
import re

files = [
    'admin/members.php',
    'admin/members-approval.php',
    'admin/members-rejected.php',
    'admin/members-blocked.php'
]

email_code = """        
        $stmtEmail = $pdo->prepare("SELECT email, full_name FROM users WHERE id = ?");
        $stmtEmail->execute([$userId]);
        $user = $stmtEmail->fetch();
        if ($user && !empty($user['email'])) {
            $to = $user['email'];
            $subject = "Profile Approved - Digambar Samaj Matrimony";
            $message = "Dear " . $user['full_name'] . ",\\n\\nCongrats! Your profile has been approved by the admin. You can now visit other profiles and write success stories.\\n\\nBest Regards,\\nDigambar Samaj Matrimony Team";
            $headers = "From: noreply@digambarsamaj.com\\r\\n";
            @mail($to, $subject, $message, $headers);
        }
"""

for filepath in files:
    if not os.path.exists(filepath):
        continue
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Search for:
    #     if ($action === 'approve') {
    #         $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    #         $stmt->execute([$userId]);
    
    pattern = re.compile(r"(\s*if\s*\(\$action\s*===\s*'approve'\)\s*\{\s*\$stmt\s*=\s*\$pdo->prepare\(\"UPDATE users SET status = 'approved' WHERE id = \?\"\);\s*\$stmt->execute\(\[\$userId\]\);)")
    
    # Check if we already injected it
    if "SELECT email, full_name FROM users WHERE id = ?" not in content:
        content = pattern.sub(r'\1' + email_code, content)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
            print(f"Updated {filepath}")
