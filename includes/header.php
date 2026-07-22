<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$user_status = null;
$registration_link = 'pre-register.php';
$show_registration = true;
$current_page = basename($_SERVER['PHP_SELF']);
$registration_link = 'pre-register.php';
$show_registration = true;

if ($is_logged_in && isset($_SESSION['user_id'])) {
    if (file_exists('includes/db.php')) {
        require_once 'includes/db.php';
        if (isset($pdo)) {
            $stmtHdr = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $stmtHdr->execute([$_SESSION['user_id']]);
            $hdrUser = $stmtHdr->fetch(PDO::FETCH_ASSOC);
            if ($hdrUser) {
                $user_status = $hdrUser['status'];
                if ($user_status === 'account_approved') {
                    $registration_link = 'registration.php';
                    $allowed_pages = ['registration.php', 'login.php', 'pre-register.php'];
                    if (!in_array($current_page, $allowed_pages)) {
                        header('Location: registration.php');
                        exit;
                    }
                } elseif ($user_status === 'active' || $user_status === 'pending' || $user_status === 'account_pending' || $user_status === 'approved') {
                    $show_registration = false;
                }
            }
        }
    }
} elseif ($is_admin_logged_in) {
    $show_registration = false;
}

// Fetch side ads
$left_ads = [];
$right_ads = [];
if (isset($pdo)) {
    try {
        $stmtAds = $pdo->query("SELECT * FROM advertisements WHERE status = 1 AND position IN ('left_side', 'right_side') ORDER BY created_at DESC");
        while ($ad = $stmtAds->fetch(PDO::FETCH_ASSOC)) {
            if ($ad['position'] === 'left_side') {
                $left_ads[] = $ad;
            } else {
                $right_ads[] = $ad;
            }
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Jain Digambar Matrimony - India's most trusted matrimony platform exclusively for the Digambar Jain Samaj. Find your perfect life partner within the community. Established 2026.">
    <meta name="keywords" content="Jain Matrimony, Digambar Jain, Jain Marriage, Jain Brides, Jain Grooms, Digambar Jain Samaj, Matrimony Website">
    <meta name="author" content="Jain Digambar Matrimony">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Jain Digambar Matrimony - Exclusive Matrimony for Digambar Jain Samaj">
    <meta property="og:description" content="Find your perfect life partner within the Digambar Jain community. Trusted matrimony platform established in 2026.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.jaindigambarmatrimony.com">
    <meta property="og:image" content="https://images.unsplash.com/photo-1516594798947-e65505dbb29d?w=1200">
    <meta property="og:site_name" content="Jain Digambar Matrimony">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Jain Digambar Matrimony - Exclusive Matrimony for Digambar Jain Samaj">
    <meta name="twitter:description" content="Find your perfect life partner within the Digambar Jain community.">
    <meta name="twitter:image" content="https://images.unsplash.com/photo-1516594798947-e65505dbb29d?w=1200">
    
    <title>Jain Digambar Matrimony - Exclusive Matrimony for Digambar Jain Samaj</title>
    
    <!-- Tailwind CSS CDN -->
    <script>
        // Suppress Tailwind CDN production warning
        const originalWarn = console.warn;
        console.warn = function(...args) {
            if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            originalWarn.apply(console, args);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E3A5F',     // Navy Blue
                        secondary: '#C97B84',   // Rose Gold
                        accent: '#C97B84',      // Rose Gold
                        dark: '#2D2D2D',        // Dark Gray
                        light: '#FAFAFA',       // Off White
                    },
                    fontFamily: {
                        'serif': ['Georgia', 'Cambria', 'serif'],
                        'sans': ['system-ui', '-apple-system', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
    
    <!-- Swiper JS CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Global SweetAlert Handler -->
    <?php if (!empty($success) || !empty($success_msg)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= htmlspecialchars(addslashes($success ?? ($success_msg ?? ''))) ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        </script>
    <?php endif; ?>
    <?php if (!empty($error) || !empty($error_msg)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?= htmlspecialchars(addslashes($error ?? ($error_msg ?? ''))) ?>'
                });
            });
        </script>
    <?php endif; ?>
    
    <!-- Custom CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }
        
        /* Hamburger Menu Animation */
        .hamburger {
            cursor: pointer;
            width: 30px;
            height: 24px;
            position: relative;
            transform: rotate(0deg);
            transition: .5s ease-in-out;
        }
        
        .hamburger span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: #0f172a;
            border-radius: 9px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }
        
        .hamburger span:nth-child(1) { top: 0px; }
        .hamburger span:nth-child(2) { top: 10px; }
        .hamburger span:nth-child(3) { top: 20px; }
        
        .hamburger.active span:nth-child(1) {
            top: 10px;
            transform: rotate(135deg);
        }
        
        .hamburger.active span:nth-child(2) {
            opacity: 0;
            left: -60px;
        }
        
        .hamburger.active span:nth-child(3) {
            top: 10px;
            transform: rotate(-135deg);
        }
        
        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 320px;
            height: 100vh;
            background: white;
            z-index: 1000;
            transition: right 0.4s cubic-bezier(0.77, 0.2, 0.05, 1);
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            padding-top: 80px;
        }
        
        .mobile-menu.active {
            right: 0;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #1E3A5F;
            border-radius: 4px;
        }
        
        /* Form Styles */
        input, select, textarea {
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #1E3A5F;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
        }
        
        /* Animation Keyframes */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="bg-light">

    <!-- Left Side Ads (Desktop Only) -->
    <?php if(!empty($left_ads)): ?>
    <div class="hidden xl:block fixed left-0 top-1/2 transform -translate-y-1/2 z-40 w-[160px] max-h-[80vh] overflow-y-auto" style="padding-left: 10px;">
        <div class="flex flex-col space-y-4">
            <?php foreach($left_ads as $ad): 
                $img_path = ltrim(str_replace('../', '', $ad['image']), '/\\');
            ?>
                <?php if(!empty($ad['link'])): ?>
                    <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="block w-full hover:opacity-90 transition">
                        <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="w-full h-auto rounded shadow-md border border-gray-200">
                    </a>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="w-full h-auto rounded shadow-md border border-gray-200">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Right Side Ads (Desktop Only) -->
    <?php if(!empty($right_ads)): ?>
    <div class="hidden xl:block fixed right-0 top-1/2 transform -translate-y-1/2 z-40 w-[160px] max-h-[80vh] overflow-y-auto" style="padding-right: 10px;">
        <div class="flex flex-col space-y-4">
            <?php foreach($right_ads as $ad): 
                $img_path = ltrim(str_replace('../', '', $ad['image']), '/\\');
            ?>
                <?php if(!empty($ad['link'])): ?>
                    <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="block w-full hover:opacity-90 transition">
                        <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="w-full h-auto rounded shadow-md border border-gray-200">
                    </a>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="w-full h-auto rounded shadow-md border border-gray-200">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <button id="closeMobileMenu" class="absolute top-6 right-6 text-2xl text-gray-600 hover:text-red-500 transition focus:outline-none"><i class="fas fa-times"></i></button>
        <div class="flex flex-col space-y-6 px-8 mt-4">
            <a href="index.php" class="<?= $current_page == 'index.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg">Home</a>
            <div class="relative group">
                <a href="about.php" class="<?= $current_page == 'about.php' || $current_page == 'community.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg flex items-center gap-2">
                    About Us <i class="fas fa-chevron-down text-xs"></i>
                </a>
                <div class="pl-4 mt-2 space-y-2">
                    <a href="community.php" class="<?= $current_page == 'community.php' ? 'text-primary font-bold' : 'text-gray-600 hover:text-primary' ?> transition block">Community</a>
                </div>
            </div>
            <a href="success-stories.php" class="<?= $current_page == 'success-stories.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg">Success Story</a>
            <a href="profiles.php" class="<?= $current_page == 'profiles.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg">Find Your Match</a>
            <a href="gallery.php" class="<?= $current_page == 'gallery.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg">Gallery</a>
            <!-- <a href="news.php" class="<?= $current_page == 'news.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg">News & Updates</a> -->
            
            <?php if ($is_logged_in): ?>
                <a href="my-profile.php" class="<?= $current_page == 'my-profile.php' || $current_page == 'registration.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg">My Profile</a>
                <a href="login.php?logout=1" class="text-red-500 hover:text-red-700 transition text-lg font-medium">Logout</a>
            <?php else: ?>
                <a href="login.php" class="<?= $current_page == 'login.php' || $current_page == 'registration.php' || $current_page == 'pre-register.php' ? 'text-primary font-bold' : 'text-dark hover:text-primary font-medium' ?> transition text-lg">Login / Registration</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 md:px-8 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div data-aos="fade-right">
                    <a href="index.php" class="flex flex-col">
                        <h1 class="text-2xl md:text-3xl font-bold text-primary">Digambar Jain Matrimony</h1>
                        <span class="text-sm text-secondary">Matrimony <span class="text-xs text-gray-500">Est. 2026</span></span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="<?= $current_page == 'index.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition pb-1">Home</a>
                    <div class="relative group">
                        <a href="about.php" class="<?= $current_page == 'about.php' || $current_page == 'community.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition inline-flex items-center gap-1 pb-1">
                            About Us <i class="fas fa-chevron-down text-xs"></i>
                        </a>
                        <div class="absolute top-full left-0 mt-2 w-48 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <a href="community.php" class="<?= $current_page == 'community.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-primary hover:text-white' ?> block px-4 py-2 rounded-t-lg transition">Community</a>
                        </div>
                    </div>
                    <a href="success-stories.php" class="<?= $current_page == 'success-stories.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition pb-1">Success Story</a>
                    <a href="profiles.php" class="<?= $current_page == 'profiles.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition pb-1">Find Your Match</a>
                    <a href="gallery.php" class="<?= $current_page == 'gallery.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition pb-1">Gallery</a>
                    <!-- <a href="news.php" class="<?= $current_page == 'news.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition pb-1">News & Updates</a> -->
                    
                    <?php if ($is_logged_in): ?>
                        <a href="my-profile.php" class="<?= $current_page == 'my-profile.php' || $current_page == 'registration.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition pb-1">My Profile</a>
                        <a href="login.php?logout=1" class="text-red-500 hover:text-red-700 transition font-medium">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="<?= $current_page == 'login.php' || $current_page == 'registration.php' || $current_page == 'pre-register.php' ? 'text-primary font-bold border-b-2 border-primary' : 'text-dark hover:text-primary font-medium' ?> transition pb-1">Login / Registration</a>
                    <?php endif; ?>
                </div>
                
                <!-- Hamburger Icon (Mobile) -->
                <div class="md:hidden hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="overflow-x-hidden">