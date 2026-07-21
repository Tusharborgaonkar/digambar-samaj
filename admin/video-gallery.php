<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS video_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        video_type ENUM('youtube', 'mp4') DEFAULT 'youtube',
        video_url VARCHAR(255) NULL,
        video_file VARCHAR(255) NULL,
        thumbnail VARCHAR(255) NULL,
        description TEXT NULL,
        display_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        try {
            if ($action === 'add' || $action === 'edit') {
                $title = $_POST['title'] ?? '';
                $video_type = $_POST['video_type'] ?? 'youtube';
                $video_url = $_POST['video_url'] ?? '';
                $description = $_POST['description'] ?? '';
                $display_order = (int)($_POST['display_order'] ?? 0);
                $status = $_POST['status'] ?? 'active';
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

                $video_file = '';
                $thumbnail = '';

                if (empty($title)) {
                    $error_msg = "Please enter a title.";
                } else {
                    $upload_dir = '../uploads/videos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Handle MP4 upload
                    if ($video_type === 'mp4' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                        $file_ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                        if ($file_ext === 'mp4') {
                            $new_filename = uniqid('vid_') . '.mp4';
                            $upload_path = $upload_dir . $new_filename;
                            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $upload_path)) {
                                chmod($upload_path, 0644);
                                $video_file = 'uploads/videos/' . $new_filename;
                            }
                        } else {
                            $error_msg = "Only MP4 videos are allowed.";
                        }
                    }

                    // Handle thumbnail upload
                    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                        $file_ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $new_filename = uniqid('thumb_') . '.' . $file_ext;
                            $upload_path = $upload_dir . $new_filename;
                            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                                chmod($upload_path, 0644);
                                $thumbnail = 'uploads/videos/' . $new_filename;
                            }
                        }
                    }

                    if (empty($error_msg)) {
                        if ($action === 'add') {
                            $stmt = $pdo->prepare("INSERT INTO video_gallery (title, video_type, video_url, video_file, thumbnail, description, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$title, $video_type, $video_url, $video_file, $thumbnail, $description, $display_order, $status]);
                            $success_msg = "Video added successfully.";
                        } else {
                            $update_parts = ["title = ?", "video_type = ?", "video_url = ?", "description = ?", "display_order = ?", "status = ?"];
                            $params = [$title, $video_type, $video_url, $description, $display_order, $status];

                            if (!empty($video_file)) {
                                $update_parts[] = "video_file = ?";
                                $params[] = $video_file;
                            }
                            if (!empty($thumbnail)) {
                                $update_parts[] = "thumbnail = ?";
                                $params[] = $thumbnail;
                            }
                            $params[] = $id;

                            $stmt = $pdo->prepare("UPDATE video_gallery SET " . implode(", ", $update_parts) . " WHERE id = ?");
                            $stmt->execute($params);
                            $success_msg = "Video updated successfully.";
                        }
                    }
                }
            } elseif ($action === 'delete' && isset($_POST['id'])) {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT video_file, thumbnail FROM video_gallery WHERE id = ?");
                $stmt->execute([$id]);
                $video = $stmt->fetch();
                
                if ($video) {
                    if (!empty($video['video_file']) && file_exists('../' . $video['video_file'])) unlink('../' . $video['video_file']);
                    if (!empty($video['thumbnail']) && file_exists('../' . $video['thumbnail'])) unlink('../' . $video['thumbnail']);
                    $stmt = $pdo->prepare("DELETE FROM video_gallery WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_msg = "Video deleted successfully.";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM video_gallery ORDER BY display_order ASC, created_at DESC");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $videos = [];
}

$current_page = 'video-gallery.php';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Video Gallery</h1>
    <button onclick="document.getElementById('videoModal').classList.remove('hidden'); document.getElementById('videoForm').reset(); document.getElementById('formAction').value='add';" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition font-semibold shadow-sm">
        <i class="fas fa-plus mr-2"></i> Add Video
    </button>
</div>

<?php if ($success_msg): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thumbnail</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($videos as $vid): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($vid['thumbnail']): ?>
                        <img src="../<?= htmlspecialchars($vid['thumbnail']) ?>" class="h-12 w-20 object-cover rounded">
                    <?php elseif ($vid['video_type'] === 'youtube'): 
                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $vid['video_url'], $match);
                        $yt_id = $match[1] ?? '';
                    ?>
                        <img src="https://img.youtube.com/vi/<?= $yt_id ?>/default.jpg" class="h-12 w-20 object-cover rounded">
                    <?php else: ?>
                        <div class="h-12 w-20 bg-gray-200 flex items-center justify-center rounded"><i class="fas fa-video text-gray-400"></i></div>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= htmlspecialchars($vid['title']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="capitalize px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"><?= $vid['video_type'] ?></span></td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?= $vid['display_order'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $vid['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= ucfirst($vid['status']) ?></span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button type="button" class="text-indigo-600 hover:text-indigo-900 mr-3 edit-btn"
                        data-id="<?= $vid['id'] ?>"
                        data-title="<?= htmlspecialchars($vid['title']) ?>"
                        data-type="<?= $vid['video_type'] ?>"
                        data-url="<?= htmlspecialchars($vid['video_url']) ?>"
                        data-desc="<?= htmlspecialchars($vid['description']) ?>"
                        data-order="<?= $vid['display_order'] ?>"
                        data-status="<?= $vid['status'] ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this video?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $vid['id'] ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($videos)): ?>
            <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No videos found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="videoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 max-w-2xl overflow-hidden">
        <form id="videoForm" method="POST" enctype="multipart/form-data">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Manage Video</h3>
                <button type="button" onclick="document.getElementById('videoModal').classList.add('hidden')" class="text-gray-500 hover:text-red-500 focus:outline-none"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="videoId">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="videoTitle" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Video Type</label>
                        <select name="video_type" id="videoType" class="w-full border-gray-300 rounded-md shadow-sm p-2 border" onchange="toggleType()">
                            <option value="youtube">YouTube URL</option>
                            <option value="mp4">MP4 Upload</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <select name="status" id="videoStatus" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div id="ytInput">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">YouTube URL <span class="text-red-500">*</span></label>
                    <input type="url" name="video_url" id="videoUrl" class="w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div id="mp4Input" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">MP4 File</label>
                    <input type="file" name="video_file" accept="video/mp4" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing on edit.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Custom Thumbnail (Optional)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" id="videoDesc" rows="2" class="w-full border-gray-300 rounded-md shadow-sm p-2 border"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Display Order</label>
                        <input type="number" name="display_order" id="videoOrder" value="0" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t text-right bg-gray-50">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded shadow hover:bg-opacity-90 font-semibold">Save Video</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleType() {
        const type = document.getElementById('videoType').value;
        if (type === 'youtube') {
            document.getElementById('ytInput').classList.remove('hidden');
            document.getElementById('mp4Input').classList.add('hidden');
            document.getElementById('videoUrl').required = true;
        } else {
            document.getElementById('ytInput').classList.add('hidden');
            document.getElementById('mp4Input').classList.remove('hidden');
            document.getElementById('videoUrl').required = false;
        }
    }

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('formAction').value = 'edit';
            document.getElementById('videoId').value = this.getAttribute('data-id');
            document.getElementById('videoTitle').value = this.getAttribute('data-title');
            document.getElementById('videoType').value = this.getAttribute('data-type');
            document.getElementById('videoUrl').value = this.getAttribute('data-url');
            document.getElementById('videoDesc').value = this.getAttribute('data-desc');
            document.getElementById('videoOrder').value = this.getAttribute('data-order');
            document.getElementById('videoStatus').value = this.getAttribute('data-status');
            
            toggleType();
            document.getElementById('videoModal').classList.remove('hidden');
        });
    });

    document.getElementById('videoModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>

<?php include 'includes/footer.php'; ?>
