<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simple rate limiting logic
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_login_attempt'])) {
    $_SESSION['last_login_attempt'] = time();
}

// Check if rate limited
$is_rate_limited = false;
$time_since_last_attempt = time() - $_SESSION['last_login_attempt'];
if ($_SESSION['login_attempts'] >= 5 && $time_since_last_attempt < 300) { // 5 minutes lock
    $is_rate_limited = true;
    $error = "Too many failed attempts. Please try again in " . ceil((300 - $time_since_last_attempt) / 60) . " minutes.";
} elseif ($time_since_last_attempt >= 300) {
    // Reset after timeout
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_rate_limited) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid request. Please refresh and try again.";
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Please enter both email and password.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, status FROM admins WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    if ($admin['status'] == 1) {
                        // Success: Regenerate session ID
                        session_regenerate_id(true);

                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_name'] = $admin['name'];
                        $_SESSION['admin_role'] = $admin['role'];

                        // Reset rate limiting
                        $_SESSION['login_attempts'] = 0;

                        // Update last login
                        $update_stmt = $pdo->prepare("UPDATE admins SET last_login = NOW(), last_login_ip = :ip WHERE id = :id");
                        $update_stmt->execute([
                            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                            'id' => $admin['id']
                        ]);

                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Your account has been deactivated. Please contact the super admin.";
                        $_SESSION['login_attempts']++;
                        $_SESSION['last_login_attempt'] = time();
                    }
                } else {
                    $error = "Invalid email or password.";
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_login_attempt'] = time();
                }
            } catch (PDOException $e) {
                // Log actual error and show generic one
                error_log("Login error: " . $e->getMessage());
                $error = "An error occurred during login. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Login - Jain Digambar Matrimony</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4338CA',     // Indigo 700
                        secondary: '#DB2777',   // Pink 600
                        accent: '#8B5CF6',      // Violet 500
                        dark: '#1E293B',        // Slate 800
                        light: '#F8FAFC',       // Slate 50 for admin background
                        admin_sidebar: '#0F172A',// Slate 900 for sidebar
                    },
                    fontFamily: {
                        'sans': ['system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($error)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?= htmlspecialchars(addslashes($error)) ?>'
                });
            });
        </script>
    <?php endif; ?>
</head>
<body class="bg-light h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        
        <!-- Header Section -->
        <div class="bg-primary text-white p-8 text-center">
            <h1 class="text-3xl font-bold tracking-wide mb-1">Jain Digambar</h1>
            <p class="text-accent uppercase tracking-widest text-sm font-semibold mb-4">Admin Portal</p>
            <p class="text-gray-300 text-sm">Sign in to manage the matrimony platform</p>
        </div>

        <!-- Form Section -->
        <div class="p-8">

            <form action="login.php" method="POST" class="space-y-6">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition outline-none"
                               placeholder="admin@example.com">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               class="w-full pl-10 pr-10 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition outline-none"
                               placeholder="••••••••">
                        
                        <!-- Toggle Password Visibility -->
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="remember" class="ml-2 text-gray-600">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="text-primary hover:underline font-medium">Forgot password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-bold shadow-md hover:bg-opacity-90 transition transform hover:-translate-y-0.5 active:translate-y-0">
                    Sign In to Dashboard
                </button>
            </form>
        </div>
        
    </div>

    <script>
        // Password toggle logic
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');
            const icon = togglePassword.querySelector('i');

            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Toggle the eye icon
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>
