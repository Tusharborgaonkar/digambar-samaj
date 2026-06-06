<?php 
require_once '../includes/db.php';
$current_page = 'dashboard.php';

// Fetch Metrics
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status != 'blocked'");
$totalMembers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM user_memberships WHERE status = 'active'");
$activeSubscriptions = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'");
$pendingApprovals = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'verified' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$monthlyRevenue = $stmt->fetchColumn() ?: 0;

// Fetch Recent Registrations
$stmt = $pdo->query("SELECT profile_id, full_name, created_at, status FROM users ORDER BY created_at DESC LIMIT 5");
$recentRegistrations = $stmt->fetchAll();

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>
<!-- Dashboard Content -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Welcome back, Admin</h3>
        <p class="text-gray-500 text-sm">Here's what's happening with your platform today.</p>
    </div>
    <div>
        <a href="../index.php" target="_blank" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm">
            <i class="fas fa-external-link-alt mr-2 text-gray-400"></i> View Website
        </a>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Total Members -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Members</p>
            <h4 class="text-3xl font-bold text-gray-800"><?= number_format($totalMembers) ?></h4>
            <p class="text-xs text-gray-400 font-medium mt-1">Platform active members</p>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-xl">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Active Subscriptions</p>
            <h4 class="text-3xl font-bold text-gray-800"><?= number_format($activeSubscriptions) ?></h4>
            <p class="text-xs text-gray-400 font-medium mt-1">Currently paid members</p>
        </div>
        <div class="w-12 h-12 bg-yellow-50 text-yellow-500 rounded-lg flex items-center justify-center text-xl">
            <i class="fas fa-crown"></i>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Pending Approvals</p>
            <h4 class="text-3xl font-bold text-gray-800"><?= number_format($pendingApprovals) ?></h4>
            <p class="text-xs text-red-500 font-medium mt-1"><i class="fas fa-exclamation-circle mr-1"></i> Needs your attention</p>
        </div>
        <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-lg flex items-center justify-center text-xl">
            <i class="fas fa-user-clock"></i>
        </div>
    </div>

    <!-- Revenue -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Monthly Revenue</p>
            <h4 class="text-3xl font-bold text-gray-800">₹<?= number_format($monthlyRevenue) ?></h4>
            <p class="text-xs text-gray-400 font-medium mt-1">This month's collections</p>
        </div>
        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-lg flex items-center justify-center text-xl">
            <i class="fas fa-rupee-sign"></i>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Quick Actions -->
    <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h4 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Quick Actions</h4>
        <div class="space-y-3">
            <a href="members.php" class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 hover:border-primary transition group">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary/10 text-primary rounded-md flex items-center justify-center mr-3 group-hover:bg-primary group-hover:text-white transition">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <span class="font-medium text-gray-700 group-hover:text-primary transition">Review New Profiles</span>
                </div>
                <i class="fas fa-chevron-right text-gray-300 group-hover:text-primary transition"></i>
            </a>
            
            <a href="membership-plans.php" class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 hover:border-primary transition group">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary/10 text-primary rounded-md flex items-center justify-center mr-3 group-hover:bg-primary group-hover:text-white transition">
                        <i class="fas fa-tag"></i>
                    </div>
                    <span class="font-medium text-gray-700 group-hover:text-primary transition">Update Pricing Plans</span>
                </div>
                <i class="fas fa-chevron-right text-gray-300 group-hover:text-primary transition"></i>
            </a>
            
            <a href="advertisement.php" class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 hover:border-primary transition group">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary/10 text-primary rounded-md flex items-center justify-center mr-3 group-hover:bg-primary group-hover:text-white transition">
                        <i class="fas fa-image"></i>
                    </div>
                    <span class="font-medium text-gray-700 group-hover:text-primary transition">Manage Hero Banners</span>
                </div>
                <i class="fas fa-chevron-right text-gray-300 group-hover:text-primary transition"></i>
            </a>
        </div>
    </div>

    <!-- Recent Registrations (Dummy Table) -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h4 class="text-lg font-bold text-gray-800">Recent Registrations</h4>
            <a href="members.php" class="text-sm text-primary font-medium hover:underline">View All</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-sm text-gray-500 uppercase border-b border-gray-100 bg-gray-50">
                        <th class="py-3 px-4 font-semibold rounded-tl-lg">Profile ID</th>
                        <th class="py-3 px-4 font-semibold">Name</th>
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach ($recentRegistrations as $user): ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="py-3 px-4 text-gray-600 font-medium"><?= htmlspecialchars($user['profile_id'] ?? 'N/A') ?></td>
                        <td class="py-3 px-4 text-gray-800 font-bold"><?= htmlspecialchars($user['full_name']) ?></td>
                        <td class="py-3 px-4 text-gray-500"><?= date('M d, Y h:i A', strtotime($user['created_at'])) ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if($user['status'] === 'approved'): ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Approved</span>
                            <?php elseif($user['status'] === 'rejected'): ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Denied</span>
                            <?php elseif($user['status'] === 'blocked'): ?>
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold">Blocked</span>
                            <?php else: ?>
                                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentRegistrations)): ?>
                    <tr>
                        <td colspan="4" class="py-4 px-4 text-center text-gray-500">No recent registrations.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>
