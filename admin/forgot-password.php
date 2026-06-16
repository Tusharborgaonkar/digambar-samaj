<?php
session_start();
require_once '../includes/db.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid security token. Please try again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $error = 'Please enter your administrator email.';
        } else {
            // For security, do not reveal if the admin email exists or not
            // We just show a success message regardless. In a real system,
            // we would check the admins table and send a secure reset link.
            // Since this is a simple stub for admin:
            $success = "If your email is registered as an admin, you will receive a reset link shortly. Alternatively, please contact the Super Admin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password - Jain Digambar</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E65100', // Deep Orange
                        secondary: '#FFB300', // Amber
                        dark: '#3E2723', // Dark Brown
                        light: '#FFF8E1' // Light Yellow
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-dark p-6 text-center">
            <h1 class="text-3xl font-bold text-secondary tracking-wide flex justify-center items-center gap-3">
                <i class="fas fa-om"></i> Jain Digambar
            </h1>
            <p class="text-gray-300 mt-2 text-sm uppercase tracking-wider font-semibold">Admin Panel</p>
        </div>

        <!-- Form Section -->
        <div class="p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Forgot Password</h2>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm" role="alert">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm" role="alert">
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
                <div class="text-center mt-4">
                    <a href="login.php" class="text-primary font-bold hover:underline"><i class="fas fa-arrow-left mr-1"></i> Back to Login</a>
                </div>
            <?php else: ?>
                <form action="forgot-password.php" method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <!-- Email -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            <i class="fas fa-envelope text-gray-400 mr-1"></i> Admin Email Address
                        </label>
                        <input class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition shadow-sm" id="email" name="email" type="email" placeholder="admin@example.com" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button class="w-full bg-primary hover:bg-opacity-90 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-200 shadow-md text-lg" type="submit">
                            Request Reset Link
                        </button>
                    </div>
                </form>
                <div class="text-center mt-6">
                    <a href="login.php" class="text-gray-600 hover:text-primary transition font-medium"><i class="fas fa-arrow-left mr-1"></i> Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
