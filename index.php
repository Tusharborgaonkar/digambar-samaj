<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Fetch settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

// Fetch active advertisements
$advertisements = [];
try {
    $adsStmt = $pdo->query("SELECT * FROM advertisements WHERE status = 1 ORDER BY id DESC");
    $advertisements = $adsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$home_top_ads = array_filter($advertisements, function($ad) { 
    if ($ad['position'] != 'home_top') return false;
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    return !empty($img);
});

$home_bottom_ads = array_filter($advertisements, function($ad) { 
    if ($ad['position'] != 'home_bottom') return false;
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    return !empty($img);
});

$left_sidebar_ads = array_filter($advertisements, function($ad) { 
    if ($ad['position'] != 'left_side') return false;
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    return !empty($img);
});

$right_sidebar_ads = array_filter($advertisements, function($ad) { 
    if ($ad['position'] != 'right_side') return false;
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    return !empty($img);
});

$footer_ads = array_filter($advertisements, function($ad) { 
    if ($ad['position'] != 'footer') return false;
    $img = $ad['image_path'] ?? ($ad['image'] ?? '');
    return !empty($img);
});

$is_logged_in = false;
$is_approved = false;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $is_logged_in = true;
    try {
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_status = $stmt->fetchColumn();
        if ($user_status === 'approved') {
            $is_approved = true;
        }
    } catch(PDOException $e) {}
} else if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $is_logged_in = true;
    $is_approved = true;
}

include 'includes/header.php';
?>

<!-- Preloader -->
<div id="preloader"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-white transition-opacity duration-500">
    <div class="flex flex-col items-center">
        <!-- Spinner -->
        <div class="relative w-16 h-16 sm:w-20 sm:h-20">
            <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
            <div
                class="absolute inset-0 rounded-full border-4 border-t-primary border-r-transparent border-b-transparent border-l-transparent animate-spin">
            </div>
            <div class="absolute inset-2 rounded-full border-4 border-gray-100"></div>
            <div
                class="absolute inset-2 rounded-full border-4 border-t-accent border-r-transparent border-b-transparent border-l-transparent animate-spin-reverse">
            </div>
        </div>
        <!-- Logo / Brand Text -->
        <div class="mt-5 flex flex-col items-center px-4 text-center">
            <h2 class="text-xl sm:text-2xl font-bold text-primary tracking-wide">Jain Digambar</h2>
            <span class="text-xs text-secondary font-semibold tracking-widest uppercase mt-1">Matrimony</span>
        </div>
    </div>
</div>

<style>
    @keyframes spin-reverse {
        0% {
            transform: rotate(360deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }

    .animate-spin-reverse {
        animation: spin-reverse 1.2s linear infinite;
    }
</style>

<script>
    // Hide preloader when page is fully loaded
    window.addEventListener('load', function () {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                preloader.remove();
            }, 500);
        }
    });
</script>

