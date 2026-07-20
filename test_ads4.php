<?php
require 'includes/db.php';

$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$adsStmt = $pdo->query("SELECT * FROM advertisements WHERE status = 1 ORDER BY id DESC");
$advertisements = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

$home_top_ads = array_filter($advertisements, function($ad) { 
    if ($ad['position'] != 'home_top') return false;
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    if (!$img) return false;
    $img_path = ltrim(str_replace('../', '', $img), '/\\');
    return file_exists(__DIR__ . '/' . $img_path);
});

$show_setting = isset($settings['show_home_top_ads']) ? $settings['show_home_top_ads'] == '1' : true;
$show_section = (!empty($home_top_ads) && $show_setting);

echo "Settings toggle enabled: " . ($show_setting ? "Yes" : "No") . "\n";
echo "Top Ads Count: " . count($home_top_ads) . "\n";
echo "Section Will Show: " . ($show_section ? "Yes" : "No") . "\n";
