<?php
// production_cleanup.php
// This script creates a full backup and then deletes all development/test files.

$source = __DIR__;
$destination = dirname(__DIR__) . '/digambar-samaj-backup.zip';

// 1. Create Backup
if (file_exists($destination)) {
    unlink($destination);
}

$zip = new ZipArchive();
if (!$zip->open($destination, ZipArchive::CREATE)) {
    die("<h2>Error: Failed to create zip file at $destination. Please check folder permissions.</h2>");
}

$sourceRealPath = str_replace('\\', '/', realpath($source));
if (is_dir($sourceRealPath) === true) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRealPath), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
        $file = str_replace('\\', '/', $file);
        if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) continue;
        
        $fileRealPath = realpath($file);
        if (is_dir($fileRealPath) === true) {
            $zip->addEmptyDir(str_replace($sourceRealPath . '/', '', $fileRealPath . '/'));
        } else if (is_file($fileRealPath) === true) {
            $zip->addFromString(str_replace($sourceRealPath . '/', '', $fileRealPath), file_get_contents($fileRealPath));
        }
    }
}
$zip->close();

if (!file_exists($destination) || filesize($destination) < 1000) {
    die("<h2>Error: Backup creation failed or file is too small. Aborting cleanup.</h2>");
}

// 2. Perform Cleanup since Backup is verified
$filesToDelete = [
    // SQL files
    'database.sql',
    'migration_add_missing_columns.sql',
    'migrations_generated.sql',
    'prod.sql',
    
    // Python scripts
    'add_custom_field_sections.py',
    'generate_migrations.py',
    
    // Test files
    'test_ads.php', 'test_ads2.php', 'test_ads3.php', 'test_ads4.php', 'test_ads_paths.php',
    'test_db.php', 'test_gallery.php', 'test_likes.php', 'test_payment_query.php',
    'test_payment_query2.php', 'test_payments.php', 'test_payments2.php', 'test_query.php',
    'test_query2.php', 'test_screenshot_path.php', 'test_stories.php',
    'admin/test_admin_ads.php',
    
    // Setup and Dump files
    'add_col.php', 'alter_columns.php', 'alter_contacts.php', 'alter_enum.php', 'alter_payments.php',
    'backup_uploads.php', 'check.php', 'check_db.php', 'check_likes.php', 'check_payments.php',
    'check_payments_count.php', 'check_screenshots.php', 'composer_install.php', 'copy.php',
    'create_likes.php', 'db-test-schema.php', 'db_debug.txt', 'dump_fields.php', 'dump_fields2.php',
    'dump_payment_schema.php', 'dump_schema.php', 'dump_success.php', 'fix.php', 'fix_db.php',
    'fix_html.php', 'generate_migrations.php', 'migrate_members.php', 'migrate_payments.php',
    'migrate_payments2.php', 'migrate_payments_final.php', 'migrate_success_stories.php',
    'migrate_success_stories_city.php', 'reset_admin.php', 'restore_uploads.php',
    'run_migration_payment.php', 'run_migrations.php', 'seed_additional_fields.php',
    'setup_contacts.php', 'setup_fields.php', 'setup_registration_fields.php', 'setup_requests.php',
    'success_dump.txt', 'sync_database.php', 'temp_alter.php',
    'do_backup.php',
    
    // CSV
    'परिचय सम्मेलन 2025-26 फोर्म Parichay Sammelan 2025-26 Form (Responses) - Form Responses 1 (12).csv'
];

$deletedCount = 0;
foreach ($filesToDelete as $f) {
    $path = __DIR__ . '/' . $f;
    if (file_exists($path) && is_file($path)) {
        unlink($path);
        $deletedCount++;
    }
}

// Self-destruct
@unlink(__FILE__);

echo "<h1>Success! Production Cleanup Complete.</h1>";
echo "<p>1. A full backup has been saved to: <strong>" . htmlspecialchars($destination) . "</strong></p>";
echo "<p>2. Deleted <strong>$deletedCount</strong> development and test files securely.</p>";
echo "<p>You can now return to the chat.</p>";
?>
