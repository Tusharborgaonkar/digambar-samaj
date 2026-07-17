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
$contact_phone = $settings['contact_phone'] ?? '+91 7575005121';
$contact_email = $settings['contact_email'] ?? 'digambarjainparichay@gmail.com';
$contact_address = $settings['contact_address'] ?? '23-A, Shubhlaxmi Palace, Opp. Money Plant Junction, Bhuyangdev Cross Road, Sola Road, Ahmedabad-380061.';
$payment_qr_code = $settings['payment_qr_code'] ?? 'assets/images/qr_code.jpg';
?>

<!-- Page Header -->
<form action="save-settings.php" method="POST" enctype="multipart/form-data">
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
                    <h5 class="font-bold text-gray-800">Show Matrimony Book Fee Notice (Home Page)</h5>
                    <p class="text-xs text-gray-500 mt-1">Show a notice on the home page about the Rs. 1000/- fee for printing photos in the matrimony book.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4">
                    <input type="hidden" name="show_matrimony_book_fee" value="0">
                    <input type="checkbox" name="show_matrimony_book_fee" value="1" <?= ($settings['show_matrimony_book_fee'] ?? '0') == '1' ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
            
            <hr class="border-gray-100">

            <div class="flex items-center justify-between">
                <div class="flex-grow">
                    <h5 class="font-bold text-gray-800">Payment QR Code Image</h5>
                    <p class="text-xs text-gray-500 mt-1">Upload the QR code image for payments. (Shown on homepage and registration)</p>
                    <?php if (!empty($payment_qr_code)): ?>
                        <div class="mt-2">
                            <img src="../image.php?file=<?= urlencode($payment_qr_code) ?>" alt="Current QR Code" class="w-24 h-24 object-cover border rounded">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ml-4">
                    <input type="file" name="payment_qr_code_file" accept="image/*" class="text-sm">
                </div>
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
                <label class="block font-bold text-gray-800 mb-2">Support Email Address (Admin Notifications)</label>
                <input type="email" name="support_email" value="<?= htmlspecialchars($support_email) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
            </div>
            
            <hr class="border-gray-100 mt-6 mb-6">
            
            <h5 class="font-bold text-gray-800 mb-4"><i class="fas fa-address-book mr-2 text-primary"></i> Public Contact Information</h5>
            
            <div class="space-y-4">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Public Contact Email</label>
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($contact_email) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Public Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?= htmlspecialchars($contact_phone) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Public Contact Address</label>
                    <textarea name="contact_address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"><?= htmlspecialchars($contact_address) ?></textarea>
                </div>
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
