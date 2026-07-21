<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("DESCRIBE payments");
    file_put_contents('schema.json', json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT));
} catch (Exception $e) {
    file_put_contents('schema.json', json_encode(['error' => $e->getMessage()]));
}
