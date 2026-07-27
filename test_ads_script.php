<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM advertisements WHERE position = 'home_bottom'");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "DB Ads:\n";
print_r($ads);

echo "\nFiltering test:\n";
foreach ($ads as $ad) {
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    echo "Img raw: $img\n";
    $img_path = ltrim(str_replace('../', '', $img), '/\\');
    echo "Img path: $img_path\n";
    $full_path = __DIR__ . '/' . $img_path;
    echo "Full path: $full_path\n";
    echo "File exists: " . (file_exists($full_path) ? 'Yes' : 'No') . "\n";
}
?>
