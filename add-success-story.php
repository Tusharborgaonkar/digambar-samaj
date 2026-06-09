<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $couple_name = $_POST['couple_name'] ?? '';
    $city = $_POST['city'] ?? '';
    $story = $_POST['story'] ?? '';
    $photo = '';

    if (empty($couple_name) || empty($city) || empty($story)) {
        $error_msg = "Please fill in all required fields.";
    } else {
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/success_stories/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $new_filename = uniqid('story_') . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo = $upload_path;
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
        try {
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $stmt = $pdo->prepare("INSERT INTO success_stories (user_id, couple_name, city, story, photo, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $couple_name, $city, $story, $photo]);
            $success_msg = "Your success story has been submitted successfully! It will appear on the website once approved by the admin.";
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="py-16 bg-light min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white rounded-xl shadow-xl p-8" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-center text-dark mb-2">Share Your Success Story</h2>
            <p class="text-center text-gray-600 mb-8">We would love to hear how you found your life partner through our platform.</p>
            
            <?php if (!empty($success_msg)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Success!</p>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                </div>
                <div class="text-center">
                    <a href="success-stories.php" class="inline-block bg-primary text-white px-6 py-2 rounded-md hover:bg-opacity-90 transition font-bold">Back to Success Stories</a>
                </div>
            <?php else: ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?= htmlspecialchars($error_msg) ?></p>
                    </div>
                <?php endif; ?>
                
                <form action="add-success-story.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Couple Name (e.g., Rahul & Priya) <span class="text-red-500">*</span></label>
                        <input type="text" name="couple_name" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-3 border bg-gray-50" placeholder="Enter your names">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                        <input type="text" name="city" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-3 border bg-gray-50" placeholder="E.g., Mumbai, Delhi">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Your Story <span class="text-red-500">*</span></label>
                        <textarea name="story" rows="4" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-3 border bg-gray-50" placeholder="Tell us how you met and your experience..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Upload Couple Photo <span class="text-red-500">*</span></label>
                        <input type="file" name="photo" accept="image/*" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-opacity-90 cursor-pointer">
                        <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG, GIF</p>
                    </div>
                    
                    <div class="pt-4 flex justify-between items-center">
                        <a href="success-stories.php" class="text-gray-500 hover:text-primary font-medium">Cancel</a>
                        <button type="submit" class="bg-primary text-white px-8 py-3 rounded-md text-lg font-bold hover:bg-opacity-90 transition shadow-lg"><i class="fas fa-paper-plane mr-2"></i>Submit Story</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
