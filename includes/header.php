<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="flex flex-col space-y-6 px-8">
            <a href="index.php" class="text-dark hover:text-primary transition text-lg font-medium">Home</a>
            <div class="relative group">
                <a href="about.php" class="text-dark hover:text-primary transition text-lg font-medium flex items-center gap-2">
                    About Us <i class="fas fa-chevron-down text-xs"></i>
                </a>
                <div class="pl-4 mt-2 space-y-2">
                    <a href="community.php" class="block text-gray-600 hover:text-primary transition">Community</a>
                </div>
            </div>
            <a href="registration.php" class="text-dark hover:text-primary transition text-lg font-medium">Registration</a>
            <a href="success-stories.php" class="text-dark hover:text-primary transition text-lg font-medium">Success Stories</a>
            <a href="gallery.php" class="text-dark hover:text-primary transition text-lg font-medium">Gallery</a>
            <a href="contact.php" class="text-dark hover:text-primary transition text-lg font-medium">Contact</a>
            <a href="my-profile.php" class="text-dark hover:text-primary transition text-lg font-medium">My Profile</a>
            <a href="profiles.php" class="text-dark hover:text-primary transition text-lg font-medium">Find Matches</a>
        </div>
    </div>
    
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 md:px-8 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div data-aos="fade-right">
                    <a href="index.php" class="flex flex-col">
                        <h1 class="text-2xl md:text-3xl font-bold text-primary">Jain Digambar</h1>
                        <span class="text-sm text-secondary">Matrimony <span class="text-xs text-gray-500">Est. 2026</span></span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-dark hover:text-primary transition font-medium">Home</a>
                    <div class="relative group">
                        <a href="about.php" class="text-dark hover:text-primary transition font-medium inline-flex items-center gap-1">
                            About Us <i class="fas fa-chevron-down text-xs"></i>
                        </a>
                        <div class="absolute top-full left-0 mt-2 w-48 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <a href="community.php" class="block px-4 py-2 text-gray-700 hover:bg-primary hover:text-white rounded-t-lg transition">Community</a>
                        </div>
                    </div>
                    <a href="registration.php" class="text-dark hover:text-primary transition font-medium">Registration</a>
                    <a href="success-stories.php" class="text-dark hover:text-primary transition font-medium">Success Stories</a>
                    <a href="gallery.php" class="text-dark hover:text-primary transition font-medium">Gallery</a>
                    <a href="contact.php" class="text-dark hover:text-primary transition font-medium">Contact</a>
                    <a href="my-profile.php" class="text-dark hover:text-primary transition font-medium">My Profile</a>
                    <a href="profiles.php" class="text-dark hover:text-primary transition font-medium">Find Matches</a>
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