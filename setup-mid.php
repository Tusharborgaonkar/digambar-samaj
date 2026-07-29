<?php
require_once 'includes/db.php';

try {
    $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX idx_profile_id (profile_id)");
    echo "UNIQUE constraint added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
        echo "UNIQUE constraint already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
