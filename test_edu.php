<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT DISTINCT higher_education FROM users LIMIT 100");
$arr = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $arr[] = $row['higher_education'];
}
echo json_encode($arr);
