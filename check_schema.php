<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SHOW COLUMNS FROM users");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $columns);
?>
