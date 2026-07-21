<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_status = 'approved' AND payment_screenshot IS NOT NULL");
    echo "Approved with screenshot: " . $stmt->fetchColumn() . "\n";
    
    $stmt2 = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_status = 'approved'");
    echo "Approved total: " . $stmt2->fetchColumn() . "\n";
    
    $stmt3 = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_status = 'pending'");
    echo "Pending total: " . $stmt3->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
