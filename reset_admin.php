<?php
include 'includes/db.php';

$password = 'admin123';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE email = 'admin@digambar.com'");
    $stmt->execute([$password_hash]);
    echo "Password for admin@digambar.com has been successfully reset to: <strong>admin123</strong><br>";
    echo "<a href='admin/login.php'>Go to Admin Login</a>";
} catch (PDOException $e) {
    echo "Error updating password: " . $e->getMessage();
}
?>
