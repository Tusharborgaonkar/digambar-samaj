<?php
require 'includes/db.php';
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "USERS TABLE COLUMNS:\n";
foreach($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
