<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN has_set_password TINYINT(1) DEFAULT 0 AFTER password_hash");
    echo "Column has_set_password added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
