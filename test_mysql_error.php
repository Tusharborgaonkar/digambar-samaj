<?php
require_once 'includes/db.php';
// Drop the column first
$pdo->exec("ALTER TABLE users DROP COLUMN is_digambar");
echo "Dropped.\n";

// Try to update it (this should fail)
try {
    $pdo->exec("UPDATE users SET is_digambar = 'Yes' WHERE id = 1");
} catch (Exception $e) {
    echo "UPDATE Error: " . $e->getMessage() . "\n";
}

// Now try to run the exact logic from sync_database.php with a different column name or something, or just run it directly.
require_once 'sync_database.php';
