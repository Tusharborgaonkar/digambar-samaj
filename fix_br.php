<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'hero_heading'");
$val = $stmt->fetchColumn();
if ($val) {
    // replace literal <br> or <br/> with newline
    $new_val = preg_replace('/<br\s*\/?>/i', "\n", $val);
    $stmt2 = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'hero_heading'");
    $stmt2->execute([$new_val]);
    echo "Fixed hero_heading\n";
}

$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'hero_description'");
$val = $stmt->fetchColumn();
if ($val) {
    // replace literal <br> or <br/> with newline
    $new_val = preg_replace('/<br\s*\/?>/i', "\n", $val);
    $stmt2 = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'hero_description'");
    $stmt2->execute([$new_val]);
    echo "Fixed hero_description\n";
}
echo "Done";
