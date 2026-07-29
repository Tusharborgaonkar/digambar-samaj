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
                        <?php if (!empty($news['image']) && strpos($news['image'], 'data:image/') === 0): ?>
                            <img src="<?= $news['image'] ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="w-full h-48 object-cover">
                        <?php elseif (!empty($news['image']) && file_exists(ltrim(str_replace('../', '', $news['image']), '/\\'))): ?>
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
                            <button onclick="openNewsModal(<?= $news['id'] ?>)" class="text-primary font-semibold hover:underline inline-flex items-center">
                                View full news <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </button>

                            <!-- Hidden data for modal -->
                            <div id="news-title-<?= $news['id'] ?>" class="hidden"><?= htmlspecialchars($news['title']) ?></div>
                            <div id="news-date-<?= $news['id'] ?>" class="hidden"><i class="far fa-calendar-alt mr-2"></i> <?= date('M d, Y', strtotime($news['created_at'])) ?></div>
                            <div id="news-content-<?= $news['id'] ?>" class="hidden"><?= nl2br(htmlspecialchars($news['content'])) ?></div>
                            <div id="news-image-<?= $news['id'] ?>" class="hidden">
                                <?php if (!empty($news['image']) && strpos($news['image'], 'data:image/') === 0): ?>
                                    <img src="<?= $news['image'] ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="w-full h-64 md:h-80 object-cover rounded-lg mb-6 shadow-sm">
                                <?php elseif (!empty($news['image']) && file_exists(ltrim(str_replace('../', '', $news['image']), '/\\'))): ?>
                                    <img src="image.php?file=<?= urlencode(ltrim(str_replace('../', '', $news['image']), '/\\')) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="w-full h-64 md:h-80 object-cover rounded-lg mb-6 shadow-sm">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- News Modal -->
            <div id="newsModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black bg-opacity-60 backdrop-blur-sm transition-opacity">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col transform transition-all">
                    <div class="flex justify-between items-start p-6 border-b border-gray-100">
                        <div>
                            <h2 id="modal-title" class="text-2xl font-bold text-dark mb-2"></h2>
                            <div id="modal-date" class="text-sm text-primary font-medium"></div>
                        </div>
                        <button onclick="closeNewsModal()" class="text-gray-400 hover:text-red-500 transition focus:outline-none">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar">
                        <div id="modal-image"></div>
                        <div id="modal-body" class="text-gray-700 text-lg leading-relaxed"></div>
                    </div>
                    <div class="p-4 border-t border-gray-100 text-right bg-gray-50 rounded-b-xl">
                        <button onclick="closeNewsModal()" class="px-6 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition shadow-sm">Close</button>
                    </div>
                </div>
            </div>

            <script>
            function openNewsModal(id) {
                document.getElementById('modal-title').innerText = document.getElementById('news-title-' + id).innerText;
                document.getElementById('modal-date').innerHTML = document.getElementById('news-date-' + id).innerHTML;
                document.getElementById('modal-image').innerHTML = document.getElementById('news-image-' + id).innerHTML;
                document.getElementById('modal-body').innerHTML = document.getElementById('news-content-' + id).innerHTML;
                
                const modal = document.getElementById('newsModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeNewsModal() {
                const modal = document.getElementById('newsModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }
            
            // Close modal when clicking outside
            document.getElementById('newsModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeNewsModal();
                }
            });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
