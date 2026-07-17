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

$home_top_ads = array_filter($advertisements, function($ad) { return $ad['position'] == 'home_top'; });
$home_bottom_ads = array_filter($advertisements, function($ad) { return $ad['position'] == 'home_bottom'; });

$is_logged_in = false;
$is_approved = false;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $is_logged_in = true;
    try {
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_status = $stmt->fetchColumn();
        if (in_array($user_status, ['approved', 'account_approved'])) {
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
        <div class="relative w-20 h-20">
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
        <div class="mt-5 flex flex-col items-center">
            <h2 class="text-2xl font-bold text-primary tracking-wide">Jain Digambar</h2>
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

<!-- Hero Section -->
<section
    class="relative min-h-[100vw] md:min-h-[85vh] flex flex-col justify-start items-start overflow-hidden bg-gray-900">
    <!-- Solid Background instead of image -->
    <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-primary/20 z-0"></div>

    <div class="container mx-auto px-4 relative z-20 w-full pt-32 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start" data-aos="fade-up">
            <!-- Left Side: Content -->
            <div class="text-left">
                <h1 class="text-4xl md:text-6xl md:text-[4.5rem] font-bold text-white mb-6 leading-tight">दिगंबर जैन युवक-युवती परिचय</h1>
                <p class="text-2xl md:text-3xl text-yellow-400 font-bold mb-6 drop-shadow-lg tracking-wide">The most trusted matrimony service for Digambar Jain!</p>
                <p class="text-lg md:text-xl text-gray-200 leading-relaxed max-w-xl md:mx-0">This website is created only for the Digambar Jain community to help eligible young men and women of the entire Digambar Jain society find their suitable life partner.</p>
            </div>
            
            <!-- Right Side: Image -->
            <div class="flex justify-center md:justify-end mt-12 md:mt-0">
                <img src="assets/images/gallery/TEMP1.jpg" alt="Matrimony Couple" class="w-full max-w-[650px] h-auto rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] border-4 border-white/30 transform hover:scale-[1.02] transition duration-500">
            </div>
        </div>
    </div>
</section>

<!-- Quick Search Section -->
<section class="bg-light relative z-20 mt-36">
    <div class="container mx-auto px-4 -mt-16 md:-mt-24 mb-12">
        <div id="quick-search"
            class="bg-white bg-opacity-95 p-6 rounded-xl shadow-2xl max-w-6xl mx-auto backdrop-blur-sm border-t-4 border-primary"
            data-aos="fade-up" data-aos-delay="200">
            <h3 class="text-xl font-bold text-dark mb-4 border-b pb-2"><i
                    class="fas fa-search text-primary mr-2"></i>Quick Search</h3>
            <?php if (!$is_logged_in): ?>
                <div class="text-center py-6">
                    <p class="text-lg text-gray-700 mb-4">Please login or register to search profiles.</p>
                    <a href="login.php" class="inline-block bg-primary text-white px-8 py-3 rounded-md font-bold shadow-md hover:bg-opacity-90 transition"><i class="fas fa-sign-in-alt mr-2"></i>Login to Search</a>
                </div>
            <?php elseif (!$is_approved): ?>
                <div class="text-center py-6">
                    <p class="text-xl text-yellow-600 font-bold mb-2"><i class="fas fa-clock mr-2"></i>Profile Pending Approval</p>
                    <p class="text-gray-700">Your profile is pending approval. Search will be available after admin approval.</p>
                </div>
            <?php else: ?>
                <form action="profiles.php" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Looking For -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Looking For</label>
                            <select name="gender"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="bride">Bride</option>
                                <option value="groom">Groom</option>
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
                            <select name="marital_status"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Any</option>
                                <option value="never_married">Never Married</option>
                                <option value="widow">Widow / Widower</option>
                                <option value="divorcee">Divorcee</option>
                            </select>
                        </div>
                        <!-- Manglik -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Manglik Status</label>
                            <select name="manglik"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2.5 border bg-gray-50">
                                <option value="">Any</option>
                                <option value="manglik">Manglik</option>
                                <option value="non_manglik">Non-Manglik</option>
                                <option value="anshik_manglik">Anshik Manglik</option>
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
                            <select name="profession"
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
        <h3 class="text-xl md:text-2xl font-bold text-yellow-800 mb-2">
            <i class="fas fa-book-open mr-2"></i> Free Registration
        </h3>
        <p class="text-lg text-yellow-700">
            If you want your photo printed in our matrimony book, a fee of Rs. 1000/- is required.
        </p>
        <p class="text-md text-yellow-600 mt-2 font-medium">
            Kindly scan the QR code to pay Rs. 1000/- and mention your Mobile No. in Payment Remarks.
        </p>
        <div class="mt-4 flex justify-center">
            <?php 
            $payment_qr_code = $settings['payment_qr_code'] ?? 'assets/images/qr_code.jpg';
            ?>
            <img src="<?= htmlspecialchars($payment_qr_code) ?>" alt="Payment QR" class="w-48 h-48 border border-yellow-300 rounded shadow-sm">
        </div>
        <div class="mt-6">
            <a href="registration.php" class="inline-block bg-primary text-white px-8 py-3 rounded-md shadow-lg hover:bg-opacity-90 transition font-bold">Register Now</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($home_top_ads)): ?>
<!-- Advertisements (Home Top) -->
<section class="py-8 bg-white border-b border-gray-100">
    <div class="container mx-auto px-4">
        <div class="flex flex-col gap-6 items-center">
            <?php foreach($home_top_ads as $ad): ?>
                <?php 
                $img_path = str_replace('../', '', $ad['image']); 
                $img_src = file_exists($img_path) ? $img_path : 'assets/images/placeholder.jpg';
                ?>
                <a href="<?= htmlspecialchars($ad['link'] ?? '#') ?>" target="_blank" class="block w-full max-w-5xl rounded-xl overflow-hidden shadow-md hover:shadow-lg transition">
                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-auto object-cover">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Profiles Section -->
<section id="latest" class="py-16 bg-light">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-3 relative inline-block">Latest Profiles
                <span class="absolute bottom-0 left-1/4 w-1/2 h-1 bg-primary rounded-full -mb-2"></span>
            </h2>
            <p class="text-gray-600 mt-4">Find your life partner from our newly registered members</p>
        </div>

        <?php
        $latest_gender = $_GET['latest_gender'] ?? 'Bride';
        if (!in_array($latest_gender, ['Bride', 'Groom'])) {
            $latest_gender = 'Bride';
        }
        ?>
        <div class="flex flex-wrap justify-center mb-8" data-aos="fade-up" data-aos-delay="100">
            <a href="?latest_gender=Bride#latest"
                class="<?= $latest_gender === 'Bride' ? 'bg-primary text-white' : 'bg-white text-dark hover:bg-gray-100 border border-r-0' ?> px-8 py-2.5 rounded-l-full font-bold focus:outline-none transition shadow-md">Latest
                Brides</a>
            <a href="?latest_gender=Groom#latest"
                class="<?= $latest_gender === 'Groom' ? 'bg-primary text-white' : 'bg-white text-dark hover:bg-gray-100 border border-l-0' ?> px-8 py-2.5 rounded-r-full font-bold focus:outline-none transition shadow-md">Latest
                Grooms</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <?php
            // Check if user or admin is logged in (to allow photo viewing)
            $is_logged_in = false;
            $is_approved = false;

            if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
                $is_logged_in = true;
                $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user_status = $stmt->fetchColumn();
                // 'approved' = profile publicly visible; 'account_approved' = account verified, can view photos
                if (in_array($user_status, ['approved', 'account_approved'])) {
                    $is_approved = true;
                }
            } else if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                $is_logged_in = true;
                $is_approved = true;
            }

            // Fetch 4 latest profiles based on selected gender
            // Show 'approved' profiles + 'pending' (form submitted, awaiting profile approval)
            // Cards show as blurred/locked for non-logged-in visitors
            $gender_db = ($latest_gender === 'Bride') ? 'Female' : 'Male';
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
                                    <i class="fas fa-lock text-3xl mb-2"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div
                            class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black via-black/70 to-transparent p-4 z-20">
                            <a href="<?= $link ?>"
                                class="text-white font-bold text-lg hover:underline"><?= htmlspecialchars($p['full_name']) ?></a>
                            <p class="text-gray-200 text-sm font-medium"><?= $p['computed_age'] ?> Yrs,
                                <?= htmlspecialchars($p['height'] ?? 'N/A') ?>
                            </p>
                        </div>
                        <?php if (isset($p['created_at']) && strtotime($p['created_at']) > strtotime('-7 days')): ?>
                            <div
                                class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded shadow z-20">
                                New</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-5">
                        <div class="space-y-2 mb-4">
                            <p class="text-sm text-gray-600 flex items-center"><i
                                    class="fas fa-graduation-cap w-6 text-primary mr-2"></i>
                                <?= htmlspecialchars($p['higher_education'] ?? 'N/A') ?></p>
                            <p class="text-sm text-gray-600 flex items-center"><i
                                    class="fas fa-briefcase w-6 text-primary mr-2"></i>
                                <?= htmlspecialchars($p['occupation'] ?? 'N/A') ?></p>
                            <p class="text-sm text-gray-600 flex items-center"><i
                                    class="fas fa-map-marker-alt w-6 text-primary mr-2"></i>
                                <?= htmlspecialchars($p['native_place'] ?? 'N/A') ?></p>
                        </div>
                        <a href="<?= $link ?>"
                            class="block text-center bg-gray-50 border border-primary text-primary hover:bg-primary hover:text-white py-2 rounded-md transition font-semibold">View
                            Profile</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="profiles.php?gender=<?= urlencode($latest_gender) ?>"
                class="inline-block bg-primary text-white px-8 py-3 rounded-md shadow-lg hover:bg-opacity-90 transition font-bold text-lg"><i
                    class="fas fa-users mr-2"></i>View All Profiles</a>
        </div>
    </div>