<!-- Hero Section (3-Column Layout) -->
<section class="relative flex flex-col justify-start items-center overflow-hidden bg-gray-900 pt-4 pb-8 md:min-h-[85vh]">
    <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-primary/20 z-0"></div>

    <div class="container mx-auto px-4 relative z-20 w-full flex flex-col xl:flex-row gap-6">
        
        <!-- Left + Right Ads wrapper: 2-col row on mobile/tablet, becomes plain flex children (sidebars) on xl -->
        <div class="grid grid-cols-2 gap-4 xl:contents order-2 xl:order-none">

        <!-- Left Ad Panel -->
        <?php if (!isset($settings['show_hero_left_ad']) || $settings['show_hero_left_ad'] == '1'): ?>
        <div class="flex flex-col w-full xl:w-64 space-y-4 flex-shrink-0 xl:order-1">
            <?php if (!empty($left_sidebar_ads)): ?>
                <?php foreach($left_sidebar_ads as $ad): 
                    $ad_img = $ad['image'] ?? $ad['image_path'] ?? '';
                    if (strpos($ad_img, 'data:image/') === 0) {
                        $img_src = $ad_img;
                    } else {
                        $img_src = 'image.php?file=' . urlencode(ltrim(str_replace('../', '', $ad_img), '/\\'));
                    }
                ?>
                    <?php if(!empty($ad['link'])): ?>
                        <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="block relative w-full h-full min-h-[160px] sm:min-h-[220px] xl:min-h-[300px] flex-grow rounded shadow-lg border border-gray-700 overflow-hidden hover:opacity-90 transition">
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover">
                        </a>
                    <?php else: ?>
                        <div class="relative w-full h-full min-h-[160px] sm:min-h-[220px] xl:min-h-[300px] flex-grow rounded shadow-lg border border-gray-700 overflow-hidden">
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Unsplash Placeholder Ad -->
                <div class="relative w-full h-full min-h-[160px] sm:min-h-[220px] xl:min-h-[300px] flex-grow rounded shadow-lg border border-gray-700 overflow-hidden group">
                    <img src="https://images.unsplash.com/photo-1583939000148-f75e1140984f?auto=format&fit=crop&w=400&q=80" alt="Advertise" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                        <span class="text-white font-bold text-base sm:text-xl xl:text-2xl tracking-widest uppercase">Advertise</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Center Section (Content & Banner) -->
        <div class="flex-grow w-full col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-0 items-stretch bg-[#1a2942] rounded-2xl overflow-hidden shadow-2xl order-1 xl:order-2 min-h-[240px] sm:min-h-[300px] md:min-h-[340px]" data-aos="fade-up">
            <div class="flex flex-col justify-center p-4 sm:p-6 md:p-10 lg:p-12 text-center sm:text-left h-full">
                <h2 class="text-base sm:text-2xl md:text-3xl lg:text-4xl font-bold text-white leading-tight">
                    <?php 
                        $hero_h = $settings['hero_heading'] ?? "The most trusted\nmatrimony\nservice for\nDigambar Jain!";
                        $hero_h = str_ireplace(['<br>', '<br/>', '<br />'], "\n", $hero_h);
                        echo nl2br(htmlspecialchars($hero_h));
                    ?>
                </h2>
                <p class="hidden sm:block text-sm sm:text-base md:text-lg text-gray-300 leading-relaxed max-w-xl mt-4 sm:mt-6 md:mt-8">
                    <?= nl2br(htmlspecialchars($settings['hero_description'] ?? 'This website is created only for the Digambar Jain community to help eligible young men and women of the entire Digambar Jain society find their suitable life partner.')) ?>
                </p>
            </div>
            
            <div class="relative w-full h-full min-h-[140px] sm:min-h-[220px] md:min-h-[300px] flex items-center justify-center bg-[#1a2942] p-2 sm:p-4">
                <?php
                $hero_img_src = 'assets/images/gallery/TEMP1.jpg';
                if (!empty($settings['hero_banner'])) {
                    if (strpos($settings['hero_banner'], 'data:image/') === 0) {
                        $hero_img_src = $settings['hero_banner'];
                    } else {
                        $clean_banner = ltrim(str_replace('../', '', $settings['hero_banner']), '/\\');
                        $hero_img_src = 'image.php?file=' . urlencode($clean_banner);
                    }
                }
                ?>
                <img src="<?= $hero_img_src ?>" alt="Matrimony Hero" class="w-full h-full object-contain max-h-[180px] sm:max-h-[280px] md:max-h-[350px] lg:max-h-[500px]">
            </div>
        </div>

        <!-- Right Ad Panel -->
        <?php if (!isset($settings['show_hero_right_ad']) || $settings['show_hero_right_ad'] == '1'): ?>
        <div class="flex flex-col w-full xl:w-64 space-y-4 flex-shrink-0 xl:order-3">
            <?php if (!empty($right_sidebar_ads)): ?>
                <?php foreach($right_sidebar_ads as $ad): 
                    $ad_img = $ad['image'] ?? $ad['image_path'] ?? '';
                    if (strpos($ad_img, 'data:image/') === 0) {
                        $img_src = $ad_img;
                    } else {
                        $img_src = 'image.php?file=' . urlencode(ltrim(str_replace('../', '', $ad_img), '/\\'));
                    }
                ?>
                    <?php if(!empty($ad['link'])): ?>
                        <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="block relative w-full h-full min-h-[160px] sm:min-h-[220px] xl:min-h-[300px] flex-grow rounded shadow-lg border border-gray-700 overflow-hidden hover:opacity-90 transition">
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover">
                        </a>
                    <?php else: ?>
                        <div class="relative w-full h-full min-h-[160px] sm:min-h-[220px] xl:min-h-[300px] flex-grow rounded shadow-lg border border-gray-700 overflow-hidden">
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Unsplash Placeholder Ad -->
                <div class="relative w-full h-full min-h-[160px] sm:min-h-[220px] xl:min-h-[300px] flex-grow rounded shadow-lg border border-gray-700 overflow-hidden group">
                    <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=400&q=80" alt="Advertise" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                        <span class="text-white font-bold text-base sm:text-xl xl:text-2xl tracking-widest uppercase">Advertise</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        </div>
        <!-- /Left + Right Ads wrapper -->

    </div>

    <!-- Bottom Ad Panel (Moved into Hero Section) -->
    <?php if (!isset($settings['show_hero_bottom_ad']) || $settings['show_hero_bottom_ad'] == '1'): ?>
    <div class="container mx-auto px-4 relative z-20 w-full mt-6">
        <div class="flex flex-wrap justify-center gap-4 w-full">
            <?php if (!empty($home_bottom_ads)): ?>
                <?php foreach($home_bottom_ads as $ad): 
                    $ad_img = $ad['image'] ?? $ad['image_path'] ?? '';
                    if (strpos($ad_img, 'data:image/') === 0) {
                        $img_src = $ad_img;
                    } else {
                        $img_src = 'image.php?file=' . urlencode(ltrim(str_replace('../', '', $ad_img), '/\\'));
                    }
                ?>
                        <div class="relative w-full h-[100px] sm:h-[130px] md:h-[150px] rounded shadow-lg border border-gray-700 overflow-hidden">
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Unsplash Placeholder Ad -->
                <div class="relative w-full h-[100px] sm:h-[130px] md:h-[150px] rounded shadow-lg border border-gray-700 overflow-hidden group">
                    <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=1200&q=80" alt="Advertise" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                        <span class="text-white font-bold text-xl sm:text-2xl md:text-3xl tracking-widest uppercase">Advertise</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</section>



