<?php
// sync_database.php
// RUN THIS ONCE ON HOSTINGER TO SYNC DATABASE SCHEMAS
require_once 'includes/db.php';

echo "<h2>Starting Database Sync...</h2>";
echo "<div style='background: #f4f4f4; padding: 20px; font-family: monospace;'>";

function safeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ SUCCESS: $description</p>";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Duplicate column name') !== false || strpos($msg, 'already exists') !== false) {
            echo "<p style='color: orange;'>⚠️ SKIPPED (Already exists): $description</p>";
        } else {
            echo "<p style='color: red;'>❌ ERROR: $description - $msg</p>";
        }
    }
}

// 1. Users table updates
safeQuery($pdo, "ALTER TABLE users ADD COLUMN payment_screenshot VARCHAR(255) NULL AFTER profile_photo", "Add payment_screenshot to users");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN payment_transaction_id VARCHAR(100) NULL AFTER payment_screenshot", "Add payment_transaction_id to users");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN payment_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER payment_transaction_id", "Add payment_status to users");
safeQuery($pdo, "ALTER TABLE users MODIFY COLUMN status ENUM('account_pending','account_approved','pending','approved','rejected','blocked') DEFAULT 'account_pending'", "Update users status ENUM");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN father_occupation_other VARCHAR(255) NULL", "Add father_occupation_other to users");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN occupation_other VARCHAR(255) NULL", "Add occupation_other to users");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN mother_occupation_other VARCHAR(255) NULL", "Add mother_occupation_other to users");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN language_other VARCHAR(255) NULL", "Add language_other to users");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN is_digambar VARCHAR(10) NULL", "Add is_digambar to users");
safeQuery($pdo, "ALTER TABLE users ADD COLUMN registration_step INT DEFAULT 1", "Add registration_step to users");

// 2. Gallery table
safeQuery($pdo, "CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NULL,
    category VARCHAR(100) DEFAULT 'All Photos',
    image_path VARCHAR(255) NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)", "Create gallery table");
safeQuery($pdo, "ALTER TABLE gallery ADD COLUMN category VARCHAR(100) DEFAULT 'All Photos'", "Add category to gallery");

// 3. Success Stories table
safeQuery($pdo, "ALTER TABLE success_stories ADD COLUMN display_order INT DEFAULT 0", "Add display_order to success_stories");
safeQuery($pdo, "ALTER TABLE success_stories ADD COLUMN city VARCHAR(255)", "Add city to success_stories");
try {
    $pdo->exec("ALTER TABLE success_stories DROP COLUMN state");
} catch(Exception $e) {}

// 4. Site Settings
safeQuery($pdo, "ALTER TABLE site_settings ADD COLUMN terms_conditions LONGTEXT NULL", "Add terms_conditions to site_settings");
safeQuery($pdo, "ALTER TABLE site_settings ADD COLUMN privacy_policy LONGTEXT NULL", "Add privacy_policy to site_settings");
safeQuery($pdo, "ALTER TABLE site_settings ADD COLUMN about_us LONGTEXT NULL", "Add about_us to site_settings");

// 5. Payments table
safeQuery($pdo, "ALTER TABLE payments ADD COLUMN full_name VARCHAR(255) NULL", "Add full_name to payments");
safeQuery($pdo, "ALTER TABLE payments ADD COLUMN phone_number VARCHAR(20) NULL", "Add phone_number to payments");
safeQuery($pdo, "ALTER TABLE payments ADD COLUMN email VARCHAR(255) NULL", "Add email to payments");
safeQuery($pdo, "ALTER TABLE payments ADD COLUMN address TEXT NULL", "Add address to payments");
safeQuery($pdo, "ALTER TABLE payments ADD COLUMN dob DATE NULL", "Add dob to payments");
safeQuery($pdo, "ALTER TABLE payments ADD COLUMN transaction_id VARCHAR(255) NULL", "Add transaction_id to payments");

// Handle rename if they previously had email_address from a wrong migration
try {
    $pdo->exec("ALTER TABLE payments CHANGE email_address email VARCHAR(255) NULL");
    echo "<p style='color: green;'>✅ SUCCESS: Renamed email_address to email in payments</p>";
} catch (Exception $e) {}

// 6. Contact Messages
safeQuery($pdo, "ALTER TABLE contact_messages ADD COLUMN phone VARCHAR(50) AFTER email", "Add phone to contact_messages");

echo "</div>";
echo "<h3 style='color: blue;'>Sync Complete! Please review the output above. Once confirmed, you can safely delete this file.</h3>";
?>
