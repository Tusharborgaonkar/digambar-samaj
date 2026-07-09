<?php
session_start();
require_once 'includes/db.php';

$is_user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if (!$is_user_logged_in && !$is_admin_logged_in) {
    header('Location: login.php');
    exit;
}

// Check user status
$is_approved = false;
if ($is_admin_logged_in) {
    $is_approved = true;
} else if ($is_user_logged_in) {
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_status = $stmt->fetchColumn();
    if ($user_status === 'approved') {
        $is_approved = true;
    }
}

$is_liked = false;
if ($is_user_logged_in && isset($_GET['id'])) {
    $stmtLike = $pdo->prepare("SELECT 1 FROM user_likes WHERE user_id = ? AND liked_user_id = ?");
    $stmtLike->execute([$_SESSION['user_id'], (int)$_GET['id']]);
    if ($stmtLike->fetchColumn()) {
        $is_liked = true;
    }
}

if (!$is_approved) {
    include 'includes/header.php';
    echo '<div class="bg-gray-50 py-8 min-h-screen"><div class="container mx-auto px-4">';
    echo '<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-red-200 p-8 text-center mt-10">';
    echo '<div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">';
    echo '<i class="fas fa-lock text-3xl text-red-500"></i></div>';
    echo '<h2 class="text-2xl font-bold text-gray-800 mb-4">Access Restricted</h2>';
    echo '<p class="text-gray-600 mb-6">Your profile is currently under review by the administrator. Once your profile is approved, you will be able to view other profiles and their photos.</p>';
    echo '</div></div></div>';
    include 'includes/footer.php';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    include 'includes/header.php';
    echo "<div class='container mx-auto p-10 text-center'><h2 class='text-2xl text-red-500 font-bold'>Invalid Profile ID</h2></div>";
    include 'includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    include 'includes/header.php';
    echo "<div class='container mx-auto p-10 text-center'><h2 class='text-2xl text-red-500 font-bold'>Profile not found</h2></div>";
    include 'includes/footer.php';
    exit;
}