<!-- Quick Search Section -->
<section class="bg-light relative z-20 mt-8 sm:mt-16 md:mt-24 lg:mt-36">
    <div class="container mx-auto px-4 -mt-4 sm:-mt-10 md:-mt-16 lg:-mt-24 mb-12">
        <div id="quick-search"
            class="bg-white bg-opacity-95 p-4 sm:p-6 rounded-xl shadow-2xl max-w-6xl mx-auto backdrop-blur-sm border-t-4 border-primary"
            data-aos="fade-up" data-aos-delay="200">
            <h3 class="text-lg sm:text-xl font-bold text-dark mb-4 border-b pb-2"><i
                    class="fas fa-search text-primary mr-2"></i>Quick Search</h3>
            <?php if (!$is_logged_in): ?>
                <div class="text-center py-6">
                    <p class="text-base sm:text-lg text-gray-700 mb-4">Please login or register to search profiles.</p>
                    <a href="login.php" class="inline-block bg-primary text-white px-6 sm:px-8 py-3 rounded-md font-bold shadow-md hover:bg-opacity-90 transition"><i class="fas fa-sign-in-alt mr-2"></i>Login to Search</a>
                </div>
            <?php elseif (!$is_approved): ?>
                <div class="text-center py-6">
                    <p class="text-lg sm:text-xl text-yellow-600 font-bold mb-2"><i class="fas fa-clock mr-2"></i>Profile Pending Approval</p>
                    <p class="text-gray-700">Your profile is pending approval. Search will be available after admin approval.</p>
                </div>
            <?php else: ?>
                <form action="profiles.php" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Looking For -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Looking For</label>
                            <select name="gender"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Both</option>
                                <option value="Girl">Girl (Female)</option>
                                <option value="Boy">Boy (Male)</option>
                            </select>
                        </div>
                        <!-- Age Group -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Age Group</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="age_from" placeholder="From"
                                    class="w-1/2 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50"
                                    min="18" max="70">
                                <span class="text-gray-500 font-medium">to</span>
                                <input type="number" name="age_to" placeholder="To"
                                    class="w-1/2 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50"
                                    min="18" max="70">
                            </div>
                        </div>
                        <!-- Marital Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Marital Status</label>
                            <select name="marital"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Any</option>
                                <option value="Never Married">Never Married</option>
                                <option value="Widow">Widow</option>
                                <option value="Widower">Widower</option>
                                <option value="Divorce">Divorcee</option>
                            </select>
                        </div>
                        <!-- Manglik -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Manglik Status</label>
                            <select name="manglik"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Any</option>
                                <option value="yes">Manglik</option>
                                <option value="no">Non-Manglik</option>
                            </select>
                        </div>
                        <!-- State -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">State</label>
                            <select name="state"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Any State</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Gujarat">Gujarat</option>
                                <option value="Rajasthan">Rajasthan</option>
                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                            </select>
                        </div>
                        <!-- City -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">City</label>
                            <input type="text" name="city" placeholder="Enter City Name"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                        </div>
                        <!-- Education -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Education</label>
                            <select name="education"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Any Education</option>
                                <option value="Bachelors">Bachelors</option>
                                <option value="Masters">Masters</option>
                                <option value="Doctorate">Doctorate</option>
                                <option value="Diploma">Diploma</option>
                            </select>
                        </div>
                        <!-- Profession -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Profession</label>
                            <select name="occupation"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Any Profession</option>
                                <option value="Doctor">Doctor</option>
                                <option value="Engineer">Engineer</option>
                                <option value="CA/CS">CA / CS</option>
                                <option value="Business">Business</option>
                                <option value="Service">Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 text-center">
                        <button type="submit"
                            class="bg-primary text-white px-10 py-3 rounded-md text-lg font-bold hover:bg-opacity-90 transition shadow-lg w-full md:w-auto"><i
                                class="fas fa-search mr-2"></i>Search Profiles</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (isset($settings['show_matrimony_book_fee']) && $settings['show_matrimony_book_fee'] == '1'): ?>
