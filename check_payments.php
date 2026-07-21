<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM payments WHERE payment_method = 'Screenshot'");
    $payments = $stmt->fetchAll();
    echo "Payments from screenshot:\n<br>";
    foreach ($payments as $p) {
        echo "User ID: " . $p['user_id'] . "\n<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
