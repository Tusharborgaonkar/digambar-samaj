<?php
require 'includes/db.php';
$adsStmt = $pdo->query("SELECT * FROM advertisements");
$ads = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

print_r($ads);
