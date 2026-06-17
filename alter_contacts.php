<?php
require_once __DIR__ . '/includes/db.php';

try {
    $sql = "ALTER TABLE contact_messages ADD COLUMN phone VARCHAR(50) AFTER email";
    $pdo->exec($sql);
    echo "Column 'phone' added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
