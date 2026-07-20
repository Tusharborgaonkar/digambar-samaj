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

<!-- Photos Section -->
<section class="py-16 bg-light">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-dark mb-3">Photo Gallery</h2>
            <div class="w-16 h-1 bg-primary mx-auto"></div>
            <p class="text-gray-600 mt-4">Glimpses of our community events and gatherings.</p>
        </div>
        
        <?php if(empty($photos)): ?>
            <p class="text-center text-gray-500">No photos available at the moment.</p>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                <?php foreach($photos as $p): 
                    $clean_path = ltrim(str_replace('../', '', $p['image_path']), '/');
                ?>
                    <a href="image.php?file=<?= urlencode($clean_path) ?>" data-fancybox="gallery" data-caption="<?= htmlspecialchars($p['title']) ?>" class="group block overflow-hidden rounded-xl shadow-sm hover:shadow-md transition">
                        <div class="aspect-w-4 aspect-h-3">
                            <img src="image.php?file=<?= urlencode($clean_path) ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                        </div>
                        <?php if(!empty($p['title'])): ?>
                            <div class="p-3 bg-white">
                                <h3 class="text-sm font-semibold text-gray-800 text-center truncate"><?= htmlspecialchars($p['title']) ?></h3>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- YouTube Videos Section -->
<section class="py-16 bg-white border-t border-gray-100">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-dark mb-3">Event Videos</h2>
            <div class="w-16 h-1 bg-primary mx-auto"></div>
            <p class="text-gray-600 mt-4">Watch highlights from our past events and programs.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Video Placeholder 1 -->
            <div class="bg-light rounded-xl overflow-hidden shadow-md" data-aos="fade-up">
                <div class="aspect-w-16 aspect-h-9 h-64">
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg text-dark">Sample Event Video 1</h3>
                </div>
            </div>
            <!-- Video Placeholder 2 -->
            <div class="bg-light rounded-xl overflow-hidden shadow-md" data-aos="fade-up" data-aos-delay="100">
                <div class="aspect-w-16 aspect-h-9 h-64">
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg text-dark">Sample Event Video 2</h3>
                </div>
            </div>
            <!-- Video Placeholder 3 -->
            <div class="bg-light rounded-xl overflow-hidden shadow-md" data-aos="fade-up" data-aos-delay="200">
                <div class="aspect-w-16 aspect-h-9 h-64">
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg text-dark">Sample Event Video 3</h3>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Fancybox Script and CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Fancybox.bind("[data-fancybox]", {
            // Your custom options
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