$fullName = htmlspecialchars($member['full_name'] ?? '');
$memberId = htmlspecialchars($member['profile_id'] ?? '');
$photo = (!empty($member['profile_photo']) && file_exists($member['profile_photo'])) ? 'image.php?file='.urlencode($member['profile_photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($member['full_name'] ?? 'User');

$age = 'N/A';
if (!empty($member['birth_date'])) {
    $dob = new DateTime($member['birth_date']);
    $now = new DateTime();
    $age = $now->diff($dob)->y . ' Years';
}

$heightDisplay = htmlspecialchars($member['height'] ?? 'N/A');
$maritalStatus = htmlspecialchars($member['marital_status'] ?? 'N/A');
$location = htmlspecialchars($member['native_place'] ?? 'N/A');
$education = htmlspecialchars($member['higher_education'] ?? 'N/A');
$occupation = htmlspecialchars($member['occupation'] ?? 'N/A');
$language = htmlspecialchars($member['languages'] ?? 'N/A');

include 'includes/header.php';
?>

<div class="bg-gray-50 py-10 min-h-screen">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2">
            <a href="index.php" class="hover:text-primary transition">Home</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="profiles.php" class="hover:text-primary transition">Search Profiles</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-dark font-medium"><?= $fullName ?> [MID: <?= $memberId ?>]</span>
        </nav>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-8">
            <!-- Profile Header -->
            <div class="flex flex-col md:flex-row">
                <div class="w-full md:w-1/3 relative h-96 md:h-auto bg-gray-100 flex items-center justify-center overflow-hidden">
                    <img src="<?= $photo ?>" alt="Profile Image" class="w-full h-full object-cover">
                </div>
                
                <div class="w-full md:w-2/3 p-6 md:p-10 flex flex-col justify-center">
                    <div class="flex justify-between items-start mb-2">
                        <h1 class="text-3xl font-bold text-dark mb-2"><?= $fullName ?> <span class="text-lg text-primary font-medium ml-2">[MID: <?= $memberId ?>]</span></h1>
                        <div class="flex gap-2">
                            <?php if ($is_user_logged_in): ?>
                            <button class="like-btn border border-primary px-3 py-1.5 rounded hover:bg-primary hover:text-white transition shadow-sm <?= $is_liked ? 'bg-primary text-white' : 'text-primary' ?>" data-id="<?= $id ?>" title="<?= $is_liked ? 'Unlike' : 'Like' ?>">
                                <i class="<?= $is_liked ? 'fas' : 'far' ?> fa-heart"></i>
                            </button>
                            <?php endif; ?>
                            <button onclick="downloadPDF()" class="border border-green-600 text-green-600 px-3 py-1.5 rounded hover:bg-green-600 hover:text-white transition shadow-sm" title="Download PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 mb-6 text-lg"><i class="fas fa-map-marker-alt text-primary mr-2"></i> <?= $location ?></p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700 font-medium mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="flex items-center gap-3"><i class="far fa-calendar-alt text-primary w-5 text-center"></i> <?= $age ?>, <?= $heightDisplay ?></div>
                        <div class="flex items-center gap-3"><i class="fas fa-graduation-cap text-primary w-5 text-center"></i> <?= $education ?></div>
                        <div class="flex items-center gap-3"><i class="fas fa-briefcase text-primary w-5 text-center"></i> <?= $occupation ?></div>
                        <div class="flex items-center gap-3"><i class="fas fa-om text-primary w-5 text-center"></i> Digambar Jain</div>
                        <div class="flex items-center gap-3"><i class="fas fa-language text-primary w-5 text-center"></i> <?= $language ?></div>
                        <div class="flex items-center gap-3"><i class="fas fa-ring text-primary w-5 text-center"></i> <?= $maritalStatus ?></div>
                    </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- About Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="far fa-user-circle text-primary mr-3 text-2xl"></i> About <?= explode(' ', $fullName)[0] ?></h3>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Hobbies & Interests:</strong> <?= nl2br(htmlspecialchars($member['hobbies'] ?? 'Not specified')) ?>
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        <strong>Physical Challenges/Disabilities:</strong> <?= htmlspecialchars($member['handicapped'] ?? 'None') ?>
                    </p>
                </div>

                <!-- Personal Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="far fa-id-card text-primary mr-3 text-2xl"></i> Personal Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Name</span>
                            <span class="text-dark font-semibold"><?= $fullName ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Date of Birth</span>
                            <span class="text-dark font-semibold"><?= !empty($member['birth_date']) ? date('d M Y', strtotime($member['birth_date'])) : 'N/A' ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Marital Status</span>
                            <span class="text-dark font-semibold"><?= $maritalStatus ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Height</span>
                            <span class="text-dark font-semibold"><?= $heightDisplay ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Weight</span>
                            <span class="text-dark font-semibold"><?= !empty($member['weight']) ? htmlspecialchars($member['weight']) . ' kg' : 'N/A' ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Gender</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['gender'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Native Place</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['native_place'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Birth Place</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['birth_place'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Religious Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-om text-primary mr-3 text-2xl"></i> Religious & Astro Background</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Religion</span>
                            <span class="text-dark font-semibold">Jain Digambar</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Gotra</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['gotra'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mama Gotra</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['mama_gotra'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Manglik Status</span>
                            <span class="text-dark font-semibold <?= (strtolower($member['manglik'] ?? '') === 'no') ? 'text-green-600' : 'text-red-600' ?>"><?= ucfirst(htmlspecialchars($member['manglik'] ?? 'N/A')) ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Time of Birth</span>
                            <span class="text-dark font-semibold"><?= !empty($member['birth_time']) ? date('h:i A', strtotime($member['birth_time'])) : 'N/A' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Education & Career -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-user-graduate text-primary mr-3 text-2xl"></i> Education & Career</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Highest Education</span>
                            <span class="text-dark font-semibold"><?= $education ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Company Name</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['company_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Occupation</span>
                            <span class="text-dark font-semibold"><?= $occupation ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Designation</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['designation'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Monthly Income</span>
                            <span class="text-dark font-semibold">₹ <?= number_format((float)($member['monthly_income'] ?? 0)) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Family Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-users text-primary mr-3 text-2xl"></i> Family Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Father's Name</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['father_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Father's Occupation</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['father_occupation'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mother's Name</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['mother_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mother's Occupation</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['mother_occupation'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Brothers</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['brothers'] ?? '0') ?> (Married: <?= htmlspecialchars($member['brothers_married'] ?? '0') ?>, Unmarried: <?= htmlspecialchars($member['brothers_unmarried'] ?? '0') ?>)</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Sisters</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['sisters'] ?? '0') ?> (Married: <?= htmlspecialchars($member['sisters_married'] ?? '0') ?>, Unmarried: <?= htmlspecialchars($member['sisters_unmarried'] ?? '0') ?>)</span>
                        </div>
                    </div>
                </div>

                <!-- Reference Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-address-book text-primary mr-3 text-2xl"></i> References & Community</h3>
                    
                    <div class="mb-6">
                        <span class="block text-sm text-gray-500 mb-1">Mandir / Community</span>
                        <span class="text-dark font-semibold"><?= htmlspecialchars($member['mandir'] ?? 'N/A') ?> <?= !empty($member['custom_mandir']) ? ' - ' . htmlspecialchars($member['custom_mandir']) : '' ?></span>
                    </div>

                    <h4 class="font-bold text-gray-700 mb-3 border-b border-gray-100 pb-2">Reference 1</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-4 gap-x-8 mb-6">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Name</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['ref1_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mobile</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['ref1_mobile'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Relation</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['ref1_relation'] ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <h4 class="font-bold text-gray-700 mb-3 border-b border-gray-100 pb-2">Reference 2</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-4 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Name</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['ref2_name'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mobile</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['ref2_mobile'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Relation</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['ref2_relation'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-address-card text-primary mr-3 text-2xl"></i> Contact Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mobile Number</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['mobile'] ?? 'N/A') ?></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Email Address</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['email'] ?? 'N/A') ?></span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-sm text-gray-500 mb-1">Permanent Address</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['permanent_address'] ?? 'N/A') ?> <?= htmlspecialchars($member['pin_code'] ?? '') ?></span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-sm text-gray-500 mb-1">Current Address</span>
                            <span class="text-dark font-semibold"><?= htmlspecialchars($member['current_address'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Partner Preferences -->
                <div class="bg-light rounded-xl shadow-sm border border-primary/20 p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-primary border-b border-primary/20 pb-3 mb-4"><i class="fas fa-heart mr-2"></i> Partner Preferences</h3>
                    
                    <p class="text-sm text-gray-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($member['partner_preference'] ?? 'Not specified by the member.')) ?>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- html2pdf for generating PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
    // We can target the main content block to download
    const element = document.querySelector('.bg-gray-50 .container');
    const opt = {
      margin:       10,
      filename:     'Profile_<?= $memberId ?>.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}

// Like Button logic
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        const icon = this.querySelector('i');
        const isLiked = this.classList.contains('bg-primary');
        
        fetch('api_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'liked_user_id=' + userId + '&action=' + (isLiked ? 'unlike' : 'like')
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'liked') {
                    this.classList.add('bg-primary', 'text-white');
                    this.classList.remove('text-primary');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    this.setAttribute('title', 'Unlike');
                } else {
                    this.classList.remove('bg-primary', 'text-white');
                    this.classList.add('text-primary');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    this.setAttribute('title', 'Like');
                }
            } else {
                Swal.fire('Error', data.message || 'Something went wrong', 'error');
            }
        });
    });
});
</script>
