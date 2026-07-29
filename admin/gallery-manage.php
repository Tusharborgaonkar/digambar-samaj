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

try {
    $pdo->exec("ALTER TABLE gallery MODIFY COLUMN image_path LONGTEXT NULL");
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

                // Save file to disk instead of base64
                $filename = uniqid('media_') . '.' . $file_ext;
                $destination = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                    $db_path = 'uploads/gallery/' . $filename;
                    
                    $stmt = $pdo->prepare("INSERT INTO gallery (title, category, image_path, media_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $category, $db_path, $media_type]);
                    
                    header("Location: gallery-manage.php?msg=uploaded");
                    exit;
                } else {
                    $error = "Failed to process uploaded file.";
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

// Handle Banner Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_banner'])) {
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/settings/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $filename = uniqid('gallery_banner_') . '.' . $file_ext;
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $destination)) {
                $db_path = 'uploads/settings/' . $filename;
                
                // Update or Insert into site_settings
                $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'gallery_background_image'");
                $stmt->execute();
                if ($stmt->fetch()) {
                    $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'gallery_background_image'")->execute([$db_path]);
                } else {
                    $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('gallery_background_image', ?)")->execute([$db_path]);
                }
                
                header("Location: gallery-manage.php?msg=banner_updated");
                exit;
            } else {
                $error = "Failed to upload banner image.";
            }
        } else {
            $error = "Invalid banner file type. Allowed: JPG, PNG, WEBP.";
        }
    } else {
        $error = "Please select a valid image file.";
    }
}

// Handle Banner Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_banner'])) {
    $pdo->prepare("UPDATE site_settings SET setting_value = '' WHERE setting_key = 'gallery_background_image'")->execute();
    header("Location: gallery-manage.php?msg=banner_deleted");
    exit;
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= $_GET['msg'] === 'deleted' ? 'Media deleted successfully.' : 'Media uploaded successfully!' ?>',
                timer: 3000,
                showConfirmButton: false
            });
            // Clean up the URL
            const url = new URL(window.location);
            url.searchParams.delete('msg');
            window.history.replaceState({}, document.title, url);
        });
    </script>
<?php endif; ?>
<?php if(isset($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= addslashes($error) ?>',
                confirmButtonColor: '#1E3A5F'
            });
        });
    </script>
<?php endif; ?>

<!-- Gallery Banner Image Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-image mr-2 text-primary"></i> Gallery Banner Image</h4>
    
    <?php 
    $current_banner = $settings['gallery_background_image'] ?? ''; 
    if (!empty($current_banner)) {
        if (strpos($current_banner, 'data:image/') === 0 || preg_match('/^https?:\/\//i', $current_banner)) {
            $banner_src = $current_banner;
        } else {
            $banner_src = '../image.php?file=' . urlencode(ltrim(str_replace('../', '', $current_banner), '/'));
        }
    ?>
    <div class="mb-4">
        <p class="text-sm font-medium text-gray-700 mb-2">Current Banner Preview:</p>
        <div class="relative w-full h-32 md:h-48 rounded-lg overflow-hidden border border-gray-200">
            <img src="<?= htmlspecialchars($banner_src) ?>" alt="Banner Preview" class="w-full h-full object-cover">
        </div>
        <form action="" method="POST" class="mt-2 text-right">
            <button type="submit" name="delete_banner" class="text-red-500 hover:text-red-700 text-sm font-bold" onclick="return confirm('Remove current banner?')"><i class="fas fa-trash mr-1"></i> Remove Banner</button>
        </form>
    </div>
    <?php } else { ?>
        <div class="mb-4 text-gray-500 text-sm p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
            No banner image uploaded. The gallery page will use the default dark background.
        </div>
    <?php } ?>

    <form action="" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-end">
        <input type="hidden" name="update_banner" value="1">
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1"><?= empty($current_banner) ? 'Upload New Banner' : 'Replace Banner Image' ?></label>
            <input type="file" name="banner_image" accept=".jpg,.jpeg,.png,.webp" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <button type="submit" class="bg-primary text-white py-2 px-6 rounded-lg font-bold shadow-sm hover:bg-opacity-90 transition w-full sm:w-auto h-[42px]">
                <?= empty($current_banner) ? 'Upload' : 'Replace' ?>
            </button>
        </div>
    </form>
</div>

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
                    <?php if($type === 'image' && !empty($p['image_path'])): ?>
                        <?php 
                            if (strpos($p['image_path'], 'data:image/') === 0 || preg_match('/^https?:\/\//i', $p['image_path'])) {
                                $img_src = $p['image_path'];
                            } else {
                                $img_src = '../image.php?file=' . urlencode($clean_path);
                            }
                        ?>
                        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="w-full h-full object-cover">
                    <?php elseif($type === 'pdf'): ?>
                        <div class="text-red-500 text-4xl"><i class="fas fa-file-pdf"></i></div>
                    <?php elseif($type === 'video' || $type === 'youtube'): ?>
                        <div class="text-blue-500 text-4xl"><i class="fas fa-play-circle"></i></div>
                    <?php endif; ?>
                    
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center">
                        <span class="text-white text-xs text-center px-2 mb-2 font-bold"><?= htmlspecialchars($p['title'] ?: ucfirst($type)) ?></span>
                        <form method="POST" action="" class="inline delete-form">
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

<script>
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this item? This cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a hidden input to simulate the button click, since form.submit() doesn't include button name
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'delete_photo';
                    hiddenInput.value = '1';
                    form.appendChild(hiddenInput);
                    form.submit();
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
