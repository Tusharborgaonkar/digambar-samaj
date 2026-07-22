<?php
require_once 'includes/db.php';

$pageTitle = 'News & Updates';
include 'includes/header.php';

// Fetch active news
$news_items = [];
try {
    $stmt = $pdo->query("SELECT * FROM news WHERE status = 1 ORDER BY created_at DESC");
    $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle error quietly or log it
}
?>

<div class="bg-gray-50 py-12 min-h-[60vh]">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12">
            <i class="fas fa-newspaper text-5xl text-primary mb-4"></i>
            <h1 class="text-4xl font-bold text-dark mb-4">News & Updates</h1>
            <p class="text-gray-600 text-lg">Stay informed with the latest news and announcements from the community.</p>
        </div>

        <?php if (empty($news_items)): ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <p class="text-gray-500 text-lg mb-6">No news articles found at the moment. Please check back later.</p>
                <a href="index.php" class="bg-primary text-white px-6 py-3 rounded-md font-semibold hover:bg-opacity-90 transition">Return to Home</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($news_items as $news): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                        <?php if (!empty($news['image']) && file_exists(ltrim(str_replace('../', '', $news['image']), '/\\'))): ?>
                            <img src="image.php?file=<?= urlencode(ltrim(str_replace('../', '', $news['image']), '/\\')) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">
                                <i class="fas fa-image text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="text-xs text-gray-500 mb-2 flex items-center">
                                <i class="far fa-calendar-alt mr-2"></i> <?= date('M d, Y', strtotime($news['created_at'])) ?>
                            </div>
                            <h3 class="text-xl font-bold text-dark mb-3"><?= htmlspecialchars($news['title']) ?></h3>
                            <div class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?= nl2br(htmlspecialchars($news['content'])) ?>
                            </div>
                            <!-- You could add a 'Read More' link here if you create a detailed news page -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
