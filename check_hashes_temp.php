<?php
require_once 'includes/db.php';
$stmt = $pdo->query('SELECT COUNT(*) as total, SUM(CASE WHEN password_hash IS NOT NULL AND password_hash != "" THEN 1 ELSE 0 END) as with_hash FROM users');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($result);
echo "</pre>";
