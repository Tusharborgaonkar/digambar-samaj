<?php
require 'includes/db.php';
$stmt = $pdo->query('SELECT id, full_name, profile_photo FROM users ORDER BY id DESC LIMIT 5');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users, JSON_PRETTY_PRINT);
