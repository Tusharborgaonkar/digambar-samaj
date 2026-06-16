<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
include 'includes/db.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: my-profile.php");
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$valid_token = false;
$email = '';

if (empty($token)) {
    $error = "Invalid or missing reset token.";
} else {
    // Validate token
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$token]);
    $reset_record = $stmt->fetch();

    if (!$reset_record) {
        $error = "This password reset link is invalid or has expired. Please request a new one.";
    } else {
        $valid_token = true;
        $email = $reset_record['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // Update user password
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $stmt->execute([$password_hash, $email]);

            // Delete token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            $pdo->commit();
            $success = "Your password has been successfully reset. You can now log in.";
            $valid_token = false; // Hide the form
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Failed to reset password: " . $e->getMessage());
            $error = "An error occurred while resetting your password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Jain Digambar Matrimony</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#d97706',
                        secondary: '#92400e'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-amber-50 min-h-screen flex flex-col">

    <?php include 'includes/header.php'; ?>

    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900">Set New Password</h2>
                <?php if ($valid_token): ?>
                <p class="text-gray-600 mt-2">Enter your new password below.</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
                <div class="text-center">
                    <a href="login.php" class="inline-block bg-primary text-white py-2 px-6 rounded-lg hover:bg-secondary transition-colors font-medium">
                        Go to Login
                    </a>
                </div>
            <?php elseif ($valid_token): ?>
                <form action="reset-password.php" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary"
                               placeholder="Min. 8 characters">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary"
                               placeholder="Confirm your password">
                    </div>

                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-secondary transition-colors font-medium">
                        Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <a href="forgot-password.php" class="inline-block bg-gray-200 text-gray-700 py-2 px-6 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Request New Link
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
