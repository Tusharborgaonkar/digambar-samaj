<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$success_msg = '';
$error_msg = '';

// Handle actions (Approve, Reject/Pending, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];

        try {
            if ($action === 'approve') {
                $stmt = $pdo->prepare("UPDATE success_stories SET status = 'approved' WHERE id = ?");
                $stmt->execute([$id]);
                $success_msg = "Story approved successfully.";
            } elseif ($action === 'pending') {
                $stmt = $pdo->prepare("UPDATE success_stories SET status = 'pending' WHERE id = ?");
                $stmt->execute([$id]);
                $success_msg = "Story set to pending.";
            } elseif ($action === 'delete') {
                // First get the photo path to delete the file
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Success Stories - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E53E3E',
                        secondary: '#ED8936',
                        dark: '#2D3748',
                        light: '#F7FAFC',
                        admin_sidebar: '#1E293B',
                        accent: '#F6E05E',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden pt-16 md:pt-0">
        <!-- Header placeholder if sidebar doesn't include it fully, though sidebar.php includes header structure, we close the wrapper at the bottom. -->
        <!-- Since sidebar.php includes `<main class="flex-1 overflow-x-hidden overflow-y-auto bg-light p-6">`, we just write content here -->
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manage Success Stories</h1>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
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
                                $photoPath = !empty($story['photo']) ? '../' . htmlspecialchars($story['photo']) : '../assets/images/placeholder-couple.png';
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
                                            data-story="<?= htmlspecialchars($story['story']) ?>">
                                            <i class="fas fa-eye text-lg"></i>
                                        </button>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $story['id'] ?>">
                                            <?php if ($story['status'] === 'pending'): ?>
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="text-green-600 hover:text-green-900 mr-3" title="Approve"><i class="fas fa-check-circle text-lg"></i></button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="pending">
                                                <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Mark as Pending"><i class="fas fa-clock text-lg"></i></button>
                                            <?php endif; ?>
                                        </form>
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this story?');">
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

</main>
</div>

<!-- Story Modal -->
<div id="storyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 max-w-2xl overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800" id="modalCoupleName">Couple Name</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-red-500 focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm font-semibold text-gray-500 mb-4" id="modalCity">City</p>
            <div class="text-gray-700 whitespace-pre-wrap max-h-96 overflow-y-auto" id="modalStoryContent">Story content here...</div>
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
            document.getElementById('storyModal').classList.remove('hidden');
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

    document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
        const sidebar = document.querySelector('aside');
        sidebar.classList.toggle('hidden');
        sidebar.classList.toggle('absolute');
        sidebar.classList.toggle('z-40');
        sidebar.classList.toggle('h-full');
    });
</script>
</body>
</html>