<!-- Matrimony Book Notice Section -->
<section class="bg-yellow-50 border-y border-yellow-200 py-6 mb-12">
    <div class="container mx-auto px-4 text-center">
        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-800 mb-2">
            <i class="fas fa-book-open mr-2"></i> Free Registration
        </h3>
        <p class="text-base sm:text-lg text-yellow-700">
            If you want your photo printed in our matrimony book, a fee of Rs. 1000/- is required.
        </p>
        <p class="text-sm sm:text-md text-yellow-600 mt-2 font-medium">
            Kindly scan the QR code to pay Rs. 1000/- and mention your Mobile No. in Payment Remarks.
        </p>
        <?php 
        $payment_qr_code = $settings['payment_qr_code'] ?? 'assets/images/qr_code.jpg';
        $is_base64_qr = strpos($payment_qr_code, 'data:image/') === 0;
        $clean_qr_code = $is_base64_qr ? '' : ltrim(str_replace('../', '', $payment_qr_code), '/\\');
        $qr_exists = $is_base64_qr || (!empty($clean_qr_code) && file_exists(__DIR__ . '/' . $clean_qr_code));
        ?>
        <div class="mt-4 flex justify-center">
            <?php if ($qr_exists): ?>
                <?php if ($is_base64_qr): ?>
                    <img src="<?= $payment_qr_code ?>" alt="Payment QR" class="w-36 h-36 sm:w-48 sm:h-48 border border-yellow-300 rounded shadow-sm object-cover">
                <?php else: ?>
                    <img src="image.php?file=<?= urlencode($clean_qr_code) ?>" alt="Payment QR" class="w-36 h-36 sm:w-48 sm:h-48 border border-yellow-300 rounded shadow-sm object-cover">
                <?php endif; ?>
            <?php else: ?>
                <img src="https://placehold.co/200x200/fef08a/854d0e?text=QR+Code+Not+Found" alt="Payment QR Placeholder" class="w-36 h-36 sm:w-48 sm:h-48 border border-yellow-300 rounded shadow-sm object-cover">
            <?php endif; ?>
        </div>
        <div class="mt-6">
            <a href="my-profile.php#payment-upload" class="inline-block bg-primary text-white px-6 sm:px-8 py-3 rounded-md shadow-lg hover:bg-opacity-90 transition font-bold">Upload Payment Screenshot</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($home_top_ads) && (isset($settings['show_home_top_ads']) ? $settings['show_home_top_ads'] == '1' : false)): ?>
