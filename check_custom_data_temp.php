<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';
$stmt = $pdo->query('SELECT f.field_key, cd.field_value FROM user_custom_data cd JOIN registration_fields f ON cd.field_id = f.id WHERE cd.user_id = 507');
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data, JSON_PRETTY_PRINT);
