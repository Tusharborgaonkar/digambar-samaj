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

// Manglik filter
if (!empty($_GET['manglik'])) {
    $manglikVal = strtolower($_GET['manglik']);
    if ($manglikVal === 'yes') {
        $where[] = "manglik = 'Yes'";
    } elseif ($manglikVal === 'no') {
        $where[] = "manglik = 'No'";
    }
}

// Marital status filter
if (!empty($_GET['marital']) && $_GET['marital'] !== 'All') {
    $where[] = "marital_status = ?";
    $params[] = $_GET['marital'];
}

// Occupation filter
if (!empty($_GET['occupation']) && $_GET['occupation'] !== 'Occupation All') {
    $where[] = "occupation LIKE ?";
    $params[] = "%" . $_GET['occupation'] . "%";
}

// Age range filter
if (!empty($_GET['age_from']) && is_numeric($_GET['age_from'])) {
    $maxBirthDate = date('Y-m-d', strtotime('-' . (int)$_GET['age_from'] . ' years'));
    $where[] = "birth_date <= ?";
    $params[] = $maxBirthDate;
}
if (!empty($_GET['age_to']) && is_numeric($_GET['age_to'])) {
    $minBirthDate = date('Y-m-d', strtotime('-' . ((int)$_GET['age_to'] + 1) . ' years +1 day'));
    $where[] = "birth_date >= ?";
    $params[] = $minBirthDate;
}

$whereClause = implode(" AND ", $where);

