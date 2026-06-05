<?php
require_once 'includes/db.php';
include 'includes/header.php'; 

// Check if user is logged in and approved
$is_approved = false;
$is_logged_in = false;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $is_logged_in = true;
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_status = $stmt->fetchColumn();
    if ($user_status === 'approved') {
        $is_approved = true;
    }
} else if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $is_logged_in = true;
    $is_approved = true;
}

// Build Dynamic Query
$where = ["status = 'approved'", "is_public = 1"];
$params = [];

$genderFilter = $_GET['gender'] ?? 'Bride';
if ($genderFilter === 'Bride' || $genderFilter === 'Groom') {
    $genderVal = ($genderFilter === 'Bride') ? 'Female' : 'Male';
    $where[] = "gender = ?";
    $params[] = $genderVal;
}

if (!empty($_GET['city'])) {
    $where[] = "native_place LIKE ?";
    $params[] = "%" . $_GET['city'] . "%";
}

if (!empty($_GET['education']) && $_GET['education'] !== 'Education All') {
    $where[] = "higher_education LIKE ?";
    $params[] = "%" . $_GET['education'] . "%";
}

$whereClause = implode(" AND ", $where);

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
$countStmt->execute($params);
$total_profiles = $countStmt->fetchColumn();
$total_pages = ceil($total_profiles / $limit);

