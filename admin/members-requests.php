<?php
require_once '../includes/db.php';

// Check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$current_page = 'members-requests.php';

// Handle processing deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'], $_POST['user_id'])) {
    $action = $_POST['action'];
    $requestId = (int)$_POST['request_id'];
    $userId = (int)$_POST['user_id'];
    
    if ($action === 'process_deletion') {
        // Here you would implement your business logic for account deletion.
        // E.g., setting status to blocked or deleted, or deleting the record.
        // Let's delete the user completely for 'deletion', or mark as blocked for 'deactivation'
        
        $reqStmt = $pdo->prepare("SELECT request_type FROM account_requests WHERE id = ?");
        $reqStmt->execute([$requestId]);
        $reqType = $reqStmt->fetchColumn();
        
        if ($reqType === 'deactivation') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
            $stmt->execute([$userId]);
        } else {
            // Full deletion
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
        }
        
        // Update request status
        $updateReq = $pdo->prepare("UPDATE account_requests SET status = 'processed' WHERE id = ?");
        $updateReq->execute([$requestId]);
    }
    
    header("Location: members-requests.php");
    exit;
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Fetch requests
$sql = "SELECT r.*, u.full_name, u.profile_id, u.profile_photo, u.gender 
        FROM account_requests r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.status = 'pending' 
        ORDER BY r.created_at DESC";
$stmt = $pdo->query($sql);
$requests = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Deactivation / Deletion Requests</h3>
        <p class="text-gray-500 text-sm">Process requests from users who want to deactivate or delete their accounts.</p>
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
                    <th class="py-4 px-6 font-semibold">Request Type</th>
                    <th class="py-4 px-6 font-semibold">Reason</th>
                    <th class="py-4 px-6 font-semibold">Date</th>
                    <th class="py-4 px-6 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php foreach ($requests as $req): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-4 px-6">
                        <input type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center">
                            <?php
                            $photo_path_real = !empty($req['profile_photo']) ? '../' . $req['profile_photo'] : '';
                            $photo = ($photo_path_real && file_exists($photo_path_real)) ? '../image.php?file=' . urlencode($req['profile_photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($req['full_name']);
                            ?>
                            <img src="<?= $photo ?>" class="w-10 h-10 rounded-full object-cover mr-3 border border-gray-200" alt="Profile Photo">
                            <div>
                                <p class="font-bold text-gray-800"><?= htmlspecialchars($req['full_name']) ?></p>
                                <p class="text-xs text-gray-500">MID: <?= htmlspecialchars($req['profile_id'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $req['request_type'] === 'deletion' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= ucfirst($req['request_type']) ?> Request
                        </span>
                    </td>
                    <td class="py-4 px-6 text-gray-600">
                        "<?= htmlspecialchars($req['reason'] ?? 'No reason provided') ?>"
                    </td>
                    <td class="py-4 px-6 text-gray-600">
                        <?= date('M d, Y', strtotime($req['created_at'])) ?>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <form method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Are you sure you want to process this request? This action cannot be undone.');">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $req['user_id'] ?>">
                            <button type="submit" name="action" value="process_deletion" class="bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white px-3 py-1 rounded-lg transition text-xs font-bold">Process Request</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="6" class="py-4 px-6 text-center text-gray-500">No pending requests found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
