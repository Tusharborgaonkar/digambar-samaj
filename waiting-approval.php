<?php
session_start();
include 'includes/db.php';

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_status = $stmt->fetchColumn();

    if ($current_status === 'account_approved') {
        header('Location: registration.php');
        exit;
    } elseif ($current_status === 'approved') {
        header('Location: my-profile.php');
        exit;
    }
}

include 'includes/header.php'; 
?>
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-100 text-center">
            
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                <i class="fas fa-hourglass-half text-2xl text-yellow-600"></i>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Waiting for Approval</h2>
            
            <p class="text-gray-600 mb-6">
                Your account is currently under review by our administration team. 
                You will be able to access the platform once your account has been approved.
            </p>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 text-left">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Approvals usually take between 24-48 hours. Thank you for your patience!
                        </p>
                    </div>
                </div>
            </div>

            <?php if(isset($_SESSION['user_logged_in'])): ?>
            <div>
                <a href="login.php?logout=true" class="w-full flex justify-center py-2.5 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                    Logout
                </a>
            </div>
            <?php else: ?>
            <div>
                <a href="login.php" class="text-primary hover:underline font-medium">Return to Login</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
