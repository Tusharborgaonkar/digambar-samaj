<?php
require_once 'includes/db.php';

$pageTitle = 'News & Updates';
include 'includes/header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl text-center">
        <i class="fas fa-newspaper text-6xl text-primary mb-6"></i>
        <h1 class="text-4xl font-bold text-dark mb-4">News & Updates</h1>
        <p class="text-gray-600 text-lg mb-8">Stay tuned! We are currently gathering the latest news and updates for the community.</p>
        <a href="index.php" class="bg-primary text-white px-6 py-3 rounded-md font-semibold hover:bg-opacity-90 transition">Return to Home</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
