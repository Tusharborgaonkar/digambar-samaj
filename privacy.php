<?php
require_once 'includes/db.php';

$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'privacy_policy'");
$dynamic_privacy = $stmt->fetchColumn();

$pageTitle = 'Privacy Policy';
include 'includes/header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl bg-white p-8 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-3xl font-bold text-dark mb-6 border-b pb-4">Privacy Policy</h1>
        <div class="prose max-w-none text-gray-700 space-y-4">
            <?php if(!empty($dynamic_privacy)): ?>
                <?= $dynamic_privacy ?>
            <?php else: ?>
                <p>Welcome to Jain Digambar Matrimony. We are committed to protecting your privacy.</p>
                <h3 class="text-xl font-semibold mt-6 mb-2">1. Information Collection</h3>
                <p>We collect personal information such as your name, contact details, marital status, education, and occupation when you register on our platform. This information is necessary to provide our matchmaking services.</p>
                <h3 class="text-xl font-semibold mt-6 mb-2">2. Use of Information</h3>
                <p>Your information is used solely for the purpose of helping you find a suitable match within the Digambar Jain community. We do not sell or rent your personal information to third parties.</p>
                <h3 class="text-xl font-semibold mt-6 mb-2">3. Data Security</h3>
                <p>We implement strict security measures to protect your data from unauthorized access or disclosure. Profile photos and sensitive information are protected and only visible to registered and approved members.</p>
                <h3 class="text-xl font-semibold mt-6 mb-2">4. Contact Us</h3>
                <p>If you have any questions about this Privacy Policy, please contact us at help@digambarjainparichay.com.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
