<?php
$file = 'c:/xampp/htdocs/digambar-samaj/edit-profile.php';
$content = file_get_contents($file);

// Remove all value="<php htmlspecialchars...>" duplicates and keep only one clean one
$content = preg_replace('/(value=\"<\?= htmlspecialchars[^>]+>\")\s*(value=\"<\?= htmlspecialchars[^>]+>\")\s*(value=\"[^\"]*\")?/', '$1 ', $content);
$content = preg_replace('/(value=\"<\?= htmlspecialchars[^>]+>\")\s*(value=\"[^\"]*\")/', '$1 ', $content);

file_put_contents($file, $content);
echo "Cleaned!";
