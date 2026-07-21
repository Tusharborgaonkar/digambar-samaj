<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_screenshot IS NOT NULL");
    echo "Total users with screenshots: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
