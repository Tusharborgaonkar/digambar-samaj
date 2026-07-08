<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
include 'includes/db.php';

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

$is_rate_limited = false;
$time_since_last_attempt = time() - $_SESSION['last_login_attempt'];
if ($_SESSION['login_attempts'] >= 5 && $time_since_last_attempt < 300) {
    $is_rate_limited = true;
    $error = "Too many failed attempts. Please try again in " . ceil((300 - $time_since_last_attempt) / 60) . " minutes.";
} elseif ($time_since_last_attempt >= 300) {
    $_SESSION['login_attempts'] = 0;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_rate_limited) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid request. Please refresh and try again.";
    } else {
        $email_or_mobile = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_STRING);
        $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR mobile = ?");
        $stmt->execute([$email_or_mobile, $email_or_mobile]);
        $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['login_attempts'] = 0;

                if ($user['status'] === 'account_pending' || $user['status'] === 'pending') {
                    header('Location: waiting-approval.php');
                    exit;
                } elseif ($user['status'] === 'account_approved') {
                    header('Location: registration.php');
                    exit;
                }

                header('Location: index.php');
                exit;
            } else {
                $error = "Invalid email/mobile or password.";
                $_SESSION['login_attempts']++;
                $_SESSION['last_login_attempt'] = time();
            }
        } catch (PDOException $e) {
            error_log("Login failed: " . $e->getMessage());
            $error = "Login failed. Please try again later.";
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-dark" data-aos="fade-up">
                Sign in to your account
            </h2>
        </div>
        
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md" data-aos="fade-up" data-aos-delay="200">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-100">
                <form class="space-y-6" action="login.php" method="POST">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email address or Mobile Number
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="text" autocomplete="email" required class="appearance-none block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none block w-full pl-10 pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword()">
                                <i id="toggle-icon" class="fas fa-eye text-gray-400 hover:text-gray-600 transition"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Remember me
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="forgot-password.php" class="font-medium text-primary hover:text-opacity-80 transition">
                                Forgot your password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                            Sign in
                        </button>
                    </div>
                </form>
                
                    <div class="mt-4 text-center text-sm">
                        Don't have an account? 
                        <a href="pre-register.php" class="font-bold text-primary hover:underline">
                            Sign up here
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
