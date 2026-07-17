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
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $ext = pathinfo($_FILES['payment_qr_code_file']['name'], PATHINFO_EXTENSION);
        $filename = 'qr_code_' . time() . '.' . $ext;
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['payment_qr_code_file']['tmp_name'], $destination)) {
            $qr_path = 'uploads/' . $filename; // Relative to front-end root
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute(['payment_qr_code', $qr_path, $qr_path]);
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
