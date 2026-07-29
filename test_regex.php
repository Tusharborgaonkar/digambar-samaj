<?php
require 'includes/db.php';
$sql = "SELECT DISTINCT higher_education FROM users WHERE 
    higher_education LIKE '%Doctor%' OR 
    higher_education LIKE '%MBBS%' OR 
    higher_education LIKE '%BDS%' OR 
    higher_education REGEXP '(^|[^a-zA-Z])(MD|MS|BAMS|BHMS|MDS|Ph\\\\.?D\\\\.?)([^a-zA-Z]|$)'
";
$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['higher_education'] . "\n";
}