<!-- Advertisements (Home Top) -->
<section class="py-8 bg-white border-b border-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-center text-dark mb-8">Advertisements</h2>
        <div class="flex flex-wrap justify-center gap-6 items-center">
            <?php foreach($home_top_ads as $ad): ?>
                <?php 
                $ad_img = $ad['image'] ?? $ad['image_path'] ?? '';
                if (strpos($ad_img, 'data:image/') === 0) {
                    $img_src = $ad_img;
                } else {
                    $img_src = 'image.php?file=' . urlencode(ltrim(str_replace('../', '', $ad_img), '/\\'));
                }
                ?>
                <div class="block w-full max-w-[295px] aspect-[2/3] rounded-xl overflow-hidden shadow-md transition bg-white">
                    <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-full object-cover">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Profiles Section -->
<section id="latest" class="py-12 sm:py-16 bg-light">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10" data-aos="fade-up">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-dark mb-3 relative inline-block">Latest Profiles
                <span class="absolute bottom-0 left-1/4 w-1/2 h-1 bg-primary rounded-full -mb-2"></span>
            </h2>
            <p class="text-gray-600 mt-4">Find your life partner from our newly registered members</p>
        </div>

        <?php
        $latest_gender = isset($_GET['latest_gender']) ? $_GET['latest_gender'] : 'Girl';
        if (!in_array($latest_gender, ['Girl', 'Boy'])) {
            $latest_gender = 'Girl';
        }
        ?>
        <div class="flex flex-wrap justify-center mb-8" data-aos="fade-up" data-aos-delay="100">
            <div class="inline-flex rounded-md shadow-sm mb-8" role="group">
                <a href="?latest_gender=Girl#latest"
                    class="<?= $latest_gender === 'Girl' ? 'bg-primary text-white' : 'bg-white text-dark hover:bg-gray-100 border border-r-0' ?> px-4 sm:px-8 py-2.5 rounded-l-full font-bold focus:outline-none transition shadow-md text-sm sm:text-base">Latest
                    Girls</a>
                <a href="?latest_gender=Boy#latest"
                    class="<?= $latest_gender === 'Boy' ? 'bg-primary text-white' : 'bg-white text-dark hover:bg-gray-100 border border-l-0' ?> px-4 sm:px-8 py-2.5 rounded-r-full font-bold focus:outline-none transition shadow-md text-sm sm:text-base">Latest
                    Boys</a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <?php
            // Check if user or admin is logged in (to allow photo viewing)
            $is_logged_in = false;
            $is_approved = false;

            if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
                $is_logged_in = true;
                $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user_status = $stmt->fetchColumn();
                // 'approved' = profile publicly visible, can view photos
                if ($user_status === 'approved') {
                    $is_approved = true;
                }
            } else if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                $is_logged_in = true;
                $is_approved = true;
            }

            // Fetch 4 latest profiles based on selected gender
            // Show 'approved' profiles + 'pending' (form submitted, awaiting profile approval)
            // Cards show as blurred/locked for non-logged-in visitors
            $gender_db = ($latest_gender === 'Girl') ? 'Female' : 'Male';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE status IN ('approved', 'pending') AND gender = ? ORDER BY id DESC LIMIT 4");
            $stmt->execute([$gender_db]);
            $index_profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add delay mapping for animation
            $delay = 0;
            foreach ($index_profiles as &$p) {
                $p['delay'] = $delay;
                $delay += 100;

                // Calculate age
                $age = 'N/A';
                if (!empty($p['birth_date'])) {
                    $bday = new DateTime($p['birth_date']);
                    $today = new DateTime('today');
                    $age = $bday->diff($today)->y;
                }
                $p['computed_age'] = $age;

                // Fallback image
                if (!empty($p['profile_photo']) && file_exists($p['profile_photo'])) {
                    $p['computed_img'] = 'image.php?file=' . urlencode($p['profile_photo']);
                } else {
                    $p['computed_img'] = 'https://ui-avatars.com/api/?name=' . urlencode($p['full_name']) . '&background=random';
                }
            }
            unset($p); // break reference
            

            foreach ($index_profiles as $p):
                $link = $is_logged_in ? "profile-details.php?id=" . $p['id'] : "login.php";
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 group border border-gray-100"
                    data-aos="fade-up" data-aos-delay="<?= $p['delay'] ?>">
                    <div class="relative overflow-hidden aspect-[3/4]">
                        <?php if ($is_approved): ?>
                            <img src="<?= htmlspecialchars($p['computed_img']) ?>" alt="Profile Photo"
                                class="w-full h-full object-cover object-top group-hover:scale-110 transition duration-500">
                        <?php else: ?>
                            <?php $placeholder = ($p['gender'] == 'Female') ? 'assets/images/bride_placeholder.png' : 'assets/images/groom_placeholder.png'; ?>
                            <div class="w-full h-full group-hover:scale-110 transition duration-500 relative">
                                <img src="<?= $placeholder ?>" alt="Profile Locked" class="w-full h-full object-cover object-top">
                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-30 text-white p-4 text-center z-10 backdrop-blur-[2px]">
                                    <i class="fas fa-lock text-2xl sm:text-3xl mb-2"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div
                            class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black via-black/70 to-transparent p-3 sm:p-4 z-20">
                            <a href="<?= $link ?>"
                                class="text-white font-bold text-sm sm:text-lg hover:underline block truncate"><?= htmlspecialchars($p['full_name']) ?></a>
                            <p class="text-gray-200 text-xs sm:text-sm font-medium"><?= $p['computed_age'] ?> Yrs,
                                <?= htmlspecialchars($p['height'] ?? 'N/A') ?>
                            </p>
                        </div>
                        <?php if (isset($p['created_at']) && strtotime($p['created_at']) > strtotime('-7 days')): ?>
                            <div
                                class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded shadow z-20">
                                New</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 sm:p-5">
                        <div class="space-y-2 mb-4">
                            <p class="text-xs sm:text-sm text-gray-600 flex items-center"><i
                                    class="fas fa-graduation-cap w-6 text-primary mr-2"></i>
                                <span class="truncate"><?= htmlspecialchars($p['higher_education'] ?? 'N/A') ?></span></p>
                            <p class="text-xs sm:text-sm text-gray-600 flex items-center"><i
                                    class="fas fa-briefcase w-6 text-primary mr-2"></i>
                                <span class="truncate"><?= htmlspecialchars($p['occupation'] ?? 'N/A') ?></span></p>
                            <p class="text-xs sm:text-sm text-gray-600 flex items-center"><i
                                    class="fas fa-map-marker-alt w-6 text-primary mr-2"></i>
                                <span class="truncate"><?= htmlspecialchars($p['native_place'] ?? 'N/A') ?></span></p>
                        </div>
                        <a href="<?= $link ?>"
                            class="block text-center bg-gray-50 border border-primary text-primary hover:bg-primary hover:text-white py-2 rounded-md transition font-semibold text-sm sm:text-base">View
                            Profile</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="profiles.php?gender=<?= urlencode($latest_gender) ?>"
                class="inline-block bg-primary text-white px-6 sm:px-8 py-3 rounded-md shadow-lg hover:bg-opacity-90 transition font-bold text-base sm:text-lg"><i
                    class="fas fa-users mr-2"></i>View All Profiles</a>
        </div>
    </div>
