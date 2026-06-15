<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Fetch all approved gallery images
$stmt = $pdo->prepare("SELECT * FROM gallery WHERE status = 1 ORDER BY created_at DESC");
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="relative h-72 md:h-[500px] bg-cover bg-[center_20%]" style="background-image: url('assets/images/herobanner.png');">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative h-full flex items-center justify-center text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white drop-shadow-md" data-aos="fade-up">Photo Gallery</h1>
    </div>
</section>

<section class="py-16 bg-light">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-dark mb-3">Our Moments</h2>
            <div class="w-16 h-1 bg-primary mx-auto"></div>
        </div>

        <!-- Gallery Filters -->
        <div class="flex flex-wrap justify-center gap-3 mb-10" data-aos="fade-up" data-aos-delay="100">
            <button class="bg-primary text-white px-6 py-2 rounded-full text-sm font-bold shadow-lg transition">All Photos</button>
            <button class="bg-white border border-gray-300 text-gray-600 px-6 py-2 rounded-full text-sm font-bold hover:border-primary hover:text-primary transition shadow-sm">Events</button>
            <button class="bg-white border border-gray-300 text-gray-600 px-6 py-2 rounded-full text-sm font-bold hover:border-primary hover:text-primary transition shadow-sm">Parichay Sammelan</button>
            <button class="bg-white border border-gray-300 text-gray-600 px-6 py-2 rounded-full text-sm font-bold hover:border-primary hover:text-primary transition shadow-sm">Religious Programs</button>
            <button class="bg-white border border-gray-300 text-gray-600 px-6 py-2 rounded-full text-sm font-bold hover:border-primary hover:text-primary transition shadow-sm">Temple Functions</button>
        </div>

        <?php if (empty($photos)): ?>
            <div class="text-center text-gray-500 py-10">
                <i class="fas fa-images text-5xl mb-4 text-gray-300"></i>
                <p class="text-xl">No photos available at the moment.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($photos as $photo): ?>
                    <a href="<?= htmlspecialchars($photo['image_path']) ?>" target="_blank" class="group relative overflow-hidden rounded-xl shadow-md block h-64 border border-gray-100 bg-white" data-aos="zoom-in">
                        <img src="<?= htmlspecialchars($photo['image_path']) ?>" alt="<?= htmlspecialchars($photo['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <i class="fas fa-search-plus text-white text-3xl"></i>
                        </div>
                        <?php if(!empty($photo['title'])): ?>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                                <p class="text-white font-medium text-center truncate"><?= htmlspecialchars($photo['title']) ?></p>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
