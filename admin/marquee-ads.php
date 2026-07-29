<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';
$current_page = 'marquee-ads.php';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS marquee_ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        advertisement_text TEXT NOT NULL,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ad'])) {
    $id = $_POST['delete_id'];
    $pdo->prepare("DELETE FROM marquee_ads WHERE id = ?")->execute([$id]);
    header("Location: marquee-ads.php?msg=deleted");
    exit;
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ad'])) {
    $text = trim($_POST['advertisement_text'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (!empty($text)) {
        $stmt = $pdo->prepare("INSERT INTO marquee_ads (advertisement_text, status) VALUES (?, ?)");
        $stmt->execute([$text, $status]);
        header("Location: marquee-ads.php?msg=added");
        exit;
    } else {
        $error = "Advertisement text cannot be empty.";
    }
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ad'])) {
    $id = $_POST['edit_id'];
    $text = trim($_POST['advertisement_text'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (!empty($text)) {
        $stmt = $pdo->prepare("UPDATE marquee_ads SET advertisement_text = ?, status = ? WHERE id = ?");
        $stmt->execute([$text, $status, $id]);
        header("Location: marquee-ads.php?msg=updated");
        exit;
    } else {
        $error = "Advertisement text cannot be empty.";
    }
}

// Fetch ads
$ads = [];
try {
    $stmt = $pdo->query("SELECT * FROM marquee_ads ORDER BY created_at DESC");
    if ($stmt) {
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Advertisement Marquee</h3>
        <p class="text-gray-500 text-sm">Manage dynamic scrolling advertisements on the homepage.</p>
    </div>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?php 
            if ($_GET['msg'] === 'deleted') echo 'Advertisement deleted successfully.';
            elseif ($_GET['msg'] === 'updated') echo 'Advertisement updated successfully.';
            else echo 'Advertisement added successfully.';
        ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Add Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-plus mr-2 text-primary"></i> Add New Advertisement</h4>
    <form action="" method="POST" class="flex flex-col gap-4">
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Advertisement Text <span class="text-red-500">*</span></label>
            <input type="text" name="advertisement_text" required placeholder="e.g. Registration for the 2026 Matrimony Event is now open." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="add_status" name="status" checked class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
            <label for="add_status" class="text-sm font-medium text-gray-700">Active (Show on homepage)</label>
        </div>
        <div>
            <button type="submit" name="add_ad" class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-6 rounded-lg transition-colors">
                Save Advertisement
            </button>
        </div>
    </form>
</div>

<!-- Advertisements List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h4 class="font-bold text-gray-800">Existing Advertisements</h4>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="py-3 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Advertisement Text</th>
                    <th class="py-3 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="py-3 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (count($ads) > 0): ?>
                    <?php foreach ($ads as $ad): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-6 text-sm text-gray-500">#<?= $ad['id'] ?></td>
                        <td class="py-4 px-6 text-sm text-gray-800 font-medium">
                            <?= htmlspecialchars($ad['advertisement_text']) ?>
                        </td>
                        <td class="py-4 px-6">
                            <?php if ($ad['status'] == 1): ?>
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">Active</span>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-700 text-xs font-bold px-2 py-1 rounded">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($ad['created_at'])) ?>
                        </td>
                        <td class="py-4 px-6 text-right space-x-2">
                            <button type="button" onclick="editAd(<?= $ad['id'] ?>, '<?= htmlspecialchars(addslashes($ad['advertisement_text'])) ?>', <?= $ad['status'] ?>)" class="text-blue-500 hover:text-blue-700 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this advertisement?');">
                                <input type="hidden" name="delete_id" value="<?= $ad['id'] ?>">
                                <button type="submit" name="delete_ad" class="text-red-500 hover:text-red-700 transition" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-8 px-6 text-center text-gray-500">No advertisements found. Add one above.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Edit Advertisement</h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <form action="" method="POST" class="flex flex-col gap-4">
                <input type="hidden" name="edit_id" id="edit_id">
                
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Advertisement Text <span class="text-red-500">*</span></label>
                    <input type="text" name="advertisement_text" id="edit_text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="edit_status" name="status" class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                    <label for="edit_status" class="text-sm font-medium text-gray-700">Active (Show on homepage)</label>
                </div>
                
                <div class="mt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" name="edit_ad" class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-6 rounded-lg transition-colors">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAd(id, text, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_text').value = text;
    document.getElementById('edit_status').checked = status == 1;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<?php include 'includes/footer.php'; ?>
