<?php
require 'includes/db.php';
try {
    $user_id = 49; // test id that actually exists, looking at screenshot the ID is JDM287.. Wait, I can just query a pending one.
    $stmt = $pdo->query("SELECT id FROM users WHERE payment_status = 'pending' LIMIT 1");
    $user = $stmt->fetch();
    if ($user) {
        $user_id = $user['id'];
        $pdo->beginTransaction();
        $sql = "INSERT INTO payments (user_id, full_name, phone_number, email, address, dob, transaction_id, payment_method, payment_screenshot, status, created_at) SELECT id, full_name, mobile, email, COALESCE(current_address, permanent_address, ''), birth_date, payment_transaction_id, 'Screenshot', payment_screenshot, 'verified', created_at FROM users WHERE id = ?";
        
        $insert = $pdo->prepare($sql);
        $insert->execute([$user_id]);
        echo "Execute successful.\n";
        $pdo->rollBack();
    } else {
        echo "No pending users.\n";
    }
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
?>
