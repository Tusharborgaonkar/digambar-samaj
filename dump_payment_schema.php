<?php
require 'includes/db.php';
$stmt = $pdo->query("DESCRIBE payments");
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Payments table columns:\n";
print_r($fields);

$stmt2 = $pdo->query("DESCRIBE users");
$fields2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "\nUsers table columns:\n";
print_r($fields2);
?>
