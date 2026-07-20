<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
echo "show_home_top_ads: " . ($settings['show_home_top_ads'] ?? 'not set') . "\n";
