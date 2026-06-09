<?php
require_once 'includes/db.php';

try {
    $pdo->exec("ALTER TABLE success_stories ADD COLUMN state VARCHAR(255) AFTER couple_name");
    echo "Column 'state' added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
         echo "Column 'state' already exists.\n";
    } else {
         echo "Error: " . $e->getMessage() . "\n";
    }
}

if (!is_dir('uploads/success_stories')) {
    mkdir('uploads/success_stories', 0777, true);
    echo "Directory uploads/success_stories created.\n";
} else {
    echo "Directory uploads/success_stories already exists.\n";
}
?>
