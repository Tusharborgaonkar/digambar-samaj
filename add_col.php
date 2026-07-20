<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN payment_screenshot VARCHAR(255) NULL AFTER profile_photo");
    echo "Column added.";
} catch (PDOException $e) {
    echo "Error (might already exist): " . $e->getMessage();
}
?>
