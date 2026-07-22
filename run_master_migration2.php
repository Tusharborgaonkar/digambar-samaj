<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';

try {
    // 5. gallery - check if media_type exists
    $stmt = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'media_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE gallery ADD COLUMN media_type ENUM('image', 'pdf', 'video', 'youtube') DEFAULT 'image'");
        $pdo->exec("ALTER TABLE gallery ADD COLUMN media_url VARCHAR(500) NULL");
        echo "gallery altered.\n";
    }

    // 6. advertisements - alter ENUM
    $pdo->exec("
        ALTER TABLE advertisements 
        MODIFY COLUMN position ENUM('home_top', 'home_bottom', 'sidebar', 'left_sidebar', 'right_sidebar', 'bottom_banner')
    ");
    echo "advertisements altered.\n";

    // 7. site_settings
    $settings = [
        ['home_title', 'Digambar Jain Yuvak-Yuvati Parichay'],
        ['home_tagline', 'Connecting Hearts, Preserving Traditions'],
        ['hero_heading', 'Find Your Perfect Match'],
        ['hero_description', 'Thousands of verified profiles from the Digambar Jain community.'],
        ['hero_banner', ''],
        ['about_us_text', 'Welcome to Digambar Jain Parichay Sammelan Samiti...'],
        ['upi_id', ''],
        ['payment_instructions', 'Scan the QR code to pay using any UPI app.']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $s) {
        $stmt->execute($s);
    }
    echo "site_settings updated.\n";

    echo "All migrations successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
