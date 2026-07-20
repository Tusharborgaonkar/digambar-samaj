<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM advertisements ORDER BY id DESC LIMIT 5");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($ads as $ad) {
    echo "ID: {$ad['id']} | Title: {$ad['title']} | Image: {$ad['image']}\n";
    $path = __DIR__ . '/' . ltrim(str_replace('../', '', $ad['image']), '/\\');
    echo "  Resolved Path: $path\n";
    echo "  Exists: " . (file_exists($path) ? "Yes" : "No") . "\n";
}
