<?php
require_once 'includes/db.php';

try {
    $pdo->exec("ALTER TABLE success_stories CHANGE state city VARCHAR(255)");
    echo "Column 'state' renamed to 'city'.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
