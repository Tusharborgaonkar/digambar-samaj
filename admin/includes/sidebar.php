<?php
// Get current page name to highlight active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside class="w-64 bg-admin_sidebar text-white flex-shrink-0 hidden md:flex flex-col transition-all duration-300 z-20">
    <!-- Admin Logo / Branding -->
    <div class="h-16 flex items-center justify-center border-b border-gray-700 bg-gray-900 px-4">
        <a href="dashboard.php" class="flex flex-col items-center text-center">
            <span class="text-xl font-bold text-white tracking-wide">Jain Digambar</span>
            <span class="text-[10px] text-accent uppercase tracking-widest font-semibold">Admin Panel</span>
        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto sidebar-scroll py-4">
        <nav class="space-y-1 px-3">
            
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'dashboard.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Dashboard</span>
            </a>

            <!-- Members Section -->
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">User Management</p>
            </div>
            
            <a href="members.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'members.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>All Members</span>
            </a>
            <a href="account-approvals.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'account-approvals.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Account Approvals</span>
            </a>
            <a href="members-approval.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'members-approval.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Profile Approvals</span>
            </a>
            <a href="members-approved.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'members-approved.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Approved Members</span>
            </a>
            <a href="members-rejected.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'members-rejected.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Denied Members</span>
            </a>
            <a href="members-blocked.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'members-blocked.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Blocked Members</span>
            </a>
            <a href="members-paid.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'members-paid.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Paid Members</span>
            </a>
            <a href="members-requests.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'members-requests.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span class="text-sm leading-tight">Deactivation/Deletion Requests</span>
            </a>

            <!-- System Settings -->
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">System Settings</p>
            </div>

            <a href="membership-plans.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'membership-plans.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Membership Plans</span>
            </a>
            
            <a href="payment.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'payment.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Payment Management</span>
            </a>

            <a href="registration-visibility.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'registration-visibility.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span class="text-sm leading-tight">Registration Form Setup</span>
            </a>
            
            <a href="advertisement.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'advertisement.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Hero Advertisements</span>
            </a>
            
            <a href="success-stories.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'success-stories.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Success Stories</span>
            </a>
            
            <a href="gallery.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'gallery.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Photo Gallery</span>
            </a>
            
            <a href="contacts.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'contacts.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Contact Messages</span>
            </a>

            <!-- Analytics -->
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Analytics</p>
            </div>

            <a href="reports.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'reports.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Reports & Stats</span>
            </a>

            <!-- Admin Settings -->
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin</p>
            </div>

            <a href="settings.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $current_page == 'settings.php' ? 'bg-primary text-white font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span>Settings</span>
            </a>
            
            <a href="login.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors text-red-400 hover:bg-red-500/10 hover:text-red-300 mt-4">
                <span>Logout</span>
            </a>

        </nav>
    </div>
</aside>

<!-- Mobile Header / Hamburger -->
<div class="md:hidden bg-admin_sidebar text-white h-16 flex items-center justify-between px-4 fixed w-full z-30">
    <a href="dashboard.php" class="flex items-center">
        <span class="font-bold text-lg">Jain Digambar</span>
    </a>
    <button id="mobileMenuBtn" class="text-white focus:outline-none">
        <i class="fas fa-bars text-2xl"></i>
    </button>
</div>

<!-- Main Content Wrapper -->
<div class="flex-1 flex flex-col h-screen overflow-hidden pt-16 md:pt-0">
    
    <!-- Top Header Navigation -->
    <header class="bg-white h-16 border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0 z-10 shadow-sm">
        <div class="flex items-center">
            <h2 class="text-xl font-semibold text-gray-800 capitalize hidden md:block">
                <?= str_replace(['.php', '-'], ['', ' '], $current_page) ?>
            </h2>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="relative">
                <button class="flex items-center space-x-2 focus:outline-none">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=1E3A5F&color=fff" alt="Admin" class="w-8 h-8 rounded-full border border-gray-300">
                    <span class="text-sm font-medium text-gray-700 hidden sm:block">Super Admin</span>
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Page Content Container -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-light p-6">
