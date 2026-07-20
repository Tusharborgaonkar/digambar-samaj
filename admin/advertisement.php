<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';
$current_page = 'advertisement.php';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS advertisements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NULL,
        link VARCHAR(255) NULL,
        image VARCHAR(255) NOT NULL,
        position VARCHAR(50) DEFAULT 'home_top',
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM advertisements WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && strpos($img, 'uploads/ads/') === 0 && file_exists('../' . $img)) {
        unlink('../' . $img);
    }
    $pdo->prepare("DELETE FROM advertisements WHERE id = ?")->execute([$id]);
    header("Location: advertisement.php?msg=deleted");
    exit;
}

// Handle Update Status/Position
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ad'])) {
    $id = $_POST['ad_id'];
    $status = isset($_POST['status']) ? 1 : 0;
    $position = $_POST['position'];
    $stmt = $pdo->prepare("UPDATE advertisements SET status = ?, position = ? WHERE id = ?");
    $stmt->execute([$status, $position, $id]);
    header("Location: advertisement.php?msg=updated");
    exit;
}

// Handle Edit Ad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ad'])) {
    $id = $_POST['edit_id'];
    $title = $_POST['title'] ?? '';
    $link = $_POST['link'] ?? '';
    $position = $_POST['position'] ?? 'home_top';
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Check if new image is uploaded
    if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/ads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));
        $fileName = 'ad_' . time() . '_' . uniqid() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['ad_image']['tmp_name'], $targetPath)) {
            chmod($targetPath, 0644);
            $dbPath = 'uploads/ads/' . $fileName;
            
            // Delete old image
            $stmt = $pdo->prepare("SELECT image FROM advertisements WHERE id = ?");
            $stmt->execute([$id]);
            $oldImg = $stmt->fetchColumn();
            if ($oldImg && strpos($oldImg, 'uploads/ads/') === 0 && file_exists('../' . $oldImg)) {
                unlink('../' . $oldImg);
            }
            
            $stmt = $pdo->prepare("UPDATE advertisements SET title = ?, link = ?, position = ?, status = ?, image = ? WHERE id = ?");
            $stmt->execute([$title, $link, $position, $status, $dbPath, $id]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE advertisements SET title = ?, link = ?, position = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $link, $position, $status, $id]);
    }
    
    header("Location: advertisement.php?msg=updated");
    exit;
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['ad_image'])) {
    $title = $_POST['title'] ?? '';
    $link = $_POST['link'] ?? '';
    $position = $_POST['position'] ?? 'home_top';
    
    if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/ads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));
        $fileName = 'ad_' . time() . '_' . uniqid() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['ad_image']['tmp_name'], $targetPath)) {
            $dbPath = 'uploads/ads/' . $fileName;
            $stmt = $pdo->prepare("INSERT INTO advertisements (title, link, image, position) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $link, $dbPath, $position]);
            header("Location: advertisement.php?msg=uploaded");
            exit;
        } else {
            $error = "Failed to upload file. Error Code: " . $_FILES['ad_image']['error'];
        }
    }
}

// Fetch Ads
$stmt = $pdo->query("SELECT * FROM advertisements ORDER BY created_at DESC");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Hero Advertisements</h3>
        <p class="text-gray-500 text-sm">Manage banners and promotional ads displayed on the homepage.</p>
    </div>
    <button class="bg-primary text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-opacity-90 transition shadow-sm flex items-center" onclick="document.getElementById('uploadAdModal').classList.remove('hidden')">
        <i class="fas fa-upload mr-2"></i> Upload New Ad
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?php
        if($_GET['msg'] === 'deleted') echo 'Advertisement deleted.';
        if($_GET['msg'] === 'updated') echo 'Advertisement updated successfully.';
        if($_GET['msg'] === 'uploaded') echo 'Advertisement uploaded successfully!';
        ?>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
<?php endif; ?>

