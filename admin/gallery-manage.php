<?php
$current_page = 'gallery-manage.php';
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

// Add category column if missing (ignore error if already exists)
try {
    $pdo->exec("ALTER TABLE gallery ADD COLUMN category VARCHAR(100) DEFAULT 'All Photos'");
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE gallery ADD COLUMN media_type ENUM('image', 'pdf', 'video', 'youtube') DEFAULT 'image'");
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE gallery ADD COLUMN media_url VARCHAR(500) NULL");
} catch (Exception $e) {}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo']) && is_numeric($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_media'])) {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : 'All Photos';
    $media_type = isset($_POST['media_type']) ? $_POST['media_type'] : 'image';
    
    if ($media_type === 'youtube') {
        $media_url = trim($_POST['media_url'] ?? '');
        if (empty($media_url)) {
            $error = "YouTube URL is required.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, category, image_path, media_type, media_url) VALUES (?, ?, '', 'youtube', ?)");
            $stmt->execute([$title, $category, $media_url]);
            header("Location: gallery-manage.php?msg=uploaded");
            exit;
        }
    } else {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/gallery/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'mp4'];
            
            if (in_array($file_ext, $allowed_exts)) {
                // Ensure media_type matches file extension
                if ($file_ext === 'pdf') $media_type = 'pdf';
                elseif ($file_ext === 'mp4') $media_type = 'video';
                else $media_type = 'image';

                $filename = uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                    $db_path = 'uploads/gallery/' . $filename;
                    
                    $stmt = $pdo->prepare("INSERT INTO gallery (title, category, image_path, media_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $category, $db_path, $media_type]);
                    
                    header("Location: gallery-manage.php?msg=uploaded");
                    exit;
                } else {
                    $error = "Failed to move uploaded file.";
                }
            } else {
                $error = "Invalid file type. Allowed: JPG, PNG, GIF, WebP, PDF, MP4.";
            }
        } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = "Upload error code: " . $_FILES['photo']['error'];
        } else {
            $error = "Please select a file to upload.";
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';

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
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-upload mr-2 text-primary"></i> Upload Media</h4>
    <form id="galleryUploadForm" action="" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
        <input type="hidden" name="upload_media" value="1">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Media Type</label>
                <select name="media_type" id="mediaTypeSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                    <option value="image">Image (JPG, PNG)</option>
                    <option value="pdf">Document (PDF)</option>
                    <option value="video">Video (MP4)</option>
                    <option value="youtube">YouTube Link</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                    <option>Events</option>
                    <option>Parichay Sammelan</option>
                    <option>Religious Programs</option>
                    <option>Temple Functions</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title (Optional)</label>
                <input type="text" name="title" placeholder="Event Name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full" id="fileUploadWrapper">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select File</label>
                <input type="file" name="photo" id="photoInput" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex-1 w-full hidden" id="urlInputWrapper">
                <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
                <input type="url" name="media_url" id="urlInput" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none text-sm">
            </div>
            <div>
                <button type="submit" class="bg-primary text-white py-2 px-6 rounded-lg font-bold shadow-sm hover:bg-opacity-90 transition w-full sm:w-auto h-[42px]">
                    Upload
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('mediaTypeSelect').addEventListener('change', function() {
        const fileWrapper = document.getElementById('fileUploadWrapper');
        const urlWrapper = document.getElementById('urlInputWrapper');
        const fileInput = document.getElementById('photoInput');
        
        if (this.value === 'youtube') {
            fileWrapper.classList.add('hidden');
            urlWrapper.classList.remove('hidden');
            fileInput.required = false;
        } else {
            fileWrapper.classList.remove('hidden');
            urlWrapper.classList.add('hidden');
            
            if (this.value === 'pdf') {
                fileInput.accept = 'application/pdf';
            } else if (this.value === 'video') {
                fileInput.accept = 'video/mp4';
            } else {
                fileInput.accept = 'image/*';
            }
        }
    });

    document.getElementById('galleryUploadForm').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        if(btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';
        }
    });
</script>

<!-- Photos Grid -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-images mr-2 text-primary"></i> Manage Gallery</h4>
    
    <?php if(empty($photos)): ?>
        <p class="text-gray-500">No media uploaded yet.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <?php foreach($photos as $p): 
                $clean_path = ltrim(str_replace('../', '', $p['image_path'] ?? ''), '/');
                $type = $p['media_type'] ?? 'image';
            ?>
                <div class="relative group rounded-lg overflow-hidden border border-gray-200 bg-gray-50 h-32 flex items-center justify-center">
                    <?php if($type === 'image' && !empty($clean_path)): ?>
                        <img src="../image.php?file=<?= urlencode($clean_path) ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="w-full h-full object-cover">
                    <?php elseif($type === 'pdf'): ?>
                        <div class="text-red-500 text-4xl"><i class="fas fa-file-pdf"></i></div>
                    <?php elseif($type === 'video' || $type === 'youtube'): ?>
                        <div class="text-blue-500 text-4xl"><i class="fas fa-play-circle"></i></div>
                    <?php endif; ?>
                    
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center">
                        <span class="text-white text-xs text-center px-2 mb-2 font-bold"><?= htmlspecialchars($p['title'] ?: ucfirst($type)) ?></span>
                        <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?')">
                            <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="delete_photo" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
