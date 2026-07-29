<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "User not found.";
        } else {
            // Verify current password
            $is_valid_password = false;
            if (!empty($user['password_hash'])) {
                $is_valid_password = password_verify($current_password, $user['password_hash']);
            } else {
                $is_valid_password = ($current_password === $user['mobile']);
            }

            if (!$is_valid_password) {
                $error = "Incorrect current password.";
            } else {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = ?, has_set_password = 1 WHERE id = ?");
                if ($update->execute([$password_hash, $user_id])) {
                    $success = "Password successfully changed.";
                } else {
                    $error = "Failed to update password. Please try again.";
                }
            }
        }
    }
}
?>

<section class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-dark" data-aos="fade-up">
                Change Password
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md" data-aos="fade-up" data-aos-delay="200">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-100">

                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                            <p class="ml-3 text-sm text-red-700"><?= $error ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <p class="ml-3 text-sm text-green-700"><?= $success ?></p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="my-profile.php" class="text-primary hover:underline font-medium">Return to Profile</a>
                    </div>
                <?php else: ?>
                    <form class="space-y-6" method="POST">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                            <div class="mt-1">
                                <input id="current_password" name="current_password" type="password" required
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                            <div class="mt-1">
                                <input id="new_password" name="new_password" type="password" required
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters.</p>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                            <div class="mt-1">
                                <input id="confirm_password" name="confirm_password" type="password" required
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <a href="my-profile.php" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="w-auto flex justify-center py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150">
                                Update Password
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
