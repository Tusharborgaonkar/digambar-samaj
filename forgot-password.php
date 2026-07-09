<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
include 'includes/db.php';

// Include custom Mailer
require_once 'includes/Mailer.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email exists in database
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));

            // Ensure password_resets table exists
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            } catch (Exception $e) {}
            
            // Delete any existing tokens for this email
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Insert new token
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
            $stmt->execute([$email, $token]);

            // Create Reset Link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['PHP_SELF']);
            if($path == '\\' || $path == '/') $path = '';
            
            $resetLink = $protocol . $domainName . $path . "/reset-password.php?token=" . $token;

            // Send Email using custom Mailer
            $mailer = new Mailer();
            $emailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2>Password Reset Request</h2>
                    <p>Hello {$user['full_name']},</p>
                    <p>We received a request to reset your password for your Digambar Jain Parichay account.</p>
                    <p>Please click the button below to reset your password. This link will expire in 1 hour.</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' style='background-color: #d97706; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
                    </p>
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p><a href='{$resetLink}'>{$resetLink}</a></p>
                    <p>If you did not request this, please ignore this email.</p>
                    <hr>
                    <p style='font-size: 12px; color: #666;'>Regards,<br>Digambar Jain Parichay Team</p>
                </div>
            ";

            $sent = $mailer->send($email, 'Password Reset Request', $emailBody);
            if ($sent) {
                $success = "A password reset link has been sent to your email address.";
            } else {
                $error = "There was a problem sending the email. Please try again later or contact support.";
            }
        } else {
            $error = "Account not found. There is no user registered with this email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Jain Digambar Matrimony</title>
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
                <h2 class="text-3xl font-bold text-gray-900">Forgot Password</h2>
                <p class="text-gray-600 mt-2">Enter your registered email address and we'll send you a link to reset your password.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php else: ?>
                <form action="forgot-password.php" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary"
                               placeholder="you@example.com">
                    </div>

                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-secondary transition-colors font-medium">
                        Send Reset Link
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Remember your password? <a href="login.php" class="font-medium text-primary hover:text-secondary">Back to Login</a>
                </p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
