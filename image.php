<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Get the requested file
$file = $_GET['file'] ?? '';

if (empty($file)) {
    header("HTTP/1.1 400 Bad Request");
    exit('File not specified');
}

// Allow public access to specific directories without login
$is_public = false;
if (strpos($file, 'uploads/advertisements') === 0 || strpos($file, 'uploads/ads') === 0 || strpos($file, 'uploads/gallery') === 0 || strpos($file, 'uploads/success_stories') === 0 || strpos($file, 'assets/') === 0) {
    $is_public = true;
}

// Ensure user is logged in for protected files
if (!$is_public && !isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header("HTTP/1.1 403 Forbidden");
    exit('Access Denied');
}

// Ensure the path is within allowed directories (uploads/ or imports/ or assets/)
$allowed_dirs = ['uploads', 'imports', 'assets'];
$file_parts = explode('/', str_replace('\\', '/', $file));
$base_dir = $file_parts[0];

if (!in_array($base_dir, $allowed_dirs)) {
    header("HTTP/1.1 403 Forbidden");
    exit('Invalid directory');
}

// Construct absolute real path to prevent path traversal
$real_base = realpath(__DIR__);
$requested_file = realpath($real_base . '/' . $file);

if ($requested_file === false || strpos($requested_file, $real_base) !== 0) {
    header("HTTP/1.1 404 Not Found");
    exit('File not found');
}

if (!file_exists($requested_file)) {
    header("HTTP/1.1 404 Not Found");
    exit('File not found');
}

// Determine content type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $requested_file);
finfo_close($finfo);

// Ensure it's an image or PDF
if (strpos($mime_type, 'image/') !== 0 && $mime_type !== 'application/pdf') {
    header("HTTP/1.1 403 Forbidden");
    exit('Not allowed');
}

// Serve the image securely
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($requested_file));
// Optional: add caching headers to reduce server load
header('Cache-Control: private, max-age=86400');
readfile($requested_file);
exit;
