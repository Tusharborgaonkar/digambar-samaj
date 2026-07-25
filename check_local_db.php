<?php
require_once 'includes/db.php';
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Local users columns:\n";
print_r($columns);
