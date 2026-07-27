<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT id, full_name, height, birth_date, mobile FROM users WHERE mobile LIKE '%9426338494%'");
$users = $stmt->fetchAll();
print_r($users);
?>
