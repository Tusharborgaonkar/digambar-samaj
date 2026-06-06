<?php
require_once 'includes/db.php';

try {
    $sql = "ALTER TABLE users MODIFY COLUMN status ENUM('pending','approved','rejected','blocked') DEFAULT 'pending'";
    $pdo->exec($sql);
    echo "Successfully updated users table status ENUM.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
