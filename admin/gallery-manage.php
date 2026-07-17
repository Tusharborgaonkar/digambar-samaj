<?php
$current_page = 'gallery-manage.php';
include 'includes/header.php';
include 'includes/sidebar.php';
require_once '../includes/db.php';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NULL,
        category VARCHAR(100) DEFAULT 'All Photos',
        image_path VARCHAR(255) NOT NULL,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists('../' . $img)) {
        unlink('../' . $img);
    }
    $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
    header("Location: gallery-manage.php?msg=deleted");
    exit;
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? 'All Photos';
    
    $uploadDir = __DIR__ . '/../uploads/gallery/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '.' . $fileExt;
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
        $dbPath = 'uploads/gallery/' . $fileName;
        $stmt = $pdo->prepare("INSERT INTO gallery (title, category, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$title, $category, $dbPath]);
        header("Location: gallery-manage.php?msg=uploaded");
        exit;
    } else {
        $error = "Failed to upload file. Error Code: " . $_FILES['photo']['error'];
    }
}

// Fetch photos
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Manage Gallery</h3>
        <p class="text-gray-500 text-sm">Upload and manage photos for the gallery page.</p>
    </div>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?= $_GET['msg'] === 'deleted' ? 'Photo deleted.' : 'Photo uploaded successfully!' ?>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
<?php endif; ?>

<!-- Upload Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-upload mr-2 text-primary"></i> Upload New Photo</h4>
    <form action="" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Photo</label>
            <input type="file" name="photo" required accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Title (Optional)</label>
            <input type="text" name="title" placeholder="Event Name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
        </div>
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                <option>Events</option>
                <option>Parichay Sammelan</option>
                <option>Religious Programs</option>
                <option>Temple Functions</option>
                <option>Other</option>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-primary text-white py-2 px-6 rounded-lg font-bold shadow-sm hover:bg-opacity-90 transition w-full sm:w-auto h-[42px]">
                Upload
            </button>
        </div>
    </form>
</div>

<!-- Photos Grid -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-images mr-2 text-primary"></i> Existing Photos</h4>
    
    <?php if(empty($photos)): ?>
        <p class="text-gray-500">No photos uploaded yet.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <?php foreach($photos as $p): ?>
                <div class="relative group rounded-lg overflow-hidden border border-gray-200">
                    <img src="../<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="w-full h-32 object-cover">
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center">
                        <span class="text-white text-xs text-center px-2 mb-2 font-bold"><?= htmlspecialchars($p['title'] ?: 'No Title') ?></span>
                        <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Are you sure you want to delete this photo?')" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
