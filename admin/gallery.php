<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';

$success_msg = '';
$error_msg = '';

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $title = trim($_POST['title'] ?? '');
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $uploadDir = '../assets/images/gallery/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('img_') . '.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $dbPath = 'assets/images/gallery/' . $fileName;
                
                $stmt = $pdo->prepare("INSERT INTO gallery (title, image_path) VALUES (?, ?)");
                if ($stmt->execute([$title, $dbPath])) {
                    $success_msg = "Image uploaded successfully!";
                } else {
                    $error_msg = "Failed to save image path in database.";
                }
            } else {
                $error_msg = "Failed to upload image.";
            }
        } else {
            $error_msg = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
    } else {
        $error_msg = "Please select an image to upload.";
    }
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $photo = $stmt->fetch();
    
    if ($photo) {
        $filePath = '../' . $photo['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = "Image deleted successfully!";
    }
}

// Fetch all gallery images
$stmt = $pdo->prepare("SELECT * FROM gallery ORDER BY created_at DESC");
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Photo Gallery - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E3A5F',
                        secondary: '#C97B84',
                        accent: '#C97B84',
                        dark: '#2D2D2D',
                        light: '#FAFAFA',
                        admin_sidebar: '#111827',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col h-screen overflow-hidden pt-16 md:pt-0">
        <!-- Top Header -->
        <header class="bg-white h-16 border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0 shadow-sm md:hidden">
            <h2 class="text-xl font-semibold text-gray-800">Photo Gallery</h2>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Photo Gallery Management</h1>
                </div>

                <?php if ($success_msg): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p><?= htmlspecialchars($success_msg) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?= htmlspecialchars($error_msg) ?></p>
                </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Upload New Photo</h2>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" value="upload">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image Title / Description</label>
                                <input type="text" name="title" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border" placeholder="Enter optional title">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select Image</label>
                                <input type="file" name="image" accept="image/*" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="bg-primary text-white px-6 py-2 rounded shadow hover:bg-opacity-90 transition"><i class="fas fa-upload mr-2"></i>Upload Photo</button>
                        </div>
                    </form>
                </div>

                <!-- Gallery Grid -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Existing Photos</h2>
                    
                    <?php if (empty($photos)): ?>
                        <p class="text-gray-500 py-4 text-center">No photos uploaded yet.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <?php foreach ($photos as $photo): ?>
                                <div class="border rounded-lg overflow-hidden group relative">
                                    <img src="../<?= htmlspecialchars($photo['image_path']) ?>" alt="<?= htmlspecialchars($photo['title']) ?>" class="w-full h-48 object-cover">
                                    
                                    <div class="p-3 bg-white">
                                        <p class="text-sm font-semibold truncate" title="<?= htmlspecialchars($photo['title']) ?>"><?= htmlspecialchars($photo['title'] ?: 'Untitled') ?></p>
                                        <p class="text-xs text-gray-500"><?= date('M d, Y', strtotime($photo['created_at'])) ?></p>
                                    </div>
                                    
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                        <a href="?delete=<?= $photo['id'] ?>" onclick="return confirm('Are you sure you want to delete this photo?');" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 shadow-lg">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
