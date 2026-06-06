<?php
include 'includes/db.php';
$stmt = $pdo->query('DESCRIBE users');
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($columns);
?>
