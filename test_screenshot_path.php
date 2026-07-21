<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT id, payment_screenshot FROM users WHERE payment_screenshot IS NOT NULL LIMIT 5");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
?>
