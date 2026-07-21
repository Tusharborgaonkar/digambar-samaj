<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Payment Screenshot Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_payment'])) {
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/receipts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['payment_screenshot']['name']);
        $ext = strtolower($fileInfo['extension']);
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (in_array($ext, $allowed)) {
            $newFileName = 'payment_' . $user_id . '_' . time() . '.' . $ext;
            $dest = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $dest)) {
                $stmt = $pdo->prepare("UPDATE users SET payment_screenshot = ? WHERE id = ?");
                $stmt->execute([$dest, $user_id]);
                echo "<script>alert('Payment screenshot uploaded successfully!'); window.location.href='my-profile.php#payment-upload';</script>";
                exit;
            } else {
                echo "<script>alert('Failed to move uploaded file.');</script>";
            }
        } else {
            echo "<script>alert('Invalid file type. Only JPG, PNG, and PDF are allowed.');</script>";
        }
    } else {
        echo "<script>alert('Please select a valid file.');</script>";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

// Calculate Age
$age = 'N/A';
if (!empty($user['birth_date'])) {
    $bday = new DateTime($user['birth_date']);
    $today = new DateTime('today');
    $age = $bday->diff($today)->y;
}

$profile_img = (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) ? 'image.php?file='.urlencode($user['profile_photo']) : 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']).'&background=random';
?>

<section class="py-12 md:py-16 bg-light">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8" data-aos="fade-up">
                <h1 class="text-3xl md:text-4xl font-bold text-dark">My Profile</h1>
                <div class="mt-4 md:mt-0 flex gap-4 flex-wrap">
                    <a href="edit-profile.php" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-opacity-90 shadow-md transition font-medium flex items-center gap-2">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>

                    <a href="login.php?logout=1" class="bg-red-50 border border-red-200 text-red-600 px-6 py-2.5 rounded-lg hover:bg-red-100 transition font-medium flex items-center gap-2 shadow-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Left Column (Profile Summary) -->
                <div class="w-full lg:w-1/3 space-y-8" data-aos="fade-up" data-aos-delay="100">
                    <!-- Profile Card -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                        <div class="bg-primary h-32 relative">
                            <!-- Optional cover pattern/image could go here -->
                        </div>
                        <div class="px-6 pb-6 relative">
                            <div class="w-28 h-28 rounded-full border-4 border-white bg-gray-100 mx-auto -mt-14 flex items-center justify-center shadow-md overflow-hidden relative z-10">
                                <img src="<?= htmlspecialchars($profile_img) ?>" alt="Profile Photo" class="w-full h-full object-cover">
                            </div>
                            <div class="text-center mt-4">
                                <h2 class="text-2xl font-bold text-dark"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></h2>
                                <p class="text-gray-500 font-medium"><?= htmlspecialchars($user['occupation'] ?? 'N/A') ?></p>
                                <div class="flex items-center justify-center gap-2 mt-2 text-gray-600 text-sm">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    <span><?= htmlspecialchars($user['native_place'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                            <hr class="my-6 border-gray-100">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-birthday-cake w-4 text-center text-gray-400"></i> Age / Height</span>
                                    <span class="font-medium text-dark"><?= $age ?> Yrs / <?= htmlspecialchars($user['height'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-om w-4 text-center text-gray-400"></i> Religion</span>
                                    <span class="font-medium text-dark">Digambar Jain</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-users w-4 text-center text-gray-400"></i> Gotra</span>
                                    <span class="font-medium text-dark"><?= htmlspecialchars($user['mama_gotra'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-id-card w-4 text-center text-gray-400"></i> Profile ID</span>
                                    <span class="font-medium text-dark"><?= htmlspecialchars($user['profile_id'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-dark mb-4 flex items-center gap-2">
                            <i class="fas fa-address-book text-primary"></i> Contact Details
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Mobile Number</p>
                                    <p class="font-medium text-dark"><?= htmlspecialchars($user['mobile'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Email Address</p>
                                    <p class="font-medium text-dark"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Native Place</p>
                                    <p class="font-medium text-dark text-sm leading-snug"><?= nl2br(htmlspecialchars($user['native_place'] ?? 'N/A')) ?></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Current Address</p>
                                    <p class="font-medium text-dark text-sm leading-snug"><?= nl2br(htmlspecialchars($user['current_address'] ?? 'N/A')) ?></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Permanent Address</p>
                                    <p class="font-medium text-dark text-sm leading-snug"><?= nl2br(htmlspecialchars($user['permanent_address'] ?? 'N/A')) ?> <?= !empty($user['pin_code']) ? ' - ' . htmlspecialchars($user['pin_code']) : '' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Screenshot Upload Card -->
                    <div id="payment-upload" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 mt-8">
                        <h3 class="text-lg font-bold text-dark mb-4 flex items-center gap-2">
                            <i class="fas fa-file-invoice-dollar text-primary"></i> Payment Screenshot
                        </h3>
                        <?php if (!empty($user['payment_screenshot']) && file_exists($user['payment_screenshot'])): ?>
                            <div class="mb-4">
                                <p class="text-sm text-green-600 font-medium mb-2"><i class="fas fa-check-circle"></i> Screenshot Uploaded</p>
                                <?php 
                                $ext = strtolower(pathinfo($user['payment_screenshot'], PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png'])): 
                                ?>
                                    <img src="image.php?file=<?= urlencode($user['payment_screenshot']) ?>" alt="Payment Receipt" class="w-full h-auto rounded border border-gray-200">
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($user['payment_screenshot']) ?>" target="_blank" class="text-blue-600 underline text-sm"><i class="fas fa-external-link-alt"></i> View Uploaded PDF/File</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="my-profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Screenshot</label>
                                <input type="file" name="payment_screenshot" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-primary hover:file:bg-blue-100" required>
                                <p class="text-xs text-gray-500 mt-1">Allowed formats: JPG, PNG, PDF</p>
                            </div>
                            <button type="submit" name="upload_payment" class="w-full bg-primary text-white py-2 rounded-lg font-medium hover:bg-opacity-90 transition">
                                Save Profile
                            </button>
                        </form>
                    </div>

                </div>

                <!-- Right Column (Detailed Info) -->
                <div class="w-full lg:w-2/3 space-y-8" data-aos="fade-up" data-aos-delay="200">
                    
                    <!-- Personal & Physical Details -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-info-circle"></i> Personal Details
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-6">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Date of Birth</p>
                                <p class="font-medium text-dark"><?= !empty($user['birth_date']) ? date('d M Y', strtotime($user['birth_date'])) : 'N/A' ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Time of Birth</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['birth_time'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Place of Birth</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['birth_place'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Manglik</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['manglik'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Weight</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['weight'] ?? 'N/A') ?> kg</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Marital Status</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['marital_status'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Mama Gotra</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['mama_gotra'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Handicapped</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['handicapped'] ?? 'No') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Education & Career -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-graduation-cap"></i> Education & Career
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Highest Education</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['higher_education'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Occupation</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['occupation'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Monthly Income</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['monthly_income'] ?? 'N/A') ?></p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Partner Preferences</p>
                                <p class="font-medium text-dark"><?= nl2br(htmlspecialchars($user['partner_preference'] ?? 'N/A')) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Family Details -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-home"></i> Family Details
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Father's Name</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['father_name'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Father's Occupation</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['father_occupation'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Mother's Name</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['mother_name'] ?? 'N/A') ?></p>
                            </div>
                            
                            <div class="sm:col-span-2 mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">Siblings Info</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-5 rounded-xl border border-gray-100">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-primary"><?= htmlspecialchars($user['brothers_married'] ?? '0') ?></p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Brothers<br>Married</p>
                                    </div>
                                    <div class="text-center border-l border-gray-200">
                                        <p class="text-2xl font-bold text-primary"><?= htmlspecialchars($user['brothers_unmarried'] ?? '0') ?></p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Brothers<br>Unmarried</p>
                                    </div>
                                    <div class="text-center md:border-l border-gray-200 pt-4 md:pt-0 border-t md:border-t-0 mt-2 md:mt-0">
                                        <p class="text-2xl font-bold text-primary"><?= htmlspecialchars($user['sisters_married'] ?? '0') ?></p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Sisters<br>Married</p>
                                    </div>
                                    <div class="text-center border-l border-gray-200 pt-4 md:pt-0 border-t md:border-t-0 mt-2 md:mt-0">
                                        <p class="text-2xl font-bold text-primary"><?= htmlspecialchars($user['sisters_unmarried'] ?? '0') ?></p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Sisters<br>Unmarried</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mandir Verification & References -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-gopuram text-primary"></i> Mandir Verification Details
                            </h3>
                            <span class="bg-<?= $user['status'] === 'approved' ? 'green' : 'yellow' ?>-100 text-<?= $user['status'] === 'approved' ? 'green' : 'yellow' ?>-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center gap-1">
                                <i class="fas fa-<?= $user['status'] === 'approved' ? 'check-circle' : 'clock' ?>"></i> <?= ucfirst($user['status']) ?>
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 mb-6">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Subcast (उपजाति)</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['subcast'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Registered Mandir (मंदिर)</p>
                                <p class="font-medium text-dark"><?= htmlspecialchars($user['mandir_name'] ?? ($user['mandir'] ?? 'N/A')) ?> <?= !empty($user['custom_mandir']) ? ' - ' . htmlspecialchars($user['custom_mandir']) : '' ?></p>
                            </div>
                        </div>

                        <h4 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">Reference Persons</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-xs font-bold text-primary uppercase mb-2">Reference Person 1</p>
                                <div class="space-y-1 text-sm text-gray-700">
                                    <p><span class="text-gray-500">Name:</span> <span class="font-medium text-dark"><?= htmlspecialchars($user['ref1_name'] ?? 'N/A') ?></span></p>
                                    <p><span class="text-gray-500">Mobile:</span> <span class="font-medium text-dark"><?= htmlspecialchars($user['ref1_mobile'] ?? 'N/A') ?></span></p>
                                    <p><span class="text-gray-500">Relation:</span> <span class="font-medium text-dark"><?= htmlspecialchars($user['ref1_relation'] ?? 'N/A') ?></span></p>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-xs font-bold text-primary uppercase mb-2">Reference Person 2</p>
                                <div class="space-y-1 text-sm text-gray-700">
                                    <p><span class="text-gray-500">Name:</span> <span class="font-medium text-dark"><?= htmlspecialchars($user['ref2_name'] ?? 'N/A') ?></span></p>
                                    <p><span class="text-gray-500">Mobile:</span> <span class="font-medium text-dark"><?= htmlspecialchars($user['ref2_mobile'] ?? 'N/A') ?></span></p>
                                    <p><span class="text-gray-500">Relation:</span> <span class="font-medium text-dark"><?= htmlspecialchars($user['ref2_relation'] ?? 'N/A') ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>