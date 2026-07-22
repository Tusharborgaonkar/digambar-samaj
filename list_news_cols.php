<?php
require_once 'includes/db.php';
$stmt = $pdo->query("DESCRIBE news");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($cols);
echo "</pre>";
?>