</section>

<!-- Find Matches Section -->
<section class="py-12 sm:py-16 bg-white border-t border-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10" data-aos="fade-up">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-dark mb-3 relative inline-block">Find Matches By Category
                <span class="absolute bottom-0 left-1/4 w-1/2 h-1 bg-primary rounded-full -mb-2"></span>
            </h2>
            <p class="text-gray-600 mt-4">Find matches based on your specific preferences</p>
        </div>

        <div class="mt-12 text-center" data-aos="fade-up">
            <a href="profiles.php?gender=Girl"
                class="inline-flex items-center justify-center bg-white border-2 border-primary text-primary px-4 sm:px-6 py-2.5 sm:py-3 rounded-md font-bold hover:bg-primary hover:text-white transition shadow-sm group mx-1 sm:mx-2 mb-2 text-sm sm:text-base">
                <i class="fas fa-female mr-2 text-primary group-hover:text-white"></i> All Girls</a>
            <a href="profiles.php?gender=Boy"
                class="inline-flex items-center justify-center bg-white border-2 border-primary text-primary px-4 sm:px-6 py-2.5 sm:py-3 rounded-md font-bold hover:bg-primary hover:text-white transition shadow-sm group mx-1 sm:mx-2 mb-2 text-sm sm:text-base">
                <i class="fas fa-male mr-2 text-primary group-hover:text-white"></i> All Boys</a>
        </div>
        <div class="flex flex-wrap justify-center gap-3 sm:gap-4 mt-8" data-aos="fade-up" data-aos-delay="100">
            <a href="profiles.php?education=Doctorate"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-user-md mr-2 text-primary group-hover:text-white"></i> Doctors</a>
            <a href="profiles.php?education=Engineer"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-hard-hat mr-2 text-primary group-hover:text-white"></i> Engineers</a>
            <a href="profiles.php?education=MBA/MCA"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-user-graduate mr-2 text-primary group-hover:text-white"></i> MBA/MCA</a>
            <a href="profiles.php?education=CA/CS"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-calculator mr-2 text-primary group-hover:text-white"></i> CA/CS</a>
            <a href="profiles.php?occupation=Business"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-briefcase mr-2 text-primary group-hover:text-white"></i> Business</a>
            <a href="profiles.php?occupation=Service"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-laptop-house mr-2 text-primary group-hover:text-white"></i> Service</a>
            <a href="profiles.php?nri=yes"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-plane mr-2 text-primary group-hover:text-white"></i> NRI</a>
            <a href="profiles.php?manglik=yes"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-om mr-2 text-primary group-hover:text-white"></i> Manglik</a>
            <a href="profiles.php?marital=Widow"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-user-alt-slash mr-2 text-primary group-hover:text-white"></i> Widow</a>
            <a href="profiles.php?marital=Divorce"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-heart-broken mr-2 text-primary group-hover:text-white"></i> Divorcee</a>
            <a href="profiles.php?marital=Widower"
                class="bg-light border border-gray-200 text-dark px-4 sm:px-6 py-2.5 sm:py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center group text-sm sm:text-base"><i
                    class="fas fa-user-slash mr-2 text-primary group-hover:text-white"></i> Widower</a>
        </div>
    </div>
