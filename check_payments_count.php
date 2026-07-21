<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_method = 'Screenshot'");
    echo "Total payments with Screenshot: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