$query = "SELECT * FROM users WHERE $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="bg-gray-50 py-8 min-h-screen">
    <div class="container mx-auto px-4">
        
        <?php if (!$is_approved): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded-md shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Limited View Mode</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <?php if (!$is_logged_in): ?>
                                <p>You are viewing profiles as a guest. Please <a href="login.php" class="font-bold underline hover:text-blue-900">log in</a> or <a href="register.php" class="font-bold underline hover:text-blue-900">register</a> to view photos and full details.</p>
                            <?php else: ?>
                                <p>Your profile is currently pending approval. Once an admin approves your profile, you will be able to view photos and full details.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar / Filters -->
            <div class="w-full md:w-1/4">
                <form action="profiles.php" method="GET" class="bg-white border border-gray-200 rounded-sm shadow-sm">
                    <!-- Advanced Search Header -->
                    <div class="bg-primary text-white text-center py-3 font-semibold text-base rounded-t-sm">
                        Advanced Search
                    </div>
                    
                    <!-- Sub Header -->
                    <div class="text-primary text-center font-semibold text-sm py-3 border-b border-gray-100 bg-gray-50">
                        Search By City, Country etc
                    </div>

                    <div class="p-5">
                        <!-- Bride / Groom Radio -->
                        <div class="flex items-center gap-6 mb-4 text-sm text-gray-700 font-medium">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="gender" value="Bride" <?= $genderFilter === 'Bride' ? 'checked' : '' ?> class="accent-primary w-4 h-4"> Bride
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="gender" value="Groom" <?= $genderFilter === 'Groom' ? 'checked' : '' ?> class="accent-primary w-4 h-4"> Groom
                            </label>
                        </div>
                        
                        <!-- Country -->
                        <select name="country" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                            <option selected>India</option>
                        </select>
                        
                        <!-- City -->
                        <input type="text" name="city" placeholder="Enter City Name" value="<?= htmlspecialchars($_GET['city'] ?? '') ?>" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        
                        <!-- Education -->
                        <select name="education" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                            <option>Education All</option>
                            <option <?= ($_GET['education'] ?? '') === 'Bachelors' ? 'selected' : '' ?>>Bachelors</option>
                            <option <?= ($_GET['education'] ?? '') === 'Masters' ? 'selected' : '' ?>>Masters</option>
                            <option <?= ($_GET['education'] ?? '') === 'Doctorate' ? 'selected' : '' ?>>Doctorate</option>
                            <option <?= ($_GET['education'] ?? '') === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                        </select>

                        <!-- Sampraday -->
                        <select name="sampraday" class="w-full border border-gray-300 rounded-md p-2.5 mb-5 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                            <option>Sampraday All</option>
                        </select>
                        
                        <!-- Search Button -->
                        <button type="submit" class="w-full bg-primary text-white font-semibold py-2.5 rounded-md hover:bg-opacity-90 transition shadow-sm">
                            Search
                        </button>
                    </div>
                </form>

                <!-- Latest Profile Section -->
                <div class="bg-white border border-gray-200 rounded-sm mt-6 shadow-sm">
                    <div class="bg-gray-50 text-primary text-center font-semibold text-base py-3 border-b border-gray-200 rounded-t-sm">
                        Latest Profile
                    </div>
                    <div class="p-5 space-y-3 text-sm font-medium">
                        <a href="profiles.php?gender=Bride" class="block text-gray-600 hover:text-primary hover:underline transition">All Bride</a>
                        <a href="profiles.php?gender=Groom" class="block text-gray-600 hover:text-primary hover:underline transition">All Groom</a>
                        <a href="profiles.php?education=Doctorate" class="block text-gray-600 hover:text-primary hover:underline transition">All Doctors</a>
                        <a href="profiles.php?education=LLB" class="block text-gray-600 hover:text-primary hover:underline transition">All LLB, LLM</a>
                        <a href="profiles.php?education=Engineer" class="block text-gray-600 hover:text-primary hover:underline transition">All Engineers</a>
                        <a href="profiles.php?education=MBA" class="block text-gray-600 hover:text-primary hover:underline transition">All MBA, MCA</a>
                        <a href="profiles.php?education=CA" class="block text-gray-600 hover:text-primary hover:underline transition">All CA, CS, ICWAI, CFS</a>
                        <a href="profiles.php?manglik=yes" class="block text-gray-600 hover:text-primary hover:underline transition">All Manglik</a>
                        <a href="profiles.php?nri=yes" class="block text-gray-600 hover:text-primary hover:underline transition">All NRI</a>
                        <a href="profiles.php?occupation=Service" class="block text-gray-600 hover:text-primary hover:underline transition">All Service</a>
                        <a href="profiles.php?occupation=Business" class="block text-gray-600 hover:text-primary hover:underline transition">All Business</a>
                        <a href="profiles.php?occupation=Profession" class="block text-gray-600 hover:text-primary hover:underline transition">All Profession</a>
                        <a href="profiles.php?marital=Widow" class="block text-gray-600 hover:text-primary hover:underline transition">All Widow</a>
                        <a href="profiles.php?marital=Divorcee" class="block text-gray-600 hover:text-primary hover:underline transition">All Divorcee</a>
                    </div>
                </div>
            </div>

            <!-- Profile List -->
            <div class="w-full md:w-3/4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-dark">Search Results <span class="text-gray-500 text-lg font-normal">(<?= $total_profiles ?> profiles found)</span></h2>
                </div>

                <div class="space-y-6">
                    <?php if (count($profiles) > 0): ?>
                        <?php foreach ($profiles as $p): 
                            // Determine display values
                            $age = 'N/A';
                            if (!empty($p['birth_date'])) {
                                $bday = new DateTime($p['birth_date']);
                                $today = new DateTime('today');
                                $age = $bday->diff($today)->y;
                            }
                            $img = !empty($p['profile_photo']) ? $p['profile_photo'] : 'https://ui-avatars.com/api/?name='.urlencode($p['full_name']).'&background=random';
                        ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition flex flex-col sm:flex-row">
                            <div class="w-full sm:w-1/4 md:w-48 h-64 sm:h-auto relative bg-gray-100 flex items-center justify-center overflow-hidden">
                                <?php if ($is_approved): ?>
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Profile" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <!-- Blurred photo with lock overlay for guests / pending users -->
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Profile" class="w-full h-full object-cover blur-lg opacity-30 select-none pointer-events-none">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500 p-4 text-center">
                                        <div class="w-14 h-14 bg-white/80 rounded-full flex items-center justify-center mb-2 shadow">
                                            <i class="fas fa-lock text-xl text-gray-400"></i>
                                        </div>
                                        <span class="text-xs font-semibold bg-white/80 px-3 py-1 rounded-full shadow-sm"><?= $is_logged_in ? 'Approval Pending' : 'Login to View' ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="w-full sm:w-3/4 p-5 flex flex-col justify-between">
                                <div>
                                    <a href="profile-details.php?id=<?= $p['id'] ?>" class="text-xl md:text-2xl text-primary font-bold hover:underline mb-2 block">
                                        <?= htmlspecialchars($p['full_name']) ?> <span class="text-gray-500 text-lg font-normal">[MID: <?= htmlspecialchars($p['profile_id']) ?>]</span>
                                    </a>
                                    <hr class="border-gray-200 mb-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-gray-700 text-sm md:text-base">
                                        <div class="font-medium"><i class="fas fa-calendar-alt text-gray-400 w-5"></i> <?= $age ?> Years, <?= htmlspecialchars($p['height'] ?? 'N/A') ?></div>
                                        <div class="font-medium"><i class="fas fa-map-marker-alt text-gray-400 w-5"></i> <?= htmlspecialchars($p['native_place'] ?? 'N/A') ?></div>
                                        <div class="font-medium"><i class="fas fa-graduation-cap text-gray-400 w-5"></i> <?= htmlspecialchars($p['higher_education'] ?? 'N/A') ?></div>
                                        <div class="font-medium"><i class="fas fa-briefcase text-gray-400 w-5"></i> <?= htmlspecialchars($p['occupation'] ?? 'N/A') ?></div>
                                        <div class="font-medium"><i class="fas fa-money-bill text-gray-400 w-5"></i> <?= htmlspecialchars($p['monthly_income'] ?? 'N/A') ?></div>
                                        <div class="font-medium"><i class="fas fa-praying-hands text-gray-400 w-5"></i> Digambar Jain</div>
                                    </div>
                                </div>
                                <div class="mt-6 flex gap-3">
                                    <a href="profile-details.php?id=<?= $p['id'] ?>" class="bg-primary text-white px-6 py-2 rounded text-sm font-semibold hover:bg-opacity-90 transition shadow-sm text-center flex items-center justify-center">
                                        <?php if (!$is_approved): ?><i class="fas fa-lock mr-2 text-xs"></i><?php endif; ?>
                                        View Full Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
                            <h3 class="text-xl font-bold text-gray-700 mb-2">No Profiles Found</h3>
                            <p class="text-gray-500">We couldn't find any profiles matching your search criteria. Try adjusting your filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="mt-10 flex justify-center">
                    <nav class="flex items-center gap-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&gender=<?= urlencode($genderFilter) ?>&city=<?= urlencode($_GET['city'] ?? '') ?>&education=<?= urlencode($_GET['education'] ?? '') ?>" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 rounded hover:bg-gray-50 transition"><i class="fas fa-chevron-left text-xs"></i></a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>&gender=<?= urlencode($genderFilter) ?>&city=<?= urlencode($_GET['city'] ?? '') ?>&education=<?= urlencode($_GET['education'] ?? '') ?>" class="px-4 py-2 <?= $i === $page ? 'bg-primary text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50' ?> rounded font-medium transition"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&gender=<?= urlencode($genderFilter) ?>&city=<?= urlencode($_GET['city'] ?? '') ?>&education=<?= urlencode($_GET['education'] ?? '') ?>" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 rounded hover:bg-gray-50 transition"><i class="fas fa-chevron-right text-xs"></i></a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
