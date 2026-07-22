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
if (empty(trim(strip_tags($terms)))) {
    require_once '../includes/default_terms.php';
    $terms = $default_terms;
}
$privacy = $settings['privacy_policy'] ?? '';
if (empty(trim(strip_tags($privacy)))) {
    require_once '../includes/default_privacy.php';
    $privacy = $default_privacy;
}
$contact_phone = $settings['contact_phone'] ?? '+91 7575005121';
$contact_email = $settings['contact_email'] ?? 'digambarjainparichay@gmail.com';
$contact_address = $settings['contact_address'] ?? '23-A, Shubhlaxmi Palace, Opp. Money Plant Junction, Bhuyangdev Cross Road, Sola Road, Ahmedabad-380061.';
$payment_qr_code = $settings['payment_qr_code'] ?? 'assets/images/qr_code.jpg';
$show_home_top_ads = $settings['show_home_top_ads'] ?? '1';

// Homepage/Hero Settings
$home_title = $settings['home_title'] ?? 'Digambar Jain Parichay';
$home_tagline = $settings['home_tagline'] ?? 'The most trusted matrimony service for Digambar Jain!';
$hero_heading = $settings['hero_heading'] ?? 'दिगंबर जैन युवक-युवती परिचय';
$hero_description = $settings['hero_description'] ?? 'This website is created only for the Digambar Jain community to help eligible young men and women find their suitable life partner.';
$hero_banner = $settings['hero_banner'] ?? '';
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
                            <?php 
                            $clean_qr_code = ltrim(str_replace('../', '', $payment_qr_code), '/\\');
                            if (file_exists(__DIR__ . '/../' . $clean_qr_code)): 
                            ?>
                                <img src="../image.php?file=<?= urlencode($clean_qr_code) ?>" alt="Current QR Code" class="w-24 h-24 object-cover border rounded">
                            <?php else: ?>
                                <img src="https://placehold.co/200x200/fef08a/854d0e?text=Missing" alt="Missing QR Code" class="w-24 h-24 object-cover border rounded">
                                <p class="text-xs text-red-500 mt-1">Image file not found on server.</p>
                            <?php endif; ?>
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

            <div class="flex items-start bg-gray-50 p-4 rounded-lg mt-4 border border-gray-100">
                <div class="flex-1">
                    <h5 class="font-bold text-gray-800">Show Advertisements</h5>
                    <p class="text-xs text-gray-500 mt-1">Display the advertisements section below Free Registration on the homepage.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4">
                    <input type="hidden" name="show_home_top_ads" value="0">
                    <input type="checkbox" name="show_home_top_ads" value="1" <?= (isset($show_home_top_ads) && $show_home_top_ads == '1') || !isset($show_home_top_ads) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>

            <hr class="border-gray-100">

            <div>
                <label class="block font-bold text-gray-800 mb-2">Support Email Address (Admin Notifications)</label>
                <input type="email" name="support_email" value="<?= htmlspecialchars($support_email) ?>" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address with @ and . characters" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
            </div>

            <hr class="border-gray-100 mt-6 mb-6">

            <h5 class="font-bold text-gray-800 mb-4"><i class="fas fa-home mr-2 text-primary"></i> Homepage & Hero Section Settings</h5>

            <div class="space-y-4">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Homepage Header Title</label>
                    <input type="text" name="home_title" value="<?= htmlspecialchars($home_title) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Homepage Tagline (Navbar)</label>
                    <input type="text" name="home_tagline" value="<?= htmlspecialchars($home_tagline) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Hero Section Heading</label>
                    <input type="text" name="hero_heading" value="<?= htmlspecialchars($hero_heading) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Hero Section Description</label>
                    <textarea name="hero_description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"><?= htmlspecialchars($hero_description) ?></textarea>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <div class="flex-grow">
                        <label class="block font-medium text-gray-700 mb-1">Hero Banner Image</label>
                        <p class="text-xs text-gray-500 mb-2">Upload the main image for the hero section.</p>
                        <?php if (!empty($hero_banner)): ?>
                            <div class="mt-2">
                            <?php 
                            $clean_hero_banner = ltrim(str_replace('../', '', $hero_banner), '/\\');
                            if (file_exists(__DIR__ . '/../' . $clean_hero_banner)): 
                            ?>
                                <img src="../image.php?file=<?= urlencode($clean_hero_banner) ?>" alt="Hero Banner" class="w-32 h-auto object-cover border rounded">
                                <?php else: ?>
                                    <p class="text-xs text-red-500">Image file not found on server.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ml-4">
                        <input type="file" name="hero_banner_file" accept="image/*" class="text-sm">
                    </div>
                </div>
            </div>
            
            <hr class="border-gray-100 mt-6 mb-6">
            
            <h5 class="font-bold text-gray-800 mb-4"><i class="fas fa-address-book mr-2 text-primary"></i> Public Contact Information</h5>
            
            <div class="space-y-4">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Public Contact Email</label>
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($contact_email) ?>" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address with @ and . characters" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Public Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?= htmlspecialchars($contact_phone) ?>" pattern="^\+?[0-9\s\-]{10,15}$" title="Enter a valid phone number (10-15 digits, optionally starting with +)" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
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
