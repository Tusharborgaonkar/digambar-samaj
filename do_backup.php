<?php
$source = __DIR__;
$destination = dirname(__DIR__) . '/digambar-samaj-backup.zip';

if (file_exists($destination)) {
    unlink($destination);
}

$zip = new ZipArchive();
if (!$zip->open($destination, ZipArchive::CREATE)) {
    die("Failed to create zip file.");
}

$source = str_replace('\\', '/', realpath($source));
if (is_dir($source) === true) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
        $file = str_replace('\\', '/', $file);

        if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
            continue;

        $file = realpath($file);

        if (is_dir($file) === true) {
            $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
        } else if (is_file($file) === true) {
            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
        }
    }
}
$zip->close();
echo "Backup created successfully at " . $destination;
?>
