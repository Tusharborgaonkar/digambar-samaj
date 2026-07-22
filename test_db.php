<?php
$file = 'c:/xampp/apache/logs/error.log';
$size = filesize($file);
$f = fopen($file, 'r');
if ($size > 10000) {
    fseek($f, $size - 10000);
}
echo "<pre>";
echo htmlspecialchars(fread($f, 10000));
echo "</pre>";
fclose($f);
