<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("DESCRIBE gallery");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Gallery table error: " . $e->getMessage() . "\n";
}
try {
    $stmt = $pdo->query("DESCRIBE success_stories");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Success stories error: " . $e->getMessage() . "\n";
}
