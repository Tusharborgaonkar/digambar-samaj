<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header("HTTP/1.1 403 Forbidden");
    exit('Access Denied');
}

// Get the requested file
$file = $_GET['file'] ?? '';

if (empty($file)) {
    header("HTTP/1.1 400 Bad Request");
    exit('File not specified');
}

// Ensure the path is within allowed directories (uploads/ or imports/)
$allowed_dirs = ['uploads', 'imports'];
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

// Ensure it's an image
if (strpos($mime_type, 'image/') !== 0) {
    header("HTTP/1.1 403 Forbidden");
    exit('Not an image');
}

// Serve the image securely
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($requested_file));
// Optional: add caching headers to reduce server load
header('Cache-Control: private, max-age=86400');
readfile($requested_file);
exit;
