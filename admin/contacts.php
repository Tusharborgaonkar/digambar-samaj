<?php
require_once '../includes/db.php';
$current_page = 'contacts.php';

// Handle deletion of messages if needed (optional, but good for admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['message_id'])) {
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([(int)$_POST['message_id']]);
        header("Location: contacts.php?deleted=1");
        exit;
    }
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Get total messages
$countStmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
$total_records = $countStmt->fetchColumn();
$total_pages = max(1, ceil($total_records / $limit));

// Fetch messages
$stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();

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
        <h3 class="text-2xl font-bold text-gray-800">Contact Messages</h3>
        <p class="text-gray-500 text-sm">Messages received from the Contact Us page.</p>
    </div>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
    <span class="block sm:inline">Message deleted successfully.</span>
</div>
<?php endif; ?>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="py-4 px-6 font-semibold">Name</th>
                    <th class="py-4 px-6 font-semibold">Contact Info</th>
                    <th class="py-4 px-6 font-semibold">Subject</th>
                    <th class="py-4 px-6 font-semibold">Message</th>
                    <th class="py-4 px-6 font-semibold">Date</th>
                    <th class="py-4 px-6 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php foreach ($messages as $msg): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-4 px-6 font-bold text-gray-800">
                        <?= htmlspecialchars($msg['name']) ?>
                    </td>
                    <td class="py-4 px-6">
                        <p class="text-gray-800"><i class="fas fa-envelope text-gray-400 w-4"></i> <?= htmlspecialchars($msg['email']) ?></p>
                        <?php if(!empty($msg['phone'])): ?>
                            <p class="text-gray-500 text-xs mt-1"><i class="fas fa-phone text-gray-400 w-4"></i> <?= htmlspecialchars($msg['phone']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="py-4 px-6 text-gray-800 font-medium">
                        <?= htmlspecialchars($msg['subject']) ?>
                    </td>
                    <td class="py-4 px-6 text-gray-600 max-w-xs" style="word-break: break-word;">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </td>
                    <td class="py-4 px-6 text-gray-600">
                        <?= date('M d, Y', strtotime($msg['created_at'])) ?><br>
                        <span class="text-xs text-gray-400"><?= date('h:i A', strtotime($msg['created_at'])) ?></span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <form method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this message?');">
                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                            <button type="submit" name="action" value="delete" class="text-red-600 hover:text-red-900 mx-1 p-1 tooltip" title="Delete Message"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="6" class="py-4 px-6 text-center text-gray-500">No messages found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
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
            
            for ($i = $start_page; $i <= $end_page; $i++): 
                if ($i == $page): ?>
                    <button class="px-3 py-1 border border-primary bg-primary text-white rounded text-sm font-medium"><?= $i ?></button>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50"><?= $i ?></a>
                <?php endif; 
            endfor; 
            ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">Next</a>
            <?php else: ?>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
