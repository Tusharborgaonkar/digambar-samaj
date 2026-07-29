<?php
require_once 'includes/db.php';
$stmt = $pdo->prepare('SELECT id, email, mobile, password_hash, has_set_password FROM users WHERE email = ?');
$stmt->execute(['sankesarajainam@gmail.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($user);
echo "</pre>";
