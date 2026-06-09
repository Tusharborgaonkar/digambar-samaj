<?php
require_once '../includes/db.php';

// Check if admin is logged in (assuming your admin header handles session checks, but let's be safe)
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    include 'includes/header.php';
    include 'includes/sidebar.php';
    echo "<div class='p-10 text-center'><h2 class='text-2xl text-red-500 font-bold'>Profile not found</h2></div>";
    include 'includes/footer.php';
    exit;
}

$fullName = htmlspecialchars($member['full_name'] ?? '');
$memberId = htmlspecialchars($member['profile_id'] ?? '');
// Ensure we have correct path from admin folder
$photo_path = (!empty($member['profile_photo'])) ? '../' . $member['profile_photo'] : '';
$photo = ($photo_path && file_exists($photo_path)) ? htmlspecialchars($photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($member['full_name'] ?? 'User');

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
include 'includes/sidebar.php';
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Member Details: <?= $fullName ?></h3>
        <p class="text-gray-500 text-sm">View complete profile information.</p>
    </div>
    
    <div class="flex gap-2">
        <a href="members.php" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition shadow-sm flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Members
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
    <!-- Profile Header -->
    <div class="flex flex-col md:flex-row">
        <div class="w-full md:w-1/4 relative bg-gray-100 flex items-center justify-center p-4">
            <img src="<?= $photo ?>" alt="Profile Image" class="w-full h-auto object-cover rounded shadow-sm border border-gray-200">
        </div>
        
        <div class="w-full md:w-3/4 p-6 flex flex-col justify-center">
            <div class="flex justify-between items-start mb-2">
                <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= $fullName ?> <span class="text-lg text-primary font-medium ml-2">[MID: <?= $memberId ?>]</span></h1>
                
                <?php if($member['status'] === 'approved'): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Approved</span>
                <?php elseif($member['status'] === 'rejected'): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Rejected</span>
                <?php elseif($member['status'] === 'blocked'): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Blocked</span>
                <?php else: ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Pending</span>
                <?php endif; ?>
            </div>
            
            <p class="text-gray-600 mb-4"><i class="fas fa-map-marker-alt text-primary mr-2"></i> <?= $location ?></p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-gray-700 font-medium bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="flex items-center gap-2"><i class="far fa-calendar-alt text-primary w-5 text-center"></i> <?= $age ?>, <?= $heightDisplay ?></div>
                <div class="flex items-center gap-2"><i class="fas fa-graduation-cap text-primary w-5 text-center"></i> <?= $education ?></div>
                <div class="flex items-center gap-2"><i class="fas fa-briefcase text-primary w-5 text-center"></i> <?= $occupation ?></div>
                <div class="flex items-center gap-2"><i class="fas fa-om text-primary w-5 text-center"></i> Digambar Jain</div>
                <div class="flex items-center gap-2"><i class="fas fa-language text-primary w-5 text-center"></i> <?= $language ?></div>
                <div class="flex items-center gap-2"><i class="fas fa-ring text-primary w-5 text-center"></i> <?= $maritalStatus ?></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Personal Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 flex items-center"><i class="far fa-id-card text-primary mr-3"></i> Personal Information</h3>
        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
            <div><span class="block text-gray-500 mb-1">Name</span><span class="text-gray-800 font-medium"><?= $fullName ?></span></div>
            <div><span class="block text-gray-500 mb-1">Date of Birth</span><span class="text-gray-800 font-medium"><?= !empty($member['birth_date']) ? date('d M Y', strtotime($member['birth_date'])) : 'N/A' ?></span></div>
            <div><span class="block text-gray-500 mb-1">Height</span><span class="text-gray-800 font-medium"><?= $heightDisplay ?></span></div>
            <div><span class="block text-gray-500 mb-1">Weight</span><span class="text-gray-800 font-medium"><?= !empty($member['weight']) ? htmlspecialchars($member['weight']) . ' kg' : 'N/A' ?></span></div>
            <div><span class="block text-gray-500 mb-1">Gender</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['gender'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Birth Place</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['birth_place'] ?? 'N/A') ?></span></div>
        </div>
    </div>

    <!-- Religious Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 flex items-center"><i class="fas fa-om text-primary mr-3"></i> Religious Background</h3>
        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
            <div><span class="block text-gray-500 mb-1">Gotra</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['gotra'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Mama Gotra</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['mama_gotra'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Manglik</span><span class="text-gray-800 font-medium"><?= ucfirst(htmlspecialchars($member['manglik'] ?? 'N/A')) ?></span></div>
            <div><span class="block text-gray-500 mb-1">Time of Birth</span><span class="text-gray-800 font-medium"><?= !empty($member['birth_time']) ? date('h:i A', strtotime($member['birth_time'])) : 'N/A' ?></span></div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 flex items-center"><i class="fas fa-address-card text-primary mr-3"></i> Contact & Address</h3>
        <div class="grid grid-cols-1 gap-y-4 text-sm">
            <div class="grid grid-cols-2 gap-x-6">
                <div><span class="block text-gray-500 mb-1">Email Address</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['email'] ?? 'N/A') ?></span></div>
                <div><span class="block text-gray-500 mb-1">Mobile Number</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['mobile'] ?? 'N/A') ?></span></div>
            </div>
            <div><span class="block text-gray-500 mb-1">Current Address</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['current_address'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Permanent Address</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['permanent_address'] ?? 'N/A') ?> <?= htmlspecialchars($member['pin_code'] ?? '') ?></span></div>
        </div>
    </div>

    <!-- Family Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 flex items-center"><i class="fas fa-users text-primary mr-3"></i> Family Details</h3>
        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
            <div><span class="block text-gray-500 mb-1">Father's Name</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['father_name'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Father's Occupation</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['father_occupation'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Mother's Name</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['mother_name'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Mother's Occupation</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['mother_occupation'] ?? 'N/A') ?></span></div>
            <div><span class="block text-gray-500 mb-1">Brothers</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['brothers'] ?? '0') ?> (Married: <?= htmlspecialchars($member['brothers_married'] ?? '0') ?>)</span></div>
            <div><span class="block text-gray-500 mb-1">Sisters</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($member['sisters'] ?? '0') ?> (Married: <?= htmlspecialchars($member['sisters_married'] ?? '0') ?>)</span></div>
        </div>
    </div>
    
    <!-- References & Partner Preferences -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 flex items-center"><i class="fas fa-address-book text-primary mr-3"></i> More Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <span class="block text-gray-500 mb-1 font-semibold">References</span>
                <p class="mb-2 text-gray-800"><strong>Ref 1:</strong> <?= htmlspecialchars($member['ref1_name'] ?? 'N/A') ?> (<?= htmlspecialchars($member['ref1_relation'] ?? '') ?>) - <?= htmlspecialchars($member['ref1_mobile'] ?? '') ?></p>
                <p class="mb-4 text-gray-800"><strong>Ref 2:</strong> <?= htmlspecialchars($member['ref2_name'] ?? 'N/A') ?> (<?= htmlspecialchars($member['ref2_relation'] ?? '') ?>) - <?= htmlspecialchars($member['ref2_mobile'] ?? '') ?></p>
                
                <span class="block text-gray-500 mb-1 font-semibold">Mandir / Community</span>
                <p class="text-gray-800"><?= htmlspecialchars($member['mandir'] ?? 'N/A') ?> <?= !empty($member['custom_mandir']) ? ' - ' . htmlspecialchars($member['custom_mandir']) : '' ?></p>
            </div>
            <div>
                <span class="block text-gray-500 mb-1 font-semibold">Hobbies & Interests</span>
                <p class="mb-4 text-gray-800"><?= nl2br(htmlspecialchars($member['hobbies'] ?? 'Not specified')) ?></p>
                
                <span class="block text-gray-500 mb-1 font-semibold">Partner Preferences</span>
                <p class="text-gray-800"><?= nl2br(htmlspecialchars($member['partner_preference'] ?? 'Not specified by the member.')) ?></p>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
