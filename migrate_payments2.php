<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_status = 'approved' AND payment_screenshot IS NOT NULL");
    echo "Approved with screenshot: " . $stmt->fetchColumn() . "\n<br>";
    
    $stmt2 = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_status = 'approved'");
    echo "Approved total: " . $stmt2->fetchColumn() . "\n<br>";
    
    $stmt3 = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_status = 'pending'");
    echo "Pending total: " . $stmt3->fetchColumn() . "\n<br>";
    
    // Also run the migration directly here
    $stmt_users = $pdo->query("SELECT id, payment_transaction_id FROM users WHERE payment_status = 'approved' AND payment_screenshot IS NOT NULL AND id NOT IN (SELECT user_id FROM payments WHERE payment_method = 'Screenshot')");
    $users = $stmt_users->fetchAll();
    
    $count = 0;
    foreach ($users as $u) {
        $insert = $pdo->prepare("INSERT INTO payments (user_id, transaction_id, payment_method, status) VALUES (?, ?, 'Screenshot', 'verified')");
        $insert->execute([$u['id'], $u['payment_transaction_id']]);
        $count++;
    }
    
    echo "Migrated $count records.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
