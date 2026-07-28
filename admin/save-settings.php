<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';

// Security: only admin can save settings
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Make sure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value LONGTEXT NULL
    )");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle File Uploads
    if (isset($_FILES['payment_qr_code_file']) && $_FILES['payment_qr_code_file']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['payment_qr_code_file']['tmp_name']);
        $mime_type = mime_content_type($_FILES['payment_qr_code_file']['tmp_name']);
        if ($image_data !== false && strpos($mime_type, 'image/') === 0) {
            $base64 = base64_encode($image_data);
            $qr_path = 'data:' . $mime_type . ';base64,' . $base64;
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute(['payment_qr_code', $qr_path, $qr_path]);
        }
    }

    if (isset($_FILES['hero_banner_file']) && $_FILES['hero_banner_file']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['hero_banner_file']['tmp_name']);
        $mime_type = mime_content_type($_FILES['hero_banner_file']['tmp_name']);
        if ($image_data !== false && strpos($mime_type, 'image/') === 0) {
            $base64 = base64_encode($image_data);
            $hero_path = 'data:' . $mime_type . ';base64,' . $base64;
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute(['hero_banner', $hero_path, $hero_path]);
        }
    }

    foreach ($_POST as $key => $value) {
        // Sanitize key (only allow alphanumeric and underscore)
        $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
        if (empty($key)) continue;
        try {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        } catch (Exception $e) {
            // Log error if needed
        }
    }
    header("Location: settings.php?msg=saved");
    exit;
}
