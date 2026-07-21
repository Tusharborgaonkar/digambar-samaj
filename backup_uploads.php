<?php
$dir = __DIR__ . '/uploads';
$zip_file = __DIR__ . '/uploads_backup.zip';

if (!is_dir($dir)) {
    die("Uploads directory does not exist.");
}

$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot open <$zip_file>\n");
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($dir) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();
echo "<h2>Backup Successful!</h2>";
echo "<p>Backup created at: <a href='uploads_backup.zip'>uploads_backup.zip</a></p>";
echo "<p>Please download this file to your computer immediately for safekeeping!</p>";
?>
