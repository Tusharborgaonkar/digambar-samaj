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
if (!isset($_SESSION['register_attempts'])) {
    $_SESSION['register_attempts'] = 0;
}
if (!isset($_SESSION['last_register_attempt'])) {
    $_SESSION['last_register_attempt'] = time();
}

$is_rate_limited = false;
$time_since_last_attempt = time() - $_SESSION['last_register_attempt'];
if ($_SESSION['register_attempts'] >= 5 && $time_since_last_attempt < 300) {
    $is_rate_limited = true;
    $error = "Too many failed attempts. Please try again in " . ceil((300 - $time_since_last_attempt) / 60) . " minutes.";
} elseif ($time_since_last_attempt >= 300) {
    $_SESSION['register_attempts'] = 0;
}

$success = '';
if (empty($error)) {
    $error = '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_rate_limited) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid request. Please refresh and try again.";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $mobile = ($_POST['country_code'] ?? '') . ($_POST['mobile'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
            $_SESSION['register_attempts']++;
            $_SESSION['last_register_attempt'] = time();
        } elseif (!preg_match('/^[0-9]{10}$/', $_POST['mobile'] ?? '')) {
            $error = "Mobile number must be exactly 10 digits.";
            $_SESSION['register_attempts']++;
            $_SESSION['last_register_attempt'] = time();
        } elseif (empty($full_name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide valid details.";
            $_SESSION['register_attempts']++;
            $_SESSION['last_register_attempt'] = time();
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (
                    full_name, mobile, email, password, status
                ) VALUES (?, ?, ?, ?, 'account_pending')");

                $stmt->execute([
                    $full_name, $mobile, $email, $password_hash
                ]);

                $success = "Your account request has been submitted to the admin for approval. Please wait for confirmation.";
                $_SESSION['register_attempts'] = 0;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "An account with this email or mobile already exists.";
                } else {
                    error_log("Registration failed: " . $e->getMessage());
                    $error = "Registration failed. Please try again later.";
                }
                $_SESSION['register_attempts']++;
                $_SESSION['last_register_attempt'] = time();
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-dark" data-aos="fade-up">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600" data-aos="fade-up" data-aos-delay="100">
                Join the Digambar Jain Matrimony community
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md" data-aos="fade-up" data-aos-delay="200">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-100">
                <?php if ($success): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 font-medium">
                                    <?= $success ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="login.php" class="text-primary hover:underline font-semibold">Return to Login</a>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium">
                                        <?= $error ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form class="space-y-6" action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="full_name" name="full_name" type="text" required class="appearance-none block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mobile Number *</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <select name="country_code" class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm focus:ring-primary focus:border-primary">
                                    <option value="+91">+91</option>
                                    <option value="+1">+1</option>
                                    <option value="+44">+44</option>
                                    <option value="+61">+61</option>
                                </select>
                                <input type="tel" name="mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50" placeholder="10-digit mobile number">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" name="password" type="password" required class="appearance-none block w-full pl-10 pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('password', 'toggle-icon-1')">
                                    <i id="toggle-icon-1" class="fas fa-eye text-gray-400 hover:text-gray-600 transition"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="confirm_password" name="confirm_password" type="password" required class="appearance-none block w-full pl-10 pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('confirm_password', 'toggle-icon-2')">
                                    <i id="toggle-icon-2" class="fas fa-eye text-gray-400 hover:text-gray-600 transition"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                                Request Account
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center text-sm">
                        Already have an account? 
                        <a href="login.php" class="font-bold text-primary hover:underline">
                            Sign in here
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
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
