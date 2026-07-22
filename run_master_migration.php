<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';

try {
    $pdo->beginTransaction();

    // 1. news
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS news (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            image VARCHAR(255),
            status BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "news table created.\n";

    // 2. scrolling_news
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS scrolling_news (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            content VARCHAR(500) NOT NULL,
            link VARCHAR(255),
            status BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "scrolling_news table created.\n";

    // 3. committee_members
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS committee_members (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            designation VARCHAR(150),
            description TEXT,
            photo VARCHAR(255),
            sort_order INT DEFAULT 0,
            status BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "committee_members table created.\n";

    // 4. user_relatives
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_relatives (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            relation VARCHAR(100),
            name VARCHAR(255),
            mobile VARCHAR(20),
            occupation VARCHAR(150),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "user_relatives table created.\n";

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

    $pdo->commit();
    echo "All migrations successful!";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
