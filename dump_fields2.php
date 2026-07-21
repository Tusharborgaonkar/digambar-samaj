<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM registration_fields WHERE field_label LIKE '%Father%' OR field_label LIKE '%Language%' OR field_label LIKE '%Occupation%'");
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($fields);
?>
