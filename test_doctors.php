<?php
require 'includes/db.php';
$sql = "SELECT DISTINCT higher_education FROM users WHERE 
    higher_education LIKE '%Doctor%' OR higher_education LIKE '%MBBS%' OR higher_education LIKE '%BDS%' OR higher_education LIKE '%MD %' OR higher_education LIKE '%M.D%' OR higher_education LIKE '%MD(%' OR higher_education = 'MD' OR higher_education LIKE '%Surgery%' OR higher_education LIKE '%Surgeon%' OR higher_education LIKE '%BAMS%' OR higher_education LIKE '%BHMS%' OR higher_education LIKE '%MDS%' OR higher_education LIKE '%Dentist%' OR higher_education LIKE '%Medical%'
";
$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['higher_education'] . "\n";
}