</section>

<!-- Find Matches Section -->
<section class="py-16 bg-white border-t border-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-3 relative inline-block">Find Matches By Category
                <span class="absolute bottom-0 left-1/4 w-1/2 h-1 bg-primary rounded-full -mb-2"></span>
            </h2>
            <p class="text-gray-600 mt-4">Find matches based on your specific preferences</p>
        </div>

        <div class="flex flex-wrap justify-center gap-4" data-aos="fade-up" data-aos-delay="100">
            <a href="profiles.php?gender=Bride"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-female mr-2 text-primary group-hover:text-white"></i> All Brides</a>
            <a href="profiles.php?gender=Groom"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-male mr-2 text-primary group-hover:text-white"></i> All Grooms</a>
            <a href="profiles.php?education=Doctorate"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-user-md mr-2 text-primary"></i> Doctors</a>
            <a href="profiles.php?education=Engineer"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-hard-hat mr-2 text-primary"></i> Engineers</a>
            <a href="profiles.php?education=MBA"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-user-graduate mr-2 text-primary"></i> MBA/MCA</a>
            <a href="profiles.php?education=CA"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-calculator mr-2 text-primary"></i> CA/CS</a>
            <a href="profiles.php?occupation=Business"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-briefcase mr-2 text-primary"></i> Business</a>
            <a href="profiles.php?occupation=Service"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-laptop-house mr-2 text-primary"></i> Service</a>
            <a href="profiles.php"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-plane mr-2 text-primary"></i> NRI</a>
            <a href="profiles.php?manglik=yes"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-om mr-2 text-primary"></i> Manglik</a>
            <a href="profiles.php?marital=Widow"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-user-alt-slash mr-2 text-primary"></i> Widow</a>
            <a href="profiles.php?marital=Divorce"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-heart-broken mr-2 text-primary"></i> Divorcee</a>
            <a href="profiles.php?marital=Widower"
                class="bg-light border border-gray-200 text-dark px-6 py-3 rounded-md hover:bg-primary hover:text-white hover:border-primary transition shadow-sm font-semibold flex items-center"><i
                    class="fas fa-user-slash mr-2 text-primary"></i> Widower</a>
        </div>
    </div>
