<?php
$current_page = 'news.php';
require_once '../includes/db.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_news']) && is_numeric($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists('../' . $img)) {
        unlink('../' . $img);
    }
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
    header("Location: news.php?msg=deleted");
    exit;
}

// Handle Add/Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $db_path = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['photo']['tmp_name']);
        $mime_type = mime_content_type($_FILES['photo']['tmp_name']);
        
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if ($image_data !== false && in_array($mime_type, $allowed_mimes)) {
            $base64 = base64_encode($image_data);
            $db_path = 'data:' . $mime_type . ';base64,' . $base64;
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
        }
    }

    if (!isset($error)) {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, image, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $db_path, $status]);
        
        header("Location: news.php?msg=added");
        exit;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch news
$news_items = [];
try {
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
    if ($stmt) {
        $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage() . ". Please ensure the 'news' table exists by running the migration script.";
}
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Manage News & Updates</h3>
        <p class="text-gray-500 text-sm">Add, remove, and manage news articles for the community.</p>
    </div>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?php 
            if ($_GET['msg'] === 'deleted') echo 'News article deleted.';
            elseif ($_GET['msg'] === 'updated') echo 'News article updated successfully!';
            else echo 'News article added successfully!';
        ?>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
<?php endif; ?>

<!-- Add News Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-plus mr-2 text-primary"></i> Add New Article</h4>
    <form id="newsAddForm" action="" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="Enter news title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
            </div>
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optional)</label>
                <input type="file" name="photo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white">
            </div>
        </div>
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
            <textarea name="content" required rows="4" placeholder="Enter full news content here..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"></textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="status" id="status" checked class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="status" class="text-sm font-medium text-gray-700">Publish immediately (Active)</label>
        </div>
        <div>
            <input type="hidden" name="add_news" value="1">
            <button type="submit" class="bg-primary text-white py-2 px-6 rounded-lg font-bold shadow-sm hover:bg-opacity-90 transition inline-flex items-center justify-center min-w-[120px]">
                Add News
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('newsAddForm').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        if(btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }
    });
</script>

<!-- News List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-newspaper mr-2 text-primary"></i> Existing News</h4>
    
    <?php if(empty($news_items)): ?>
        <p class="text-gray-500">No news articles added yet.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-3 text-sm font-semibold text-gray-600">Image</th>
                        <th class="p-3 text-sm font-semibold text-gray-600">Title</th>
                        <th class="p-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="p-3 text-sm font-semibold text-gray-600">Date</th>
                        <th class="p-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($news_items as $news): 
                        $clean_path = $news['image'] ? ltrim(str_replace('../', '', $news['image']), '/') : '';
                    ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3">
                                <?php if($news['image'] && strpos($news['image'], 'data:image/') === 0): ?>
                                    <img src="<?= $news['image'] ?>" alt="img" class="w-16 h-12 object-cover rounded">
                                <?php elseif($clean_path && file_exists('../' . $clean_path)): ?>
                                    <img src="../image.php?file=<?= urlencode($clean_path) ?>" alt="img" class="w-16 h-12 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">No img</div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($news['title']) ?></td>
                            <td class="p-3">
                                <?php if($news['status'] == 1): ?>
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Active</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-sm text-gray-500"><?= date('M d, Y', strtotime($news['created_at'])) ?></td>
                            <td class="p-3">
                                <a href="edit-news.php?id=<?= $news['id'] ?>" class="text-blue-500 hover:text-blue-700 p-1 mr-2 inline-block">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this news article?')">
                                    <input type="hidden" name="delete_id" value="<?= $news['id'] ?>">
                                    <button type="submit" name="delete_news" class="text-red-500 hover:text-red-700 p-1">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