// Build query string for pagination (preserve all filters)
$filterParams = [];
foreach (['gender', 'city', 'education', 'manglik', 'marital', 'occupation', 'age_from', 'age_to'] as $key) {
    if (!empty($_GET[$key])) {
        $filterParams[$key] = $_GET[$key];
    }
}
// Always include gender
if (empty($filterParams['gender'])) {
    $filterParams['gender'] = $genderFilter;
}
$filterQueryString = http_build_query($filterParams);

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
                        
                        <!-- City -->
                        <input type="text" name="city" placeholder="Enter City / Native Place" value="<?= htmlspecialchars($_GET['city'] ?? '') ?>" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        
                        <!-- Education -->
                        <?php
                        $educationOptions = ['Education All', 'Bachelors', 'Masters', 'Doctorate', 'Diploma', 'Engineer', 'MBA', 'MCA', 'LLB', 'LLM', 'CA', 'CS', 'ICWAI'];
                        $selectedEdu = $_GET['education'] ?? 'Education All';
                        ?>
                        <select name="education" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                            <?php foreach ($educationOptions as $edu): ?>
                                <option <?= $selectedEdu === $edu ? 'selected' : '' ?>><?= $edu ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Marital Status -->
                        <?php $selectedMarital = $_GET['marital'] ?? 'All'; ?>
                        <select name="marital" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                            <option value="All" <?= $selectedMarital === 'All' ? 'selected' : '' ?>>Marital Status All</option>
                            <option value="Never Married" <?= $selectedMarital === 'Never Married' ? 'selected' : '' ?>>Never Married</option>
                            <option value="Widow" <?= $selectedMarital === 'Widow' ? 'selected' : '' ?>>Widow</option>
                            <option value="Widower" <?= $selectedMarital === 'Widower' ? 'selected' : '' ?>>Widower</option>
                            <option value="Divorce" <?= $selectedMarital === 'Divorce' ? 'selected' : '' ?>>Divorcee</option>
                        </select>

                        <!-- Occupation -->
                        <?php $selectedOcc = $_GET['occupation'] ?? 'Occupation All'; ?>
                        <select name="occupation" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                            <option value="Occupation All" <?= $selectedOcc === 'Occupation All' ? 'selected' : '' ?>>Occupation All</option>
                            <option value="Service" <?= $selectedOcc === 'Service' ? 'selected' : '' ?>>Service / Job</option>
                            <option value="Business" <?= $selectedOcc === 'Business' ? 'selected' : '' ?>>Business</option>
                            <option value="Profession" <?= $selectedOcc === 'Profession' ? 'selected' : '' ?>>Profession</option>
                            <option value="Doctor" <?= $selectedOcc === 'Doctor' ? 'selected' : '' ?>>Doctor</option>
                            <option value="Engineer" <?= $selectedOcc === 'Engineer' ? 'selected' : '' ?>>Engineer</option>
                            <option value="Teacher" <?= $selectedOcc === 'Teacher' ? 'selected' : '' ?>>Teacher</option>
                        </select>

                        <!-- Manglik -->
                        <?php $selectedManglik = $_GET['manglik'] ?? ''; ?>
                        <select name="manglik" class="w-full border border-gray-300 rounded-md p-2.5 mb-4 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                            <option value="" <?= $selectedManglik === '' ? 'selected' : '' ?>>Manglik All</option>
                            <option value="yes" <?= $selectedManglik === 'yes' ? 'selected' : '' ?>>Manglik</option>
                            <option value="no" <?= $selectedManglik === 'no' ? 'selected' : '' ?>>Non-Manglik</option>
                        </select>

                        <!-- Age Range -->
                        <div class="flex items-center gap-2 mb-5">
                            <input type="number" name="age_from" placeholder="Age From" min="18" max="80" value="<?= htmlspecialchars($_GET['age_from'] ?? '') ?>" class="w-1/2 border border-gray-300 rounded-md p-2.5 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <span class="text-gray-400 text-sm">to</span>
                            <input type="number" name="age_to" placeholder="Age To" min="18" max="80" value="<?= htmlspecialchars($_GET['age_to'] ?? '') ?>" class="w-1/2 border border-gray-300 rounded-md p-2.5 text-sm text-gray-700 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <!-- Search Button -->
                        <button type="submit" class="w-full bg-primary text-white font-semibold py-2.5 rounded-md hover:bg-opacity-90 transition shadow-sm">
                            Search
                        </button>
                        
                        <!-- Reset Link -->
                        <?php if (!empty($_GET['city']) || !empty($_GET['education']) || !empty($_GET['manglik']) || !empty($_GET['marital']) || !empty($_GET['occupation']) || !empty($_GET['age_from']) || !empty($_GET['age_to'])): ?>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>" class="block text-center text-sm text-gray-500 hover:text-primary mt-3 transition">
                            <i class="fas fa-times-circle mr-1"></i> Clear All Filters
                        </a>
                        <?php endif; ?>
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
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&education=Doctorate" class="block text-gray-600 hover:text-primary hover:underline transition">All Doctors</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&education=LLB" class="block text-gray-600 hover:text-primary hover:underline transition">All LLB, LLM</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&education=Engineer" class="block text-gray-600 hover:text-primary hover:underline transition">All Engineers</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&education=MBA" class="block text-gray-600 hover:text-primary hover:underline transition">All MBA, MCA</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&education=CA" class="block text-gray-600 hover:text-primary hover:underline transition">All CA, CS, ICWAI</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&manglik=yes" class="block text-gray-600 hover:text-primary hover:underline transition">All Manglik</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&occupation=Service" class="block text-gray-600 hover:text-primary hover:underline transition">All Service</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&occupation=Business" class="block text-gray-600 hover:text-primary hover:underline transition">All Business</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&occupation=Profession" class="block text-gray-600 hover:text-primary hover:underline transition">All Profession</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&marital=Widow" class="block text-gray-600 hover:text-primary hover:underline transition">All Widow</a>
                        <a href="profiles.php?gender=<?= urlencode($genderFilter) ?>&marital=Divorce" class="block text-gray-600 hover:text-primary hover:underline transition">All Divorcee</a>
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
                            $img = (!empty($p['profile_photo']) && file_exists($p['profile_photo'])) ? $p['profile_photo'] : 'https://ui-avatars.com/api/?name='.urlencode($p['full_name']).'&background=random';
                        ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition flex flex-col sm:flex-row">
                            <div class="w-full sm:w-1/4 md:w-56 relative bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0" style="min-height: 280px;">
                                <?php if ($is_approved): ?>
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Profile" class="w-full h-full object-cover object-top absolute inset-0">
                                <?php else: ?>
                                    <!-- Blurred photo with lock overlay for guests / pending users -->
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Profile" class="w-full h-full object-cover object-top absolute inset-0 blur-lg opacity-30 select-none pointer-events-none">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500 p-4 text-center z-10">
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
                            <a href="?page=<?= $page - 1 ?>&<?= $filterQueryString ?>" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 rounded hover:bg-gray-50 transition"><i class="fas fa-chevron-left text-xs"></i></a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>&<?= $filterQueryString ?>" class="px-4 py-2 <?= $i === $page ? 'bg-primary text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50' ?> rounded font-medium transition"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&<?= $filterQueryString ?>" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 rounded hover:bg-gray-50 transition"><i class="fas fa-chevron-right text-xs"></i></a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
