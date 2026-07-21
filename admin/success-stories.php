<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS success_stories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        couple_name VARCHAR(255) NOT NULL,
        city VARCHAR(100) NOT NULL,
        story TEXT NOT NULL,
        photo VARCHAR(255) NULL,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE success_stories ADD COLUMN display_order INT DEFAULT 0");
} catch (Exception $e) {}
$success_msg = '';
$error_msg = '';

// Handle actions (Approve, Reject/Pending, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        try {
            if ($action === 'add') {
                $couple_name = $_POST['couple_name'] ?? '';
                $city = $_POST['city'] ?? '';
                $story_text = $_POST['story'] ?? '';
                $photo = '';

                if (empty($couple_name) || empty($city) || empty($story_text)) {
                    $error_msg = "Please fill in all required fields.";
                } else {
                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../uploads/success_stories/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_ext, $allowed_exts)) {
                            $new_filename = uniqid('story_') . '.' . $file_ext;
                            $upload_path = $upload_dir . $new_filename;
                            
                            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                                $photo = 'uploads/success_stories/' . $new_filename;
                            } else {
                                $error_msg = "Failed to upload photo.";
                            }
                        } else {
                            $error_msg = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed.";
                        }
                    } else {
                        $error_msg = "Please upload a photo.";
                    }
                }

                if (empty($error_msg)) {
                    $stmt = $pdo->prepare("INSERT INTO success_stories (couple_name, city, story, photo, status) VALUES (?, ?, ?, ?, 'approved')");
                    $stmt->execute([$couple_name, $city, $story_text, $photo]);
                    $success_msg = "Success story added successfully.";
                }
            } elseif (isset($_POST['id'])) {
                $id = (int)$_POST['id'];
                if ($action === 'edit') {
                    $couple_name = $_POST['couple_name'] ?? '';
                    $city = $_POST['city'] ?? '';
                    $story_text = $_POST['story'] ?? '';
                    $display_order = (int)($_POST['display_order'] ?? 0);
                    
                    if (empty($couple_name) || empty($city) || empty($story_text)) {
                        $error_msg = "Please fill in all required fields.";
                    } else {
                        // Check if a new photo was uploaded
                        $photo_query = "";
                        $params = [$couple_name, $city, $story_text, $display_order];
                        
                        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = '../uploads/success_stories/';
                            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                            
                            if (in_array($file_ext, $allowed_exts)) {
                                $new_filename = uniqid('story_') . '.' . $file_ext;
                                $upload_path = $upload_dir . $new_filename;
                                
                                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                                    // Get old photo to delete
                                    $stmt = $pdo->prepare("SELECT photo FROM success_stories WHERE id = ?");
                                    $stmt->execute([$id]);
                                    $old_story = $stmt->fetch();
                                    if ($old_story && !empty($old_story['photo']) && file_exists('../' . $old_story['photo'])) {
                                        unlink('../' . $old_story['photo']);
                                    }
                                    
                                    $photo_query = ", photo = ?";
                                    $params[] = 'uploads/success_stories/' . $new_filename;
                                } else {
                                    $error_msg = "Failed to upload photo.";
                                }
                            } else {
                                $error_msg = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed.";
                            }
                        }
                        
                        if (empty($error_msg)) {
                            $params[] = $id;
                            $stmt = $pdo->prepare("UPDATE success_stories SET couple_name = ?, city = ?, story = ?, display_order = ? $photo_query WHERE id = ?");
                            $stmt->execute($params);
                            $success_msg = "Story updated successfully.";
                        }
                    }
                } elseif ($action === 'approve') {
                    $stmt = $pdo->prepare("UPDATE success_stories SET status = 'approved' WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_msg = "Story approved successfully.";
                } elseif ($action === 'pending') {
                    $stmt = $pdo->prepare("UPDATE success_stories SET status = 'pending' WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_msg = "Story set to pending.";
                } elseif ($action === 'delete') {
                    $stmt = $pdo->prepare("SELECT photo FROM success_stories WHERE id = ?");
                    $stmt->execute([$id]);
                    $story = $stmt->fetch();
                    
                    if ($story && !empty($story['photo']) && file_exists('../' . $story['photo'])) {
                        unlink('../' . $story['photo']);
                    }

                    $stmt = $pdo->prepare("DELETE FROM success_stories WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_msg = "Story deleted successfully.";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch all stories
try {
    $stmt = $pdo->query("SELECT * FROM success_stories ORDER BY created_at DESC");
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Failed to fetch stories: " . $e->getMessage();
    $stories = [];
}
$current_page = 'success-stories.php';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manage Success Stories</h1>
    <button onclick="document.getElementById('addStoryModal').classList.remove('hidden')" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition font-semibold shadow-sm"><i class="fas fa-plus mr-2"></i> Add Success Story</button>
</div>

<?php if ($success_msg): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
    <?= htmlspecialchars($success_msg) ?>
</div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
    <?= htmlspecialchars($error_msg) ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Couple Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Story</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($stories) > 0): ?>
                            <?php foreach ($stories as $story): 
                                $cleanPath = !empty($story['photo']) ? ltrim(str_replace('../', '', $story['photo']), '/') : '';
                                $photoPath = !empty($cleanPath) ? '../image.php?file=' . urlencode($cleanPath) : '../assets/images/placeholder-couple.png';
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img src="<?= $photoPath ?>" alt="Photo" class="h-16 w-16 object-cover rounded-md border border-gray-200">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        <?= htmlspecialchars($story['couple_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        <?= htmlspecialchars($story['city']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($story['story']) ?>">
                                            <?= htmlspecialchars($story['story']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($story['status'] === 'approved'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-blue-600 hover:text-blue-900 mr-3 view-story-btn" title="View Details" 
                                            data-name="<?= htmlspecialchars($story['couple_name']) ?>" 
                                            data-city="<?= htmlspecialchars($story['city']) ?>" 
                                            data-story="<?= htmlspecialchars($story['story']) ?>"
                                            data-photo="<?= $photoPath ?>">
                                            <i class="fas fa-eye text-lg"></i>
                                        </button>
                                        <button type="button" class="text-indigo-600 hover:text-indigo-900 mr-3 edit-story-btn" title="Edit Story"
                                            data-id="<?= $story['id'] ?>"
                                            data-name="<?= htmlspecialchars($story['couple_name']) ?>" 
                                            data-city="<?= htmlspecialchars($story['city']) ?>" 
                                            data-story="<?= htmlspecialchars($story['story']) ?>"
                                            data-order="<?= $story['display_order'] ?? 0 ?>"
                                            data-photo="<?= $photoPath ?>">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $story['id'] ?>">
                                            <?php if ($story['status'] === 'pending'): ?>
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="text-green-600 hover:text-green-900 mr-3" title="Approve" onclick="event.preventDefault(); Swal.fire({title: 'Approve Story?', text: 'You are about to approve this story.', icon: 'success', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, approve it!'}).then((result) => { if (result.isConfirmed) { this.form.submit(); } });"><i class="fas fa-check-circle text-lg"></i></button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="pending">
                                                <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Mark as Pending" onclick="event.preventDefault(); Swal.fire({title: 'Hold Story?', text: 'You are about to mark this story as pending.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, mark as pending!'}).then((result) => { if (result.isConfirmed) { this.form.submit(); } });"><i class="fas fa-clock text-lg"></i></button>
                                            <?php endif; ?>
                                        </form>
                                        <form method="POST" class="inline-block" onsubmit="event.preventDefault(); Swal.fire({title: 'Are you sure?', text: 'You want to delete this story?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!'}).then((result) => { if (result.isConfirmed) { this.submit(); } });">
                                            <input type="hidden" name="id" value="<?= $story['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash-alt text-lg"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No success stories found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
</div>

<!-- Add Story Modal -->
<div id="addStoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 max-w-2xl overflow-hidden">
        <form method="POST" enctype="multipart/form-data">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Add New Success Story</h3>
                <button type="button" onclick="document.getElementById('addStoryModal').classList.add('hidden')" class="text-gray-500 hover:text-red-500 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Couple Name <span class="text-red-500">*</span></label>
                    <input type="text" name="couple_name" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-white">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                    <input type="text" name="city" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-white">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Story <span class="text-red-500">*</span></label>
                    <textarea name="story" rows="4" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-white"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Photo <span class="text-red-500">*</span></label>
                    <input type="file" name="photo" accept="image/*" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-white">
                </div>
            </div>
            <div class="px-6 py-4 border-t text-right bg-gray-50 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addStoryModal').classList.add('hidden')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded shadow hover:bg-gray-300 font-semibold">Cancel</button>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded shadow hover:bg-opacity-90 font-semibold">Save Story</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Story Modal -->
<div id="editStoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 max-w-2xl overflow-hidden">
        <form method="POST" enctype="multipart/form-data">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Edit Success Story</h3>
                <button type="button" onclick="document.getElementById('editStoryModal').classList.add('hidden')" class="text-gray-500 hover:text-red-500 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Couple Name <span class="text-red-500">*</span></label>
                    <input type="text" name="couple_name" id="edit_couple_name" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-white">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                    <input type="text" name="city" id="edit_city" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-white">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Display Order</label>
                    <input type="number" name="display_order" id="edit_display_order" value="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-white">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Story <span class="text-red-500">*</span></label>
                    <textarea name="story" id="edit_story" rows="4" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-white"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Current Photo</label>
                    <img id="edit_current_photo" src="" alt="Current Photo" class="h-24 w-24 object-cover rounded-md border border-gray-200">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Upload New Photo (Leave empty to keep existing)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-white">
                </div>
            </div>
            <div class="px-6 py-4 border-t text-right bg-gray-50 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('editStoryModal').classList.add('hidden')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded shadow hover:bg-gray-300 font-semibold">Cancel</button>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded shadow hover:bg-opacity-90 font-semibold">Update Story</button>
            </div>
        </form>
    </div>
</div>

<!-- Story Modal (View) -->
<div id="storyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 max-w-2xl overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800" id="modalCoupleName">Couple Name</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-red-500 focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 flex flex-col md:flex-row gap-6">
            <div class="w-full md:w-1/3 flex-shrink-0">
                <img id="modalPhoto" src="" alt="Couple Photo" class="w-full h-auto rounded-lg shadow-md border border-gray-200">
            </div>
            <div class="w-full md:w-2/3">
                <p class="text-sm font-semibold text-gray-500 mb-4" id="modalCity">City</p>
                <div class="text-gray-700 whitespace-pre-wrap max-h-96 overflow-y-auto" id="modalStoryContent">Story content here...</div>
            </div>
        </div>
        <div class="px-6 py-4 border-t text-right bg-gray-50">
            <button onclick="closeModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded shadow hover:bg-gray-300 font-semibold">Close</button>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.view-story-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalCoupleName').innerText = this.getAttribute('data-name');
            document.getElementById('modalCity').innerText = 'City: ' + this.getAttribute('data-city');
            document.getElementById('modalStoryContent').innerText = this.getAttribute('data-story');
            document.getElementById('modalPhoto').src = this.getAttribute('data-photo');
            document.getElementById('storyModal').classList.remove('hidden');
        });
    });

    document.querySelectorAll('.edit-story-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_couple_name').value = this.getAttribute('data-name');
            document.getElementById('edit_city').value = this.getAttribute('data-city');
            document.getElementById('edit_story').value = this.getAttribute('data-story');
            document.getElementById('edit_display_order').value = this.getAttribute('data-order');
            document.getElementById('edit_current_photo').src = this.getAttribute('data-photo');
            document.getElementById('editStoryModal').classList.remove('hidden');
        });
    });

    function closeModal() {
        document.getElementById('storyModal').classList.add('hidden');
    }

    // Close modal on click outside
    document.getElementById('storyModal').addEventListener('click', function(e) {
        if(e.target === this) {
            closeModal();
        }
    });
    
    document.getElementById('editStoryModal').addEventListener('click', function(e) {
        if(e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
