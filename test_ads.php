<?php
require 'includes/db.php';
$adsStmt = $pdo->query("SELECT * FROM advertisements ORDER BY id DESC");
$ads = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total ads: " . count($ads) . "\n";
foreach($ads as $ad) {
    echo "ID: " . $ad['id'] . " Status: " . $ad['status'] . "\n";
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    $img_path = ltrim(str_replace('../', '', $img), '/\\');
    $full_path = __DIR__ . '/' . $img_path;
    echo "Img: " . $img . "\n";
    echo "Path: " . $full_path . "\n";
    echo "Exists: " . (file_exists($full_path) ? 'Yes' : 'No') . "\n\n";
}
