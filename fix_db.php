<?php
require 'includes/db.php';
try {
    $pdo->exec("DROP TABLE IF EXISTS site_settings");
    $pdo->exec("CREATE TABLE site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value LONGTEXT NULL
    )");
    echo "Successfully recreated site_settings as key-value.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
