<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM registration_fields");
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($fields);
?>
