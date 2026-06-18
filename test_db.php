<?php
include 'includes/db.php';
$stmt = $pdo->query("SELECT field_key, is_custom, is_visible FROM registration_fields WHERE field_key IN ('subcast', 'mandir', 'ref1_name')");
print_r($stmt->fetchAll());
?>
