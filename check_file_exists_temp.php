<?php
$file = 'uploads/1785243911_photo_WhatsApp Image 2026-07-22 at 12.07.59 PM.jpeg';
echo "File: $file\n";
echo "file_exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";
echo "realpath: " . realpath($file) . "\n";
