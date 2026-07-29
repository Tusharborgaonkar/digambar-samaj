<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE advertisements ADD COLUMN media_type VARCHAR(20) DEFAULT 'image' AFTER image;");
    echo "Successfully added media_type to advertisements.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column media_type already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
