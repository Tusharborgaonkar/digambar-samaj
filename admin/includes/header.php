<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Dashboard - Jain Digambar Matrimony</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Config (Matching Main Site + Admin Specifics) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4338CA',     // Indigo 700
                        secondary: '#DB2777',   // Pink 600
                        accent: '#8B5CF6',      // Violet 500
                        dark: '#1E293B',        // Slate 800
                        light: '#F8FAFC',       // Slate 50 for admin background
                        admin_sidebar: '#0F172A',// Slate 900 for sidebar
                    },
                    fontFamily: {
                        'sans': ['system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Custom Scrollbar for Sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
        
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #F3F4F6;
        }
    </style>
</head>
<body class="bg-light text-gray-800 h-screen flex overflow-hidden">
