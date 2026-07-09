<?php
$current_page = 'settings.php';
include 'includes/header.php'; 
include 'includes/sidebar.php'; 
require_once '../includes/db.php';

// Fetch all settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

$payment_enabled = $settings['payment_enabled'] ?? '0';
$auto_approve = $settings['auto_approve'] ?? '0';
$support_email = $settings['support_email'] ?? 'support@jaindigambarmatrimony.com';
$about_youtube = $settings['about_youtube'] ?? '';
$about_us = $settings['about_us'] ?? '';
$terms = $settings['terms_conditions'] ?? '';
$privacy = $settings['privacy_policy'] ?? '';
?>

<!-- Page Header -->
<form action="save-settings.php" method="POST">
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Admin Settings</h3>
        <p class="text-gray-500 text-sm">Configure global platform settings and dynamic pages.</p>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='saved'): ?>
            <p class="text-green-600 font-bold mt-2">Settings saved successfully!</p>
        <?php endif; ?>
    </div>
    <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-opacity-90 transition shadow-sm flex items-center">
        <i class="fas fa-save mr-2"></i> Save All Settings
    </button>
</div>

<div class="grid grid-cols-1 gap-6 mb-8">
    
    <!-- Global Settings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <h4 class="font-bold text-gray-800"><i class="fas fa-globe mr-2 text-primary"></i> Global Platform Settings</h4>
        </div>
        <div class="p-6 space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <h5 class="font-bold text-gray-800">Enable Payment on Registration</h5>
                    <p class="text-xs text-gray-500 mt-1">If enabled, users must pay the subscription fee before completing their profile registration.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4">
                    <input type="hidden" name="payment_enabled" value="0">
                    <input type="checkbox" name="payment_enabled" value="1" <?= $payment_enabled == '1' ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
            
            <hr class="border-gray-100">

            <div class="flex items-center justify-between">
                <div>
                    <h5 class="font-bold text-gray-800">Auto-Approve Profiles</h5>
                    <p class="text-xs text-gray-500 mt-1">Automatically approve new registrations without admin verification.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4">
                    <input type="hidden" name="auto_approve" value="0">
                    <input type="checkbox" name="auto_approve" value="1" <?= $auto_approve == '1' ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>

            <hr class="border-gray-100">

            <div>
                <label class="block font-bold text-gray-800 mb-2">Support Email Address</label>
                <input type="email" name="support_email" value="<?= htmlspecialchars($support_email) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
            </div>
            
            <hr class="border-gray-100 mt-6 mb-6">

            <div>
                <label class="block font-bold text-gray-800 mb-2">About Us YouTube Video URL</label>
                <input type="text" name="about_youtube" value="<?= htmlspecialchars($about_youtube) ?>" placeholder="Enter YouTube Embed URL" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                <p class="text-xs text-gray-500 mt-1">This video will be displayed on the About Us page.</p>
            </div>

            <hr class="border-gray-100 mt-6 mb-6">

            <div>
                <label class="block font-bold text-gray-800 mb-2">About Us Content</label>
                <textarea name="about_us" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"><?= htmlspecialchars($about_us) ?></textarea>
                <p class="text-xs text-gray-500 mt-1">HTML is supported.</p>
            </div>

            <hr class="border-gray-100 mt-6 mb-6">

            <div>
                <label class="block font-bold text-gray-800 mb-2">Terms & Conditions</label>
                <textarea name="terms_conditions" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"><?= htmlspecialchars($terms) ?></textarea>
                <p class="text-xs text-gray-500 mt-1">HTML is supported.</p>
            </div>

            <hr class="border-gray-100 mt-6 mb-6">

            <div>
                <label class="block font-bold text-gray-800 mb-2">Privacy Policy</label>
                <textarea name="privacy_policy" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"><?= htmlspecialchars($privacy) ?></textarea>
                <p class="text-xs text-gray-500 mt-1">HTML is supported.</p>
            </div>
            
        </div>
    </div>
</div>
</form>

<?php include 'includes/footer.php'; ?>
