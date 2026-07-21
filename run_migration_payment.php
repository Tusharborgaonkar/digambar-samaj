<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE payments ADD COLUMN full_name VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE payments ADD COLUMN phone_number VARCHAR(20) NULL");
    $pdo->exec("ALTER TABLE payments ADD COLUMN email_address VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE payments ADD COLUMN address TEXT NULL");
    $pdo->exec("ALTER TABLE payments ADD COLUMN dob DATE NULL");
    echo "payments columns added. ";
} catch(Exception $e) {
    echo "payments error: " . $e->getMessage() . " ";
}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN payment_transaction_id VARCHAR(100) NULL AFTER payment_screenshot");
    echo "users column added. ";
} catch(Exception $e) {
    echo "users error: " . $e->getMessage() . " ";
}
