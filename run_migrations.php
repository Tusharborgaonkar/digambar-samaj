<?php
include 'includes/db.php';

try {
    $pdo->exec("ALTER TABLE site_settings ADD COLUMN terms_conditions LONGTEXT NULL");
    echo "Added terms_conditions.<br>";
} catch (Exception $e) {
    echo "site_settings error: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE site_settings ADD COLUMN privacy_policy LONGTEXT NULL");
    echo "Added privacy_policy.<br>";
} catch (Exception $e) {
    echo "site_settings error: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE site_settings ADD COLUMN about_us LONGTEXT NULL");
    echo "Added about_us.<br>";
} catch (Exception $e) {
    echo "site_settings error: " . $e->getMessage() . "<br>";
}
?>
