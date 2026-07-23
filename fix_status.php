<?php
require 'includes/db.php';
try {
    $stmt = $pdo->query("UPDATE users SET status = 'account_approved' WHERE status = 'pending'");
    echo "<h1>Fixed " . $stmt->rowCount() . " users!</h1>";
    echo "<p>Your account status has been reset. You can now access the registration form again.</p>";
    echo "<a href='registration.php'>Go to Registration</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
