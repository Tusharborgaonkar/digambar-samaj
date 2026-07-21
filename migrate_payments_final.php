<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT id, payment_transaction_id, payment_status, created_at, payment_screenshot FROM users WHERE payment_screenshot IS NOT NULL AND payment_screenshot != ''");
    $users = $stmt->fetchAll();
    
    $count = 0;
    foreach ($users as $u) {
        $check = $pdo->prepare("SELECT id FROM payments WHERE user_id = ? AND payment_method = 'Screenshot'");
        $check->execute([$u['id']]);
        if ($check->rowCount() == 0) {
            $status = ($u['payment_status'] === 'approved' || $u['payment_status'] === 'account_approved') ? 'verified' : (($u['payment_status'] === 'rejected') ? 'rejected' : 'pending');
            $insert = $pdo->prepare("INSERT INTO payments (user_id, transaction_id, payment_method, payment_screenshot, status, created_at) VALUES (?, ?, 'Screenshot', ?, ?, ?)");
            $insert->execute([$u['id'], $u['payment_transaction_id'], $u['payment_screenshot'], $status, $u['created_at']]);
            $count++;
        }
    }
    echo "Migrated $count records to payments table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
