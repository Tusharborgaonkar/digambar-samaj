<?php include 'includes/header.php'; ?>
<?php
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>

<div class="bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar / Filters -->
            <div class="w-full md:w-1/4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 sticky top-24">
                    <h3 class="text-lg font-bold text-dark border-b pb-3 mb-4"><i class="fas fa-filter text-primary mr-2"></i> Refine Search</h3>
                    
                    <!-- Filter groups -->
                    <div class="mb-5">
                        <label class="block font-semibold text-gray-700 mb-2 text-sm">Looking For</label>
                        <select class="w-full border-gray-300 rounded p-2 text-sm bg-gray-50 border focus:border-primary focus:outline-none">
                            <option>Bride</option>
                            <option>Groom</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="block font-semibold text-gray-700 mb-2 text-sm">Age</label>
                        <div class="flex items-center gap-2">
                            <input type="number" placeholder="18" class="w-1/2 border-gray-300 border rounded p-2 text-sm bg-gray-50 focus:border-primary focus:outline-none">
                            <span class="text-gray-500 text-sm">to</span>
                            <input type="number" placeholder="40" class="w-1/2 border-gray-300 border rounded p-2 text-sm bg-gray-50 focus:border-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block font-semibold text-gray-700 mb-2 text-sm">Religion / Sect</label>
                        <select class="w-full border-gray-300 rounded p-2 text-sm bg-gray-50 border focus:border-primary focus:outline-none">
                            <option>All Jain Sects</option>
                            <option>Digambar</option>
                            <option>Shwetambar</option>
                            <option>Sthanakvasi</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="block font-semibold text-gray-700 mb-2 text-sm">Location</label>
                        <input type="text" placeholder="City or State" class="w-full border-gray-300 border rounded p-2 text-sm bg-gray-50 focus:border-primary focus:outline-none">
                    </div>

                    <button class="w-full bg-primary text-white font-bold py-2 rounded hover:bg-opacity-90 transition">Apply Filters</button>
                </div>
            </div>

            <!-- Profile List -->
            <div class="w-full md:w-3/4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-dark">Search Results <span class="text-gray-500 text-lg font-normal">(Showing 1-6 of 6 profiles)</span></h2>
                    <select class="border-gray-300 rounded p-2 text-sm bg-white border shadow-sm focus:outline-none">
                        <option>Sort by: Newest First</option>
                        <option>Sort by: Recently Active</option>
                        <option>Sort by: Age (Low to High)</option>
                    </select>
                </div>

                <div class="space-y-6">
                    
                    <?php
                    $profiles = [
                        ['name' => 'Pooja Ajay Dagli', 'id' => '23815', 'age' => 37, 'height' => "5' 3\"", 'loc' => 'Mumbai', 'edu' => 'MBA, MCA Or Equivalent', 'state' => 'Maharashtra', 'income' => 'Rs.10,00,001 - above', 'country' => 'India', 'lang' => 'Gujrathi', 'sect' => 'Sthanakwas', 'img' => 'https://images.unsplash.com/photo-1554151228-14d9def656e4?w=400', 'gender' => 'Bride'],
                        ['name' => 'Rahul Jain', 'id' => '23818', 'age' => 29, 'height' => "5' 11\"", 'loc' => 'Bangalore', 'edu' => 'Software Engineer, B.Tech', 'state' => 'Karnataka', 'income' => 'Rs.25,00,001 - above', 'country' => 'India', 'lang' => 'Hindi', 'sect' => 'Digambar', 'img' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400', 'gender' => 'Groom'],
                        ['name' => 'Neha Shah', 'id' => '23816', 'age' => 28, 'height' => "5' 5\"", 'loc' => 'Ahmedabad', 'edu' => 'Dentist, BDS', 'state' => 'Gujarat', 'income' => 'Rs.15,00,001 - above', 'country' => 'India', 'lang' => 'Gujrathi', 'sect' => 'Shwetambar', 'img' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400', 'gender' => 'Bride'],
                        ['name' => 'Amit Desai', 'id' => '23819', 'age' => 31, 'height' => "5' 10\"", 'loc' => 'Surat', 'edu' => 'Chartered Accountant', 'state' => 'Gujarat', 'income' => 'Rs.30,00,001 - above', 'country' => 'India', 'lang' => 'Gujrathi', 'sect' => 'Digambar', 'img' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400', 'gender' => 'Groom'],
                        ['name' => 'Anjali Kothari', 'id' => '23817', 'age' => 27, 'height' => "5' 4\"", 'loc' => 'Delhi', 'edu' => 'HR Manager, MBA', 'state' => 'Delhi NCR', 'income' => 'Rs.12,00,001 - above', 'country' => 'India', 'lang' => 'Hindi', 'sect' => 'Digambar', 'img' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400', 'gender' => 'Bride'],
                        ['name' => 'Rohan Mehta', 'id' => '23820', 'age' => 32, 'height' => "5' 9\"", 'loc' => 'Pune', 'edu' => 'Business Owner', 'state' => 'Maharashtra', 'income' => 'Rs.40,00,001 - above', 'country' => 'India', 'lang' => 'Marathi', 'sect' => 'Sthanakvasi', 'img' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400', 'gender' => 'Groom']
                    ];

                    foreach ($profiles as $p):
                        $link = $is_logged_in ? "profile-details.php" : "login.php";
                    ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition flex flex-col sm:flex-row">
                        <div class="w-full sm:w-1/4 md:w-48 h-64 sm:h-auto relative">
                            <?php if ($is_logged_in): ?>
                                <img src="<?= $p['img'] ?>" alt="<?= $p['gender'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full object-cover bg-gray-200 relative overflow-hidden" style="background-image: url('<?= $p['img'] ?>'); background-size: cover; background-position: center; filter: blur(10px);"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-40 text-white p-4 text-center">
                                    <i class="fas fa-lock text-3xl mb-2"></i>
                                    <span class="text-sm font-bold">Login to view photo</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="w-full sm:w-3/4 p-5 flex flex-col justify-between">
                            <div>
                                <a href="<?= $link ?>" class="text-xl md:text-2xl text-primary font-bold hover:underline mb-2 block"><?= $p['name'] ?> [MID: <?= $p['id'] ?>]</a>
                                <hr class="border-gray-200 mb-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-gray-700 text-sm md:text-base">
                                    <div class="font-medium"><?= $p['age'] ?> Years, <?= $p['height'] ?></div>
                                    <div class="font-medium"><?= $p['loc'] ?></div>
                                    <div class="font-medium"><?= $p['edu'] ?></div>
                                    <div class="font-medium"><?= $p['state'] ?></div>
                                    <div class="font-medium"><?= $p['income'] ?></div>
                                    <div class="font-medium"><?= $p['country'] ?></div>
                                    <div class="font-medium"><?= $p['lang'] ?></div>
                                    <div class="font-medium"><?= $p['sect'] ?></div>
                                </div>
                            </div>
                            <div class="mt-6 flex gap-3">
                                <a href="<?= $link ?>" class="bg-primary text-white px-6 py-2 rounded text-sm font-semibold hover:bg-opacity-90 transition shadow-sm text-center">View Full Profile</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
                
                <!-- Pagination -->
                <div class="mt-10 flex justify-center">
                    <nav class="flex items-center gap-1">
                        <a href="#" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 rounded hover:bg-gray-50 transition"><i class="fas fa-chevron-left text-xs"></i></a>
                        <a href="#" class="px-4 py-2 bg-primary text-white rounded font-medium shadow-sm">1</a>
                        <a href="#" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 rounded hover:bg-gray-50 transition"><i class="fas fa-chevron-right text-xs"></i></a>
                    </nav>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
