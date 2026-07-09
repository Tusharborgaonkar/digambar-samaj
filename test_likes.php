<?php
require 'includes/db.php';
try {
    $stmt = $pdo->query('SELECT * FROM user_likes');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
