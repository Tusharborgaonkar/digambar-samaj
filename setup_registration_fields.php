<?php
require_once 'includes/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create registration_fields table
    $sql1 = "CREATE TABLE IF NOT EXISTS registration_fields (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        field_group VARCHAR(100) DEFAULT 'Basic Details',
        field_key VARCHAR(100) NOT NULL UNIQUE,
        field_label VARCHAR(255) NOT NULL,
        field_type VARCHAR(50) DEFAULT 'text', 
        field_options TEXT,
        is_custom BOOLEAN DEFAULT FALSE,
        is_visible BOOLEAN DEFAULT TRUE,
        is_required BOOLEAN DEFAULT FALSE,
        is_core BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql1);

    // 2. Create user_custom_data table
    $sql2 = "CREATE TABLE IF NOT EXISTS user_custom_data (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        field_id BIGINT UNSIGNED NOT NULL,
        field_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (field_id) REFERENCES registration_fields(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_field (user_id, field_id)
    )";
    $pdo->exec($sql2);

    // 3. Seed standard fields
    // Core fields (Cannot be hidden/deleted)
    $core_fields = [
        ['Basic Details', 'full_name', 'Full Name', 'text', '', 0, 1, 1, 1, 1],
        ['Basic Details', 'email', 'Email Address', 'email', '', 0, 1, 1, 1, 2],
        ['Basic Details', 'mobile', 'Mobile Number', 'tel', '', 0, 1, 1, 1, 3],
        ['Basic Details', 'password', 'Password', 'password', '', 0, 1, 1, 1, 4],
    ];

    // Other standard users table fields
    $standard_fields = [
        ['Basic Details', 'profile_created_for', 'Profile Created For', 'dropdown', 'Self,Son,Daughter,Brother,Sister,Relative,Friend', 0, 1, 1, 0, 5],
        ['Basic Details', 'gender', 'Gender', 'dropdown', 'Male,Female', 0, 1, 1, 0, 6],
        ['Basic Details', 'birth_date', 'Date of Birth', 'date', '', 0, 1, 1, 0, 7],
        ['Religious Details', 'are_you_digambar_jain', 'Are you Digambar Jain?', 'dropdown', 'Yes,No', 0, 1, 0, 0, 8],
        ['Religious Details', 'gotra', 'Gotra', 'text', '', 0, 1, 0, 0, 9],
        ['Religious Details', 'manglik', 'Manglik Status', 'dropdown', 'Yes,No', 0, 1, 0, 0, 10],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO registration_fields (field_group, field_key, field_label, field_type, field_options, is_custom, is_visible, is_required, is_core, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($core_fields as $f) {
        $stmt->execute($f);
    }
    foreach ($standard_fields as $f) {
        $stmt->execute($f);
    }

    echo "Tables created and seeded successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
