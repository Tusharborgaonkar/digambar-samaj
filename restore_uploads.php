<?php
$zip_file = __DIR__ . '/uploads_backup.zip';
$extract_to = __DIR__ . '/uploads';

if (!file_exists($zip_file)) {
    die("Backup file (uploads_backup.zip) not found!");
}

if (!is_dir($extract_to)) {
    mkdir($extract_to, 0755, true);
}

$zip = new ZipArchive;
if ($zip->open($zip_file) === TRUE) {
    $zip->extractTo($extract_to);
    $zip->close();
    echo "<h2>Restore Successful!</h2>";
    echo "<p>All images have been restored to the uploads directory.</p>";
    echo "<p style='color: red;'>Please delete this script (restore_uploads.php) and the zip file for security.</p>";
} else {
    echo "<h2>Failed to open the zip file.</h2>";
}
?>
