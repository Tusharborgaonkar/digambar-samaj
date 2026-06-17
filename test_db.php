<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';
$stmt = $pdo->query("SELECT field_key, is_core FROM registration_fields WHERE field_key IN ('subcast', 'mandir', 'ref1_name')");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) echo $c['field_key']." = ".$c['is_core']."\n";
