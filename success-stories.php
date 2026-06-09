<!-- success-stories.php -->
<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 
?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 max-w-5xl mx-auto" data-aos="fade-up">
            <h1 class="text-4xl md:text-5xl font-bold text-dark text-center md:text-left mb-6 md:mb-0">Success Stories</h1>
            <a href="add-success-story.php" class="bg-primary text-white px-6 py-3 rounded-md font-bold shadow-lg hover:bg-opacity-90 transition"><i class="fas fa-plus mr-2"></i>Share Your Success Story</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM success_stories WHERE status = 'approved' ORDER BY id DESC");
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($stories) > 0) {
                    foreach ($stories as $story): 
                        $photo = !empty($story['photo']) ? htmlspecialchars($story['photo']) : 'assets/images/placeholder-couple.png';
            ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 group border border-gray-100" data-aos="fade-up">
                            <div class="relative overflow-hidden aspect-[3/4]">
                                <img src="<?php echo $photo; ?>" alt="Couple" class="w-full h-full object-cover object-top group-hover:scale-110 transition duration-500">
                                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black via-black/70 to-transparent p-4 z-20">
                                    <h3 class="text-white font-bold text-lg"><?php echo htmlspecialchars($story['couple_name']); ?></h3>
                                    <p class="text-gray-200 text-sm font-medium"><?php echo htmlspecialchars($story['city']); ?></p>
                                </div>
                                <?php if (isset($story['created_at']) && strtotime($story['created_at']) > strtotime('-7 days')): ?>
                                <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded shadow z-20">New</div>
                                <?php endif; ?>
                            </div>
                            <div class="p-5">
                                <p class="text-gray-600 text-sm italic line-clamp-4">"<?php echo htmlspecialchars($story['story']); ?>"</p>
                            </div>
                        </div>
            <?php 
                    endforeach; 
                } else {
                    echo '<div class="col-span-1 md:col-span-3 text-center text-gray-500 py-10">No success stories available yet. Be the first to share your story!</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="col-span-1 md:col-span-3 text-center text-red-500 py-10">Failed to load success stories.</div>';
            }
            ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>