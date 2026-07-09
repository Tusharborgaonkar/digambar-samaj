<?php
include 'includes/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        liked_user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (user_id, liked_user_id)
    )");
    echo "Created user_likes table.<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
