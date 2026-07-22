<?php
$current_page = 'news.php'; // Keep news.php active in sidebar
require_once '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: news.php");
    exit;
}

$id = $_GET['id'];

// Fetch the existing news article
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    header("Location: news.php");
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_news'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    
    $update_image_sql = "";
    $params = [$title, $content, $status];

    // Handle new image upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/news/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $filename = uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $db_path = 'uploads/news/' . $filename;
                
                // Delete old image if it exists
                if ($news['image'] && file_exists('../' . $news['image'])) {
                    unlink('../' . $news['image']);
                }
                
                $update_image_sql = ", image = ?";
                $params[] = $db_path;
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
        }
    }

    if (!isset($error)) {
        $params[] = $id;
        $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ?, status = ? {$update_image_sql} WHERE id = ?");
        $stmt->execute($params);
        
        header("Location: news.php?msg=updated");
        exit;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Edit News & Updates</h3>
        <p class="text-gray-500 text-sm">Update the details of the news article.</p>
    </div>
    <a href="news.php" class="text-gray-600 hover:text-primary transition font-semibold"><i class="fas fa-arrow-left mr-2"></i>Back to News</a>
</div>

<?php if(isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
<?php endif; ?>

<!-- Edit News Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 max-w-4xl">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-edit mr-2 text-primary"></i> Edit Article</h4>
    <form id="newsEditForm" action="" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="<?= htmlspecialchars($news['title']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
            </div>
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Update Image (Optional)</label>
                <input type="file" name="photo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white">
                <p class="text-xs text-gray-500 mt-1">Leave empty to keep the current image.</p>
                <?php if($news['image'] && file_exists('../' . ltrim(str_replace('../', '', $news['image']), '/'))): ?>
                    <div class="mt-2">
                        <p class="text-xs font-semibold mb-1">Current Image:</p>
                        <img src="../image.php?file=<?= urlencode(ltrim(str_replace('../', '', $news['image']), '/')) ?>" alt="Current Image" class="h-20 rounded border">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
            <textarea name="content" required rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"><?= htmlspecialchars($news['content']) ?></textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="status" id="status" <?= $news['status'] == 1 ? 'checked' : '' ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="status" class="text-sm font-medium text-gray-700">Publish immediately (Active)</label>
        </div>
        <div class="mt-4">
            <input type="hidden" name="edit_news" value="1">
            <button type="submit" class="bg-primary text-white py-2 px-8 rounded-lg font-bold shadow-sm hover:bg-opacity-90 transition inline-flex items-center justify-center min-w-[120px]">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('newsEditForm').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        if(btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
