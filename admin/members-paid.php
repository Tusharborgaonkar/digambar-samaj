<?php
require_once '../includes/db.php';

// Check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$current_page = 'members-paid.php';

// Fetch paid members
$sql = "SELECT um.*, u.full_name, u.profile_id, u.profile_photo, u.gender, m.plan_name, m.price, p.payment_method 
        FROM user_memberships um 
        JOIN users u ON um.user_id = u.id 
        JOIN memberships m ON um.membership_id = m.id 
        LEFT JOIN payments p ON p.user_id = u.id AND p.membership_id = m.id
        WHERE um.status = 'active'
        ORDER BY um.created_at DESC";
$stmt = $pdo->query($sql);
$memberships = $stmt->fetchAll();

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Paid Members</h3>
        <p class="text-gray-500 text-sm">Members with active subscriptions.</p>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="py-4 px-6 font-semibold w-16">
                        <input type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-4 px-6 font-semibold">Profile</th>
                    <th class="py-4 px-6 font-semibold">Plan Details</th>
                    <th class="py-4 px-6 font-semibold">Expiry Date</th>
                    <th class="py-4 px-6 font-semibold">Status</th>
                    <th class="py-4 px-6 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php foreach ($memberships as $sub): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-4 px-6">
                        <input type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center">
                            <?php
                            $photo_path_real = !empty($sub['profile_photo']) ? '../' . $sub['profile_photo'] : '';
                            $photo = ($photo_path_real && file_exists($photo_path_real)) ? '../image.php?file=' . urlencode($sub['profile_photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($sub['full_name']);
                            ?>
                            <img src="<?= $photo ?>" class="w-10 h-10 rounded-full object-cover mr-3 border border-gray-200" alt="Profile Photo">
                            <div>
                                <p class="font-bold text-gray-800"><?= htmlspecialchars($sub['full_name']) ?></p>
                                <p class="text-xs text-gray-500">ID: <?= htmlspecialchars($sub['profile_id'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <p class="text-gray-800 font-bold"><?= htmlspecialchars($sub['plan_name']) ?></p>
                        <p class="text-gray-500 text-xs mt-1">₹<?= number_format($sub['price']) ?> paid <?= !empty($sub['payment_method']) ? 'via ' . htmlspecialchars($sub['payment_method']) : '' ?></p>
                    </td>
                    <td class="py-4 px-6 text-gray-600">
                        <?= !empty($sub['end_date']) ? date('M d, Y', strtotime($sub['end_date'])) : 'Lifetime' ?>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $sub['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ucfirst($sub['status']) ?>
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <button class="text-blue-600 hover:text-blue-900 mx-1 p-1 tooltip" title="View Transaction"><i class="fas fa-file-invoice"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($memberships)): ?>
                <tr>
                    <td colspan="6" class="py-4 px-6 text-center text-gray-500">No paid members found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
