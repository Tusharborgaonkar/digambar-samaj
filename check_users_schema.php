<?php
require_once 'includes/db.php';
$stmt = $pdo->query('DESCRIBE users');
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($result);
echo "</pre>";
