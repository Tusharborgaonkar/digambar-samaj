<?php
require_once '../includes/db.php';
$current_page = 'members-rejected.php';

// Handle status updates (Approve, Hold, Block or Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $action = $_POST['action'];
    $userId = (int)$_POST['user_id'];
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif ($action === 'hold') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'pending' WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif ($action === 'block') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    header("Location: members-rejected.php");
    exit;
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// Get total rejected records
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE status = 'rejected'");
$countStmt->execute();
$total_records = $countStmt->fetchColumn();
$total_pages = max(1, ceil($total_records / $limit));

// Fetch rejected members from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'rejected' ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();

$start_result = $offset + 1;
$end_result = min($offset + $limit, $total_records);
if ($total_records == 0) {
    $start_result = 0;
    $end_result = 0;
}
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Denied Members</h3>
        <p class="text-gray-500 text-sm">Members whose profiles were denied during the verification process.</p>
    </div>
    <div class="flex gap-2">
        <button class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm flex items-center">
            <i class="fas fa-download mr-2"></i> Export
        </button>
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
                    <th class="py-4 px-6 font-semibold">Contact Info</th>
                    <th class="py-4 px-6 font-semibold">Registration Date</th>
                    <th class="py-4 px-6 font-semibold">Status</th>
                    <th class="py-4 px-6 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php foreach ($members as $member): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-4 px-6">
                        <input type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center">
                            <?php
                            $photo_path = !empty($member['profile_photo']) ? '../' . $member['profile_photo'] : '';
                            $photo = ($photo_path && file_exists($photo_path)) ? htmlspecialchars($photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($member['full_name']);
                            ?>
                            <img src="<?= $photo ?>" class="w-10 h-10 rounded-full object-cover mr-3 border border-gray-200" alt="Profile Photo">
                            <div>
                                <p class="font-bold text-gray-800"><?= htmlspecialchars($member['full_name']) ?></p>
                                <p class="text-xs text-gray-500">MID: <?= htmlspecialchars($member['profile_id'] ?? 'N/A') ?> • <?= htmlspecialchars($member['gender'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <p class="text-gray-800"><i class="fas fa-envelope text-gray-400 w-4"></i> <?= htmlspecialchars($member['email'] ?? 'N/A') ?></p>
                        <p class="text-gray-500 text-xs mt-1"><i class="fas fa-phone text-gray-400 w-4"></i> <?= htmlspecialchars($member['mobile'] ?? 'N/A') ?></p>
                    </td>
                    <td class="py-4 px-6 text-gray-600">
                        <?= date('M d, Y', strtotime($member['created_at'])) ?><br>
                        <span class="text-xs text-gray-400"><?= date('h:i A', strtotime($member['created_at'])) ?></span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Denied
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <a href="../profile-details.php?id=<?= $member['id'] ?>" class="inline-block text-blue-600 hover:text-blue-900 mx-1 p-1 tooltip" title="View Profile"><i class="fas fa-eye"></i></a>
                        
                        <form method="POST" class="inline-block m-0 p-0">
                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                            <button type="submit" name="action" value="approve" class="text-green-600 hover:text-green-900 mx-1 p-1 tooltip" title="Approve"><i class="fas fa-check-circle"></i></button>
                            <button type="submit" name="action" value="hold" class="text-yellow-600 hover:text-yellow-900 mx-1 p-1 tooltip" title="Hold Profile"><i class="fas fa-pause-circle"></i></button>
                            <button type="submit" name="action" value="block" class="text-gray-600 hover:text-gray-900 mx-1 p-1 tooltip" title="Block User"><i class="fas fa-ban"></i></button>
                            <button type="submit" name="action" value="delete" class="text-red-600 hover:text-red-900 mx-1 p-1 tooltip" title="Delete Profile" onclick="return confirm('Are you sure you want to completely delete this profile?');"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($members)): ?>
                <tr>
                    <td colspan="6" class="py-4 px-6 text-center text-gray-500">No denied members found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between">
        <div class="text-sm text-gray-500 mb-4 sm:mb-0">
            Showing <span class="font-medium text-gray-800"><?= $start_result ?></span> to <span class="font-medium text-gray-800"><?= $end_result ?></span> of <span class="font-medium text-gray-800"><?= $total_records ?></span> results
        </div>
        <div class="flex space-x-1">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">Previous</a>
            <?php else: ?>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <?php endif; ?>

            <?php 
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, max(1, $page + 2));
            
            if ($start_page > 1) {
                echo '<a href="?page=1" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">1</a>';
                if ($start_page > 2) {
                    echo '<span class="px-3 py-1 text-gray-500 flex items-center justify-center">...</span>';
                }
            }
            
            for ($i = $start_page; $i <= $end_page; $i++): 
                if ($i == $page): ?>
                    <button class="px-3 py-1 border border-primary bg-primary text-white rounded text-sm font-medium"><?= $i ?></button>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50"><?= $i ?></a>
                <?php endif; 
            endfor; 
            
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<span class="px-3 py-1 text-gray-500 flex items-center justify-center">...</span>';
                }
                echo '<a href="?page=' . $total_pages . '" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">' . $total_pages . '</a>';
            }
            ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">Next</a>
            <?php else: ?>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
