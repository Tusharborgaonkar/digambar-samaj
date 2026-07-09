<?php
echo "<pre>";
putenv('COMPOSER_HOME=' . __DIR__ . '/.composer');
if (!file_exists('composer.phar')) {
    echo "Downloading composer.phar...\n";
    file_put_contents('composer.phar', file_get_contents('https://getcomposer.org/composer.phar'));
}
echo "Running composer.phar...\n";
$output = shell_exec('php -d extension=zip composer.phar require phpoffice/phpspreadsheet --ignore-platform-reqs 2>&1');
echo htmlspecialchars($output);
echo "</pre>";
?>
