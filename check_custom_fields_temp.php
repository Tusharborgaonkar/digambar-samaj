<?php
require 'includes/db.php';
$stmt = $pdo->query('SELECT field_key FROM registration_fields WHERE is_custom = 1');
$fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($fields);
