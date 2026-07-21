<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("DESCRIBE payments");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Payments table error: " . $e->getMessage() . "\n";
}
