<?php
require 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT * FROM gallery");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
