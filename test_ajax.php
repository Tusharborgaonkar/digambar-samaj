<?php
require_once 'includes/db.php';

// Fetch user 1
$stmt = $pdo->query("SELECT id, full_name, mobile, email, registration_step FROM users ORDER BY id DESC LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Latest user:\n";
print_r($user);

// Let's pretend to submit an AJAX request for Section 1
$_POST = [
    'ajax_save' => 'true',
    'registration_step' => '2',
    'full_name' => 'Test User',
    'mobile' => '9876543210',
    'email' => 'test@example.com',
    'birth_date' => '2000-01-01',
    'pin_code' => '',
    'annual_income' => '',
    'father_income' => '',
    // other fields omitted
];
$_SESSION = ['user_id' => $user['id'], 'user_logged_in' => true];

ob_start();
// Instead of including registration.php directly (which includes db.php again), we can't easily avoid it if registration.php uses `include` instead of `require_once`.
// Let's replace 'include "includes/db.php"' with empty in a temp file.
$regContent = file_get_contents('registration.php');
$regContent = str_replace("include 'includes/db.php';", "", $regContent);
file_put_contents('temp_reg.php', $regContent);

include 'temp_reg.php';
$output = ob_get_clean();

echo "\n\nAJAX Output:\n";
echo $output;
unlink('temp_reg.php');
