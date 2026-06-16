<?php
require_once 'includes/db.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS account_requests (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        request_type ENUM('deactivation', 'deletion') DEFAULT 'deletion',
        reason TEXT,
        status ENUM('pending', 'processed', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
