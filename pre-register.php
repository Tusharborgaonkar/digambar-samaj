<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
include 'includes/db.php';
include 'includes/Mailer.php';

// Create otp_verifications table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS otp_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_otp_email (email)
)");

// Handle reset before any output
if (isset($_GET['reset'])) {
    unset($_SESSION['otp_step'], $_SESSION['otp_email'], $_SESSION['reg_data']);
    header('Location: pre-register.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting
if (!isset($_SESSION['register_attempts'])) $_SESSION['register_attempts'] = 0;
if (!isset($_SESSION['last_register_attempt'])) $_SESSION['last_register_attempt'] = time();

$is_rate_limited = false;
$time_since_last = time() - $_SESSION['last_register_attempt'];
if ($_SESSION['register_attempts'] >= 5 && $time_since_last < 300) {
    $is_rate_limited = true;
    $error = "Too many failed attempts. Please try again in " . ceil((300 - $time_since_last) / 60) . " minutes.";
} elseif ($time_since_last >= 300) {
    $_SESSION['register_attempts'] = 0;
}

$success = '';
$error = $error ?? '';
$step = $_SESSION['otp_step'] ?? 'form'; // 'form' | 'otp'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_rate_limited) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid request. Please refresh and try again.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'send_otp') {
        // Step 1: Validate form & send OTP
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $mobile    = $_POST['mobile'] ?? '';
        $country_code = $_POST['country_code'] ?? '+91';
        $password  = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
            $error = "Mobile number must be exactly 10 digits.";
        } elseif (empty($full_name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide valid details.";
        } else {
            // Check duplicate
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? OR mobile = ? LIMIT 1");
            $chk->execute([$email, $country_code . $mobile]);
            if ($chk->fetch()) {
                $error = "An account with this email or mobile already exists.";
            } else {
                // Generate OTP
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires = date('Y-m-d H:i:s', time() + 600); // 10 min

                // Delete old OTPs for this email
                $pdo->prepare("DELETE FROM otp_verifications WHERE email = ?")->execute([$email]);

                // Insert new OTP
                $pdo->prepare("INSERT INTO otp_verifications (email, otp_code, expires_at) VALUES (?, ?, ?)")
                    ->execute([$email, $otp, $expires]);

                // Send OTP email
                $mailer = new Mailer();
                $html = "
                <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden'>
                  <div style='background:#7c3aed;padding:20px;text-align:center'>
                    <h2 style='color:#fff;margin:0'>Digambar Jain Matrimony</h2>
                  </div>
                  <div style='padding:30px'>
                    <p style='font-size:16px'>Hello <strong>" . htmlspecialchars($full_name) . "</strong>,</p>
                    <p>Your OTP for account registration is:</p>
                    <div style='text-align:center;margin:24px 0'>
                      <span style='font-size:36px;font-weight:bold;letter-spacing:10px;color:#7c3aed'>{$otp}</span>
                    </div>
                    <p style='color:#6b7280;font-size:13px'>This OTP is valid for <strong>10 minutes</strong>. Do not share it with anyone.</p>
                  </div>
                </div>";

                $sent = $mailer->send($email, "Your OTP - Digambar Jain Matrimony", $html);

                if ($sent) {
                    // Store form data in session for step 2
                    $_SESSION['otp_step']     = 'otp';
                    $_SESSION['otp_email']    = $email;
                    $_SESSION['reg_data']     = compact('full_name', 'email', 'mobile', 'country_code', 'password');
                    $step = 'otp';
                    $success = "OTP sent to <strong>{$email}</strong>. Please check your inbox.";
                } else {
                    $error = "Failed to send OTP. Please try again.";
                    $_SESSION['register_attempts']++;
                    $_SESSION['last_register_attempt'] = time();
                }
            }
        }

    } elseif (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        // Step 2: Verify OTP & create account
        $entered_otp = trim($_POST['otp'] ?? '');
        $email = $_SESSION['otp_email'] ?? '';
        $reg   = $_SESSION['reg_data'] ?? [];

        if (empty($email) || empty($reg)) {
            $error = "Session expired. Please start again.";
            unset($_SESSION['otp_step'], $_SESSION['otp_email'], $_SESSION['reg_data']);
            $step = 'form';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM otp_verifications WHERE email = ? AND verified = 0 ORDER BY id DESC LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = "No OTP found. Please request a new one.";
                $step = 'otp';
            } elseif (strtotime($row['expires_at']) < time()) {
                $error = "OTP has expired. Please go back and request a new one.";
                $step = 'otp';
            } elseif ($row['otp_code'] !== $entered_otp) {
                $error = "Invalid OTP. Please try again.";
                $_SESSION['register_attempts']++;
                $_SESSION['last_register_attempt'] = time();
                $step = 'otp';
            } else {
                // Mark OTP as verified
                $pdo->prepare("UPDATE otp_verifications SET verified = 1 WHERE id = ?")->execute([$row['id']]);

                // Create account
                $password_hash = password_hash($reg['password'], PASSWORD_DEFAULT);
                try {
                    $ins = $pdo->prepare("INSERT INTO users (full_name, mobile, email, password_hash, status) VALUES (?, ?, ?, ?, 'account_approved')");
                    $ins->execute([$reg['full_name'], $reg['country_code'] . $reg['mobile'], $reg['email'], $password_hash]);

                    unset($_SESSION['otp_step'], $_SESSION['otp_email'], $_SESSION['reg_data']);
                    $_SESSION['register_attempts'] = 0;
                    $step = 'done';
                    $success = "Your account has been created successfully. You can now login to complete your registration.";
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error = "An account with this email or mobile already exists.";
                    } else {
                        error_log("Registration failed: " . $e->getMessage());
                        $error = "Registration failed. Please try again later.";
                    }
                    $step = 'otp';
                }
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

                <?php if ($step === 'done'): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <p class="ml-3 text-sm text-green-700 font-medium"><?= $success ?></p>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="login.php" class="text-primary hover:underline font-semibold">Return to Login</a>
                    </div>

                <?php elseif ($step === 'otp'): ?>
                    <?php if ($success): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                            <p class="text-sm text-green-700 font-medium"><?= $success ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
                        </div>
                    <?php endif; ?>

                    <p class="text-sm text-gray-600 mb-4">Enter the 6-digit OTP sent to <strong><?= htmlspecialchars($_SESSION['otp_email'] ?? '') ?></strong></p>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="verify_otp">
                        <div>
                            <label for="otp" class="block text-sm font-medium text-gray-700">OTP Code *</label>
                            <div class="mt-1">
                                <input id="otp" name="otp" type="text" maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                                    required autocomplete="one-time-code"
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-center text-2xl tracking-widest focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50">
                            </div>
                        </div>
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                            Verify OTP & Create Account
                        </button>
                    </form>
                    <div class="mt-4 text-center text-sm">
                        <a href="pre-register.php?reset=1" class="text-primary hover:underline">Go back & resend OTP</a>
                    </div>

                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <div class="flex">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                                <p class="ml-3 text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form class="space-y-6" action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="send_otp">

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
                                <input type="tel" name="mobile" pattern="[0-9]{10}" maxlength="10" minlength="10"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required
                                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-50"
                                    placeholder="10-digit mobile number">
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
                                Send OTP to Email
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 text-center text-sm">
                        Already have an account?
                        <a href="login.php" class="font-bold text-primary hover:underline">Sign in here</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>



<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