</section>

<!-- Browse Directory (Location & Sect) -->
<section class="py-12 sm:py-16 bg-light border-y border-gray-200">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8">
            
            <!-- Browse By City -->
            <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="0">
                <h3 class="text-lg sm:text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-city text-primary mr-2"></i>Browse By City</h3>
                <ul class="space-y-3 mt-4">
                    <li><a href="profiles.php?city=Delhi" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Delhi Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Mumbai" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Mumbai Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Kolkata" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Kolkata Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Chennai" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Chennai Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Ahmedabad" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Ahmedabad Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li class="pt-2"><a href="profiles.php" class="text-primary font-bold hover:underline">View More Cities...</a></li>
                </ul>
            </div>

            <!-- Browse By State -->
            <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-lg sm:text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-map text-primary mr-2"></i>Browse By State</h3>
                <ul class="space-y-3 mt-4">
                    <li><a href="profiles.php?city=Gujarat" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Gujarat Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Maharashtra" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Maharashtra Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Rajasthan" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Rajasthan Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=MP" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>MP Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Haryana" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Haryana Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Bihar" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Bihar Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li class="pt-2"><a href="profiles.php" class="text-primary font-bold hover:underline">View More States...</a></li>
                </ul>
            </div>

            <!-- Browse By Country -->
            <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-lg sm:text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-globe text-primary mr-2"></i>Browse By Country</h3>
                <ul class="space-y-3 mt-4">
                    <li><a href="profiles.php?city=USA" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>USA Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=UK" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>UK Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Canada" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Canada Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=Australia" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Australia Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php?city=UAE" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>UAE Matrimony</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li class="pt-2"><a href="profiles.php" class="text-primary font-bold hover:underline">View More Countries...</a></li>
                </ul>
            </div>

            <!-- Browse By Sect -->
            <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-lg sm:text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-praying-hands text-primary mr-2"></i>Browse By Sect</h3>
                <ul class="space-y-3 mt-4">
                    <li><a href="profiles.php" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Digambar Jain</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Shwetambar Murtipujak</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Sthanakvasi</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Terapanth</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                    <li><a href="profiles.php" class="text-gray-600 hover:text-primary transition font-medium flex items-center justify-between group"><span>Other Jain Sects</span> <i class="fas fa-angle-right text-gray-300 group-hover:text-primary"></i></a></li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- News & Updates / Stats Section -->
