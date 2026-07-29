<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';
$stmt = $pdo->query('SELECT field_key, is_custom FROM registration_fields');
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($fields, JSON_PRETTY_PRINT);