<!-- Active Ads Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <?php foreach($ads as $ad): ?>
    <!-- Ad Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="h-48 bg-gray-200 relative overflow-hidden group">
            <img src="../image.php?file=<?= urlencode($ad['image']) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <a href="../image.php?file=<?= urlencode($ad['image']) ?>" target="_blank" class="bg-white text-gray-800 p-2 rounded-full mx-1 hover:bg-gray-100 transition shadow" title="Preview"><i class="fas fa-eye w-5 h-5 flex items-center justify-center"></i></a>
                <button type="button" onclick="openEditModal(<?= $ad['id'] ?>, '<?= htmlspecialchars(addslashes($ad['title'])) ?>', '<?= htmlspecialchars(addslashes($ad['link'])) ?>', '<?= $ad['position'] ?>', <?= $ad['status'] ?>)" class="bg-white text-blue-600 p-2 rounded-full mx-1 hover:bg-blue-50 transition shadow" title="Edit"><i class="fas fa-edit w-5 h-5 flex items-center justify-center"></i></button>
                <a href="?delete=<?= $ad['id'] ?>" onclick="return confirm('Delete this ad?');" class="bg-white text-red-600 p-2 rounded-full mx-1 hover:bg-red-50 transition shadow" title="Delete"><i class="fas fa-trash w-5 h-5 flex items-center justify-center"></i></a>
            </div>
            <div class="absolute top-3 right-3">
                <?php if($ad['status'] == 1): ?>
                    <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded shadow-sm">Active</span>
                <?php else: ?>
                    <span class="bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded shadow-sm">Inactive</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="p-5">
            <h4 class="font-bold text-gray-800 mb-1"><?= htmlspecialchars($ad['title']) ?></h4>
            <p class="text-xs text-gray-500 mb-2">Uploaded on <?= date('M d, Y', strtotime($ad['created_at'])) ?></p>
            <?php if(!empty($ad['link'])): ?>
                <p class="text-xs text-blue-500 mb-4 truncate"><a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank"><?= htmlspecialchars($ad['link']) ?></a></p>
            <?php else: ?>
                <p class="text-xs text-gray-400 mb-4">No link</p>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                <div class="space-y-3 bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Position</span>
                        <select name="position" class="text-sm border-gray-300 rounded focus:ring-primary focus:border-primary p-1 outline-none bg-white">
                            <option value="home_top" <?= $ad['position'] == 'home_top' ? 'selected' : '' ?>>Home Top</option>
                            <option value="home_bottom" <?= $ad['position'] == 'home_bottom' ? 'selected' : '' ?>>Home Bottom</option>
                        </select>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Status</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" value="1" class="sr-only peer" <?= $ad['status'] == 1 ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
                <button type="submit" name="update_ad" class="w-full mt-4 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-bold hover:bg-gray-200 transition">
                    Update Settings
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($ads)): ?>
        <p class="text-gray-500">No advertisements found.</p>
    <?php endif; ?>
</div>

<!-- Upload Ad Modal -->
<div id="uploadAdModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800">Upload Advertisement</h3>
            <button class="text-gray-400 hover:text-gray-600 transition" onclick="document.getElementById('uploadAdModal').classList.add('hidden')">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto">
            <form class="space-y-5" action="" method="POST" enctype="multipart/form-data">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Title / Name</label>
                    <input type="text" name="title" placeholder="e.g. Summer Promo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Link URL (Optional)</label>
                    <input type="url" name="link" placeholder="https://..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Banner Image</label>
                    <input type="file" name="ad_image" required accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <select name="position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white text-sm">
                            <option value="home_top">Home Top</option>
                            <option value="home_bottom">Home Bottom</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-bold hover:bg-opacity-90 transition mt-4">
                    Upload Advertisement
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Ad Modal -->
<div id="editAdModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800">Edit Advertisement</h3>
            <button class="text-gray-400 hover:text-gray-600 transition" onclick="document.getElementById('editAdModal').classList.add('hidden')">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto">
            <form class="space-y-5" action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit_ad_id">
                <input type="hidden" name="edit_ad" value="1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Title / Name</label>
                    <input type="text" name="title" id="edit_title" placeholder="e.g. Summer Promo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Link URL (Optional)</label>
                    <input type="url" name="link" id="edit_link" placeholder="https://..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Banner Image (Leave empty to keep current)</label>
                    <input type="file" name="ad_image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <select name="position" id="edit_position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white text-sm">
                            <option value="home_top">Home Top</option>
                            <option value="home_bottom">Home Bottom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <label class="relative inline-flex items-center cursor-pointer mt-2">
                            <input type="checkbox" name="status" id="edit_status" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-bold hover:bg-opacity-90 transition mt-4">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, title, link, position, status) {
    document.getElementById('edit_ad_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_link').value = link;
    document.getElementById('edit_position').value = position;
    document.getElementById('edit_status').checked = (status == 1);
    document.getElementById('editAdModal').classList.remove('hidden');
}
</script>

<?php include 'includes/footer.php'; ?>