<section class="py-12 sm:py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 gap-12 items-center">

            <!-- News & Updates (Commented out)
            <div data-aos="fade-right">
                <div class="flex justify-between items-center mb-6 border-b pb-2">
                    <h2 class="text-3xl font-bold text-dark flex items-center"><i
                            class="fas fa-newspaper text-primary mr-3 text-2xl"></i>News & Updates</h2>
                    <a href="news.php"
                        class="bg-light text-primary px-4 py-1.5 rounded-md hover:bg-primary hover:text-white transition text-sm font-bold shadow-sm">View
                        All</a>
                </div>

                <div class="space-y-4">
                    
                </div>
            </div>
            -->

            <!-- Stats section -->
            <div class="bg-light p-5 sm:p-8 rounded-2xl border border-gray-100" data-aos="fade-left">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-dark mb-4 text-center lg:text-left">Trusted by Thousands of Digambar Jain Samaj since 5 years</h2>

                <div class="grid grid-cols-2 gap-3 sm:gap-6">
                    <div
                        class="bg-white p-4 sm:p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-11 h-11 sm:w-14 sm:h-14 mx-auto bg-red-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-heart text-lg sm:text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-xl sm:text-3xl font-bold text-dark mb-1" data-target="5000">5000+</div>
                        <p class="text-xs sm:text-sm text-gray-500 font-semibold">Happy Marriages</p>
                    </div>
                    <div
                        class="bg-white p-4 sm:p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-11 h-11 sm:w-14 sm:h-14 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-users text-lg sm:text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-xl sm:text-3xl font-bold text-dark mb-1" data-target="25000">25000+</div>
                        <p class="text-xs sm:text-sm text-gray-500 font-semibold">Verified Profiles</p>
                    </div>
                    <div
                        class="bg-white p-4 sm:p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-11 h-11 sm:w-14 sm:h-14 mx-auto bg-green-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-globe-asia text-lg sm:text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-xl sm:text-3xl font-bold text-dark mb-1" data-target="100">100+</div>
                        <p class="text-xs sm:text-sm text-gray-500 font-semibold">Cities Covered</p>
                    </div>
                    <div
                        class="bg-white p-4 sm:p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-11 h-11 sm:w-14 sm:h-14 mx-auto bg-yellow-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-award text-lg sm:text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-xl sm:text-3xl font-bold text-dark mb-1" data-target="15">15+</div>
                        <p class="text-xs sm:text-sm text-gray-500 font-semibold">Years of Trust</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php if (!empty($footer_ads)): ?>
<!-- Advertisements (Footer) -->
<section class="py-8 bg-gray-50 border-t border-gray-200">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center gap-6 items-center">
            <?php foreach($footer_ads as $ad): ?>
                <?php 
                $ad_img = $ad['image'] ?? $ad['image_path'] ?? '';
                if (strpos($ad_img, 'data:image/') === 0) {
                    $img_src = $ad_img;
                } else {
                    $img_src = 'image.php?file=' . urlencode(ltrim(str_replace('../', '', $ad_img), '/\\'));
                }
                ?>
                <?php $ad_link = $ad['link'] ?? ''; ?>
                <?php if(!empty($ad_link) && $ad_link !== '#'): ?>
                    <a href="<?= htmlspecialchars($ad_link) ?>" target="_blank" class="block w-full max-w-[295px] aspect-[2/3] rounded-xl overflow-hidden shadow-md hover:shadow-lg transition bg-white">
                        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-full object-cover">
                    </a>
                <?php else: ?>
                    <div class="block w-full max-w-[295px] aspect-[2/3] rounded-xl overflow-hidden shadow-md transition bg-white">
                        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>