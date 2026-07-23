<?php
// fix_visitors.php - Upload this file to your production root folder and visit it in your browser
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';

echo "<h2>Visitor Counter Diagnostic Script</h2>";

try {
    // Check if the setting exists
    $stmtCheck = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'visitor_count'");
    if (!$stmtCheck) {
        throw new Exception("Query failed to execute. Check if site_settings table exists.");
    }
    
    $vrow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($vrow !== false) {
        echo "<p>Visitor count already exists in database. Current Value: <strong>" . htmlspecialchars($vrow['setting_value']) . "</strong></p>";
        
        // Try to manually increment it
        $new_count = intval($vrow['setting_value']) + 1;
        $affected = $pdo->exec("UPDATE site_settings SET setting_value = '$new_count' WHERE setting_key = 'visitor_count'");
        echo "<p>Increment attempt executed. Rows affected: $affected. New Value should be: $new_count</p>";
    } else {
        echo "<p>Visitor count setting does not exist yet. Attempting to insert...</p>";
        $affected = $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES ('visitor_count', '1')");
        echo "<p>Insert executed. Rows affected: $affected</p>";
    }
    
    // Verify final state
    $stmtVerify = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'visitor_count'");
    echo "<p>Final Value in Database: <strong>" . htmlspecialchars($stmtVerify->fetchColumn()) . "</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>General Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p><em>Once the counter is working, you can delete this file from your server.</em></p>";
?>