</section>

<!-- Browse Directory (Location & Sect) -->
<section class="py-16 bg-light border-y border-gray-200">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            
            <!-- Browse By City -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="0">
                <h3 class="text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-city text-primary mr-2"></i>Browse By City</h3>
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
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-map text-primary mr-2"></i>Browse By State</h3>
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
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-globe text-primary mr-2"></i>Browse By Country</h3>
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
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-xl font-bold text-dark mb-4 border-b-2 border-primary pb-2 flex items-center"><i class="fas fa-praying-hands text-primary mr-2"></i>Browse By Sect</h3>
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
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- News & Updates -->
            <div data-aos="fade-right">
                <div class="flex justify-between items-center mb-6 border-b pb-2">
                    <h2 class="text-3xl font-bold text-dark flex items-center"><i
                            class="fas fa-newspaper text-primary mr-3 text-2xl"></i>News & Updates</h2>
                    <a href="news.php"
                        class="bg-light text-primary px-4 py-1.5 rounded-md hover:bg-primary hover:text-white transition text-sm font-bold shadow-sm">View
                        All</a>
                </div>

                <div class="space-y-4">
                    <!-- News items will be loaded dynamically from admin panel -->
                </div>
            </div>

            <!-- Stats section -->
            <div class="bg-light p-8 rounded-2xl border border-gray-100" data-aos="fade-left">
                <h2 class="text-3xl font-bold text-dark mb-4 text-center lg:text-left">Trusted by Thousands of Digambar Jain Samaj since 5 years</h2>

                <div class="grid grid-cols-2 gap-6">
                    <div
                        class="bg-white p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-14 h-14 mx-auto bg-red-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-heart text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-3xl font-bold text-dark mb-1" data-target="5000">5000+</div>
                        <p class="text-sm text-gray-500 font-semibold">Happy Marriages</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-14 h-14 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-users text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-3xl font-bold text-dark mb-1" data-target="25000">25000+</div>
                        <p class="text-sm text-gray-500 font-semibold">Verified Profiles</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-14 h-14 mx-auto bg-green-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-globe-asia text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-3xl font-bold text-dark mb-1" data-target="100">100+</div>
                        <p class="text-sm text-gray-500 font-semibold">Cities Covered</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-xl text-center shadow-sm border border-gray-100 hover:border-primary transition group">
                        <div
                            class="w-14 h-14 mx-auto bg-yellow-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary transition">
                            <i class="fas fa-award text-2xl text-primary group-hover:text-white"></i>
                        </div>
                        <div class="counter text-3xl font-bold text-dark mb-1" data-target="15">15+</div>
                        <p class="text-sm text-gray-500 font-semibold">Years of Trust</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php if (!empty($home_bottom_ads)): ?>
<!-- Advertisements (Home Bottom) -->
<section class="py-8 bg-gray-50 border-t border-gray-200">
    <div class="container mx-auto px-4">
        <div class="flex flex-col gap-6 items-center">
            <?php foreach($home_bottom_ads as $ad): ?>
                <?php 
                $img_path = str_replace('../', '', $ad['image']); 
                $img_src = file_exists($img_path) ? $img_path : 'assets/images/placeholder.jpg';
                ?>
                <a href="<?= htmlspecialchars($ad['link'] ?? '#') ?>" target="_blank" class="block w-full max-w-5xl rounded-xl overflow-hidden shadow-md hover:shadow-lg transition">
                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-auto object-cover">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>