<?php
// Prevent caching to ensure the latest data is always fetched from the database on refresh
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
include 'includes/db.php';

// Access Check for Stage 2
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($current_user) {
    $stmtCustomData = $pdo->prepare("SELECT f.field_key, cd.field_value FROM user_custom_data cd JOIN registration_fields f ON cd.field_id = f.id WHERE cd.user_id = ?");
    $stmtCustomData->execute([$user_id]);
    $custom_data = $stmtCustomData->fetchAll(PDO::FETCH_KEY_PAIR);
    if ($custom_data) {
        $current_user = array_merge($current_user, $custom_data);
    }
}

// Fetch site settings for QR code
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
$payment_qr_code = $settings['payment_qr_code'] ?? 'assets/images/qr_code.jpg';

$full_name = '';

$is_edit = ($current_user !== false && $current_user['status'] === 'approved');
$is_ajax = isset($_POST['ajax_save']);
$is_final_submit = ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_ajax);
$new_status = $is_ajax ? $current_user['status'] : ($is_edit ? $current_user['status'] : ($is_final_submit ? 'pending' : $current_user['status']));
$current_step = $current_user['registration_step'] ?? 1;

if (!$current_user || !in_array($current_user['status'], ['account_approved', 'approved'])) {
    if ($current_user && ($current_user['status'] === 'account_pending' || $current_user['status'] === 'pending')) {
        header("Location: waiting-approval.php");
        exit;
    }

    header("Location: index.php");
    exit;
}

$success = '';
$error = '';

// Fetch custom dynamic fields to display and process
$stmtCustom = $pdo->query("SELECT * FROM registration_fields WHERE is_custom = 1 AND is_visible = 1 ORDER BY sort_order ASC, id ASC");
$customFields = $stmtCustom->fetchAll();

$customFieldsByGroup = [];
foreach ($customFields as $field) {
    $group = $field['field_group'] ?: 'Additional Information';
    if ($group === 'Custom Fields') $group = 'Additional Information';
    $customFieldsByGroup[$group][] = $field;
}

function renderCustomFieldHTML($field) {
    $req = $field['is_required'] ? '*' : '';
    $reqAttr = $field['is_required'] ? 'required' : '';
    $label = htmlspecialchars($field['field_label']);
    $key = htmlspecialchars($field['field_key']);
    $type = htmlspecialchars($field['field_type']);
    
    $html = '<div><label class="block text-gray-700 font-medium mb-2">' . $label . ' ' . $req . '</label>';
    if ($type === 'textarea') {
        $html .= '<textarea name="' . $key . '" ' . $reqAttr . ' rows="2" class="w-full border rounded-lg px-4 py-2"></textarea>';
    } elseif ($type === 'dropdown') {
        $html .= '<select name="' . $key . '" ' . $reqAttr . ' class="w-full border rounded-lg px-4 py-2"><option value="">Select ' . $label . '</option>';
        $options = explode(',', $field['field_options']);
        foreach ($options as $opt) {
            $opt = trim($opt);
            if ($opt) $html .= '<option value="' . htmlspecialchars($opt) . '">' . htmlspecialchars($opt) . '</option>';
        }
        $html .= '</select>';
    } elseif ($type === 'file') {
        $html .= '<input type="file" name="' . $key . '" ' . $reqAttr . ' class="w-full border rounded-lg px-4 py-2">';
    } else {
        $html .= '<input type="' . $type . '" name="' . $key . '" ' . $reqAttr . ' class="w-full border rounded-lg px-4 py-2">';
    }
    $html .= '</div>';
    return $html;
}


// Fetch core fields visibility settings
$stmtCore = $pdo->query("SELECT field_key, is_visible, is_required, field_options FROM registration_fields WHERE is_custom = 0");
$coreFieldsSettings = [];
while ($row = $stmtCore->fetch(PDO::FETCH_ASSOC)) {
    $coreFieldsSettings[$row['field_key']] = [
        'is_visible' => $row['is_visible'],
        'is_required' => $row['is_required'],
        'field_options' => $row['field_options']
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data (excluding fields already collected in Stage 1)
    $birth_date = $_POST['birth_date'] ?? '';
    $birth_time = $_POST['birth_time'] ?? '';
    $birth_place = htmlspecialchars($_POST['birth_place'] ?? '');
    $native = htmlspecialchars($_POST['native'] ?? '');
    $gotra = htmlspecialchars($_POST['gotra'] ?? '');
    $mama_gotra = htmlspecialchars($_POST['mama_gotra'] ?? '');
    $manglik = $_POST['manglik'] ?? '';
    $height = htmlspecialchars($_POST['height'] ?? '');
    $weight = htmlspecialchars($_POST['weight'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $full_name = htmlspecialchars($_POST['full_name'] ?? '');
    $mobile = htmlspecialchars($_POST['mobile'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $permanent_address = htmlspecialchars($_POST['permanent_address'] ?? '');
    $pin_code = htmlspecialchars($_POST['pin_code'] ?? '');
    $current_address = htmlspecialchars($_POST['current_address'] ?? '');
    // If "same as permanent" checkbox was checked, copy permanent address
    if (isset($_POST['same_as_permanent']) && $_POST['same_as_permanent'] === '1') {
        $current_address = $permanent_address;
    }
    $education = htmlspecialchars($_POST['education'] ?? '');
    $hobbies = htmlspecialchars($_POST['hobbies'] ?? '');
    $partner_preference = htmlspecialchars($_POST['partner_preference'] ?? '');
    $monthly_income = htmlspecialchars($_POST['annual_income'] ?? '');
    if ($monthly_income === '') $monthly_income = null;
    $marital_status = htmlspecialchars($_POST['marital_status'] ?? '');
    $handicapped = $_POST['handicapped'] ?? '';
    
    $registration_step = isset($_POST['registration_step']) ? (int)$_POST['registration_step'] : ($current_user['registration_step'] ?? 1);
    $is_digambar = $_POST['is_digambar'] ?? '';
    
    $languages_arr = $_POST['languages'] ?? [];
    if (in_array('Other', $languages_arr) && !empty($_POST['other_language'])) {
        $languages_arr[] = htmlspecialchars($_POST['other_language']);
    }
    $languages = !empty($languages_arr) ? implode(',', $languages_arr) : '';
    
    $occupation = $_POST['occupation'] ?? '';
    $occupation_other = '';
    if ($occupation === 'Other' && !empty($_POST['occupation_details'])) {
        $occupation_other = htmlspecialchars($_POST['occupation_details']);
    }
    $company_name = htmlspecialchars($_POST['company_name'] ?? '');
    $designation = htmlspecialchars($_POST['designation'] ?? '');
    $father_name = htmlspecialchars($_POST['father_name'] ?? '');
    $father_mobile = htmlspecialchars($_POST['father_mobile'] ?? '');
    $father_income = htmlspecialchars($_POST['father_income'] ?? '');
    if ($father_income === '') $father_income = null;
    $father_occupation = htmlspecialchars($_POST['father_occupation'] ?? '');
    $father_occupation_other = '';
    if ($father_occupation === 'Other' && !empty($_POST['father_occupation_details'])) {
        $father_occupation_other = htmlspecialchars($_POST['father_occupation_details']);
    }
    $mother_name = htmlspecialchars($_POST['mother_name'] ?? '');
    $mother_mobile = htmlspecialchars($_POST['mother_mobile'] ?? '');
    $mother_occupation = htmlspecialchars($_POST['mother_occupation'] ?? '');
    $mother_occupation_other = '';
    if ($mother_occupation === 'Other' && !empty($_POST['mother_occupation_details'])) {
        $mother_occupation_other = htmlspecialchars($_POST['mother_occupation_details']);
    }
    $brothers = (int)($_POST['brothers'] ?? 0);
    $brothers_married = (int)($_POST['brothers_married'] ?? 0);
    $brothers_unmarried = (int)($_POST['brothers_unmarried'] ?? 0);
    $sisters = (int)($_POST['sisters'] ?? 0);
    $sisters_married = (int)($_POST['sisters_married'] ?? 0);
    $sisters_unmarried = (int)($_POST['sisters_unmarried'] ?? 0);
    $cast = htmlspecialchars($_POST['cast'] ?? '');
    if ($cast === 'Other' && !empty($_POST['custom_cast'])) {
        $cast = htmlspecialchars($_POST['custom_cast']);
    }
    $subcast = htmlspecialchars($_POST['subcast'] ?? '');
    $custom_subcast = htmlspecialchars($_POST['custom_subcast'] ?? '');
    $mandir = htmlspecialchars($_POST['mandir'] ?? '');
    $custom_mandir = htmlspecialchars($_POST['custom_mandir'] ?? '');
    $ref1_name = htmlspecialchars($_POST['ref1_name'] ?? '');
    $ref1_mobile = htmlspecialchars($_POST['ref1_mobile'] ?? '');
    $ref1_relation = htmlspecialchars($_POST['ref1_relation'] ?? '');
    $ref2_name = htmlspecialchars($_POST['ref2_name'] ?? '');
    $ref2_mobile = htmlspecialchars($_POST['ref2_mobile'] ?? '');
    $ref2_relation = htmlspecialchars($_POST['ref2_relation'] ?? '');

    $filled_by = htmlspecialchars($_POST['filled_by'] ?? 'Candidate');
    $id_proof_type = htmlspecialchars($_POST['id_proof_type'] ?? '');
    $mandir_name = htmlspecialchars($_POST['mandir_name'] ?? '');
    $mandir_address = htmlspecialchars($_POST['mandir_address'] ?? '');
    $mandir_pincode = htmlspecialchars($_POST['mandir_pincode'] ?? '');

    $profile_photo_drive_url = htmlspecialchars($_POST['profile_photo_drive_url'] ?? '');
    $payment_proof_drive_url = htmlspecialchars($_POST['payment_proof_drive_url'] ?? '');

    $birth_time = null;
    if (isset($_POST['birth_time_hh'], $_POST['birth_time_mm'], $_POST['birth_time_ampm']) && $_POST['birth_time_hh'] !== '' && $_POST['birth_time_mm'] !== '' && $_POST['birth_time_ampm'] !== '') {
        $birth_time = str_pad($_POST['birth_time_hh'], 2, '0', STR_PAD_LEFT) . ':' . 
                      str_pad($_POST['birth_time_mm'], 2, '0', STR_PAD_LEFT) . ' ' . 
                      $_POST['birth_time_ampm'];
    }
    if ($birth_date === '') $birth_date = null;

    // PHP Validations
    if (!empty($birth_date)) {
        $bdate = new DateTime($birth_date);
        $today = new DateTime();
        $age = $today->diff($bdate)->y;
        if ($age < 18) {
            $error = "Candidate must be at least 18 years old to register.";
        }
    }
    if ($pin_code && !preg_match('/^[0-9]{4,6}$/', $pin_code)) {
        $error = "Pin code must be 4 to 6 digits.";
    } elseif ($monthly_income !== null && (!is_numeric($monthly_income) || $monthly_income < 0)) {
        $error = "Candidate annual income must be a valid positive amount.";
    } elseif ($father_income !== null && (!is_numeric($father_income) || $father_income < 0)) {
        $error = "Father income must be a valid positive amount.";
    } elseif ($mobile && !preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = "Please ensure all mobile numbers are exactly 10 digits.";
    } elseif ($father_mobile && !preg_match('/^[0-9]{10}$/', $father_mobile)) {
        $error = "Please ensure all mobile numbers are exactly 10 digits.";
    } elseif ($mother_mobile && !preg_match('/^[0-9]{10}$/', $mother_mobile)) {
        $error = "Please ensure all mobile numbers are exactly 10 digits.";
    } elseif ($ref1_mobile && !preg_match('/^[0-9]{10}$/', $ref1_mobile)) {
        $error = "Please ensure all mobile numbers are exactly 10 digits.";
    } elseif ($ref2_mobile && !preg_match('/^[0-9]{10}$/', $ref2_mobile)) {
        $error = "Please ensure all mobile numbers are exactly 10 digits.";
    } elseif ($ref1_mobile && $ref1_mobile === $ref2_mobile) {
        $error = "Reference 1 and 2 mobile numbers must be different.";
    }

    if ($error && isset($_POST['ajax_save'])) {
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }

    if (!$error) {
        // Handle File Uploads
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $photo = $current_user['profile_photo'] ?? '';
    $family_photo = $current_user['family_photo'] ?? '';
    $payment_screenshot = $current_user['payment_screenshot'] ?? '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = $upload_dir . time() . '_photo_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }
    if (isset($_FILES['family_photo']) && $_FILES['family_photo']['error'] === UPLOAD_ERR_OK) {
        $family_photo = $upload_dir . time() . '_family_' . basename($_FILES['family_photo']['name']);
        move_uploaded_file($_FILES['family_photo']['tmp_name'], $family_photo);
    }
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
        $payment_screenshot = $upload_dir . time() . '_payment_' . basename($_FILES['payment_screenshot']['name']);
        move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $payment_screenshot);
    }
    
    $id_proof_path = $current_user['id_proof_path'] ?? '';
    if (isset($_FILES['id_proof_path']) && $_FILES['id_proof_path']['error'] === UPLOAD_ERR_OK) {
        $id_proof_path = $upload_dir . time() . '_idproof_' . basename($_FILES['id_proof_path']['name']);
        move_uploaded_file($_FILES['id_proof_path']['tmp_name'], $id_proof_path);
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET 
            is_digambar=?, full_name=?, mobile=?, email=?, `cast`=?, birth_date=?, birth_time=?, birth_place=?, native_place=?, gotra=?, mama_gotra=?, manglik=?,
            height=?, weight=?, gender=?, permanent_address=?, pin_code=?, current_address=?, higher_education=?, hobbies=?, partner_preference=?,
            monthly_income=?, marital_status=?, handicapped=?, languages=?, occupation=?, occupation_other=?, company_name=?, designation=?, father_name=?,
            father_mobile=?, father_income=?, father_occupation=?, father_occupation_other=?, mother_name=?, mother_mobile=?, mother_occupation=?,
            mother_occupation_other=?, brothers=?, brothers_married=?, brothers_unmarried=?, sisters=?, sisters_married=?,
            sisters_unmarried=?, subcast=?, custom_subcast=?, mandir=?, custom_mandir=?, ref1_name=?, ref1_mobile=?, ref1_relation=?,
            ref2_name=?, ref2_mobile=?, ref2_relation=?, profile_photo=?, family_photo=?, payment_screenshot=?, profile_photo_drive_url=?, payment_proof_drive_url=?, status=?,
            filled_by=?, id_proof_type=?, id_proof_path=?, mandir_name=?, mandir_address=?, mandir_pincode=?, registration_step=?
            WHERE id=?
        ");

        $stmt->execute([
            $is_digambar, $full_name, $mobile, $email, $cast, $birth_date, $birth_time, $birth_place, $native, $gotra, $mama_gotra, $manglik,
            $height, $weight, $gender, $permanent_address, $pin_code, $current_address, $education, $hobbies, $partner_preference,
            $monthly_income, $marital_status, $handicapped, $languages, $occupation, $occupation_other, $company_name, $designation, $father_name,
            $father_mobile, $father_income, $father_occupation, $father_occupation_other, $mother_name, $mother_mobile, $mother_occupation,
            $mother_occupation_other, $brothers, $brothers_married, $brothers_unmarried, $sisters, $sisters_married,
            $sisters_unmarried, $subcast, $custom_subcast, $mandir, $custom_mandir, $ref1_name, $ref1_mobile, $ref1_relation,
            $ref2_name, $ref2_mobile, $ref2_relation, $photo, $family_photo, $payment_screenshot, $profile_photo_drive_url, $payment_proof_drive_url, $new_status,
            $filled_by, $id_proof_type, $id_proof_path, $mandir_name, $mandir_address, $mandir_pincode, $registration_step,
            $user_id
        ]);

        // Save Custom Fields
        $stmtInsertCustom = $pdo->prepare("INSERT INTO user_custom_data (user_id, field_id, field_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE field_value = VALUES(field_value)");
        foreach ($customFields as $field) {
            $key = $field['field_key'];
            $value = null;
            if ($field['field_type'] === 'file') {
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] == UPLOAD_ERR_OK) {
                    $filename = time() . '_custom_' . basename($_FILES[$key]['name']);
                    $target_file = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $target_file)) {
                        $value = $target_file;
                    }
                }
            } else {
                if (isset($_POST[$key])) {
                    $value = is_array($_POST[$key]) ? implode(',', $_POST[$key]) : $_POST[$key];
                }
            }
            if ($value !== null) {
                $stmtInsertCustom->execute([$user_id, $field['id'], $value]);
            }
        }

        // Send Email
        if (!isset($_POST['ajax_save'])) {
            require_once 'includes/Mailer.php';
            $mailer = new Mailer();
            $userEmail = $_SESSION['user_email'] ?? ''; // or fetch from db if not in session
            if (!$userEmail) {
                $stmtEmail = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmtEmail->execute([$user_id]);
                $userEmail = $stmtEmail->fetchColumn();
            }
            
            if ($userEmail) {
                if ($is_edit) {
                    $subject = "Profile Updated";
                    $body = "<h2>Hello " . htmlspecialchars($full_name) . "</h2><p>Your profile has been successfully updated.</p>";
                } else {
                    $subject = "Profile Submitted for Approval";
                    $body = "<h2>Hello " . htmlspecialchars($full_name) . "</h2><p>Your profile has been successfully submitted and is currently pending approval by the admin. We will notify you once it is approved.</p>";
                }
                $mailer->send($userEmail, $subject, $body);
                
                // Notify Admin
                if ($is_edit) {
                    $adminSubject = "Profile Updated";
                    $adminBody = "<h2>Profile Updated</h2><p>The profile for <b>" . htmlspecialchars($full_name) . "</b> has been updated.</p>";
                } else {
                    $adminSubject = "New Profile Registration";
                    $adminBody = "<h2>New Profile Submitted</h2><p>A new profile for <b>" . htmlspecialchars($full_name) . "</b> has been submitted and is pending approval.</p>";
                }
                $mailer->send('help@digambarjainparichay.com', $adminSubject, $adminBody);
            }

            $success = $is_edit ? "Profile successfully updated!" : "Registration successful! Your profile has been sent for approval.";
        } else {
            // It's an ajax save, just return success
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (PDOException $e) {
        if (isset($_POST['ajax_save'])) {
            echo json_encode(['success' => false, 'message' => "Database error: " . $e->getMessage()]);
            exit;
        }
        $error = "Registration failed: " . $e->getMessage();
    }
}
}
?>
<?php include 'includes/header.php'; ?>

<section class="py-16 bg-light">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold text-center text-dark mb-4" data-aos="fade-up"><?= $is_edit ? 'Edit Profile' : 'Registration Form' ?></h1>
            <p class="text-center text-gray-600 mb-8" data-aos="fade-up" data-aos-delay="100"><?= $is_edit ? 'Update your profile information' : 'Join the most trusted Digambar Jain Matrimony platform' ?></p>
            
            <?php if (!empty($error)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: <?= json_encode($error) ?>,
                            confirmButtonColor: '#eab308'
                        });
                    });
                </script>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        sessionStorage.removeItem("registrationFormData");
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: <?= json_encode($success) ?>,
                            confirmButtonColor: '#eab308',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            <?php if ($is_edit): ?>
                                window.location.href = "my-profile.php";
                            <?php else: ?>
                                window.location.href = "waiting-approval.php";
                            <?php endif; ?>
                        });
                    });
                </script>
            <?php endif; ?>
            
            <div class="mb-8" id="progressBarContainer">
                <div class="flex justify-between items-center mb-2 relative">
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 z-0"></div>
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-primary z-0 transition-all duration-300" id="progressLine" style="width: 0%;"></div>
                    
                    <div class="step-indicator relative z-10 flex flex-col items-center cursor-default group" data-step="1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold border-4 border-white transition-colors duration-300 step-circle">1</div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 absolute top-10 whitespace-nowrap step-text">Basic Info</span>
                    </div>
                    <div class="step-indicator relative z-10 flex flex-col items-center cursor-default group" data-step="2">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold border-4 border-white transition-colors duration-300 step-circle">2</div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 absolute top-10 whitespace-nowrap step-text">Personal Details</span>
                    </div>
                    <div class="step-indicator relative z-10 flex flex-col items-center cursor-default group" data-step="3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold border-4 border-white transition-colors duration-300 step-circle">3</div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 absolute top-10 whitespace-nowrap step-text">Family Details</span>
                    </div>
                    <div class="step-indicator relative z-10 flex flex-col items-center cursor-default group" data-step="4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold border-4 border-white transition-colors duration-300 step-circle">4</div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 absolute top-10 whitespace-nowrap step-text">Temple & Docs</span>
                    </div>
                </div>
            </div>

            <form id="registrationForm" method="POST" action="" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-6 md:p-8 mt-12" data-aos="fade-up" data-aos-delay="200">
                <input type="hidden" name="registration_step" id="registration_step" value="<?= htmlspecialchars($current_step) ?>">
                <!-- Section 1: Basic Information -->
                <div class="form-section mb-8 pb-4 border-b border-gray-200" data-step="1">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 1: Basic Information</h2>
                    
                    <!-- Are You Digambar Jain -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Are You Digambar Jain? *</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center"><input type="radio" name="is_digambar" value="yes" required class="mr-2" <?= (isset($current_user['is_digambar']) && $current_user['is_digambar'] == 'yes') ? 'checked' : '' ?>> Yes</label>
                            <label class="inline-flex items-center"><input type="radio" name="is_digambar" value="no" required class="mr-2" <?= (isset($current_user['is_digambar']) && $current_user['is_digambar'] == 'no') ? 'checked' : '' ?>> No</label>
                        </div>
                    </div>
                    
                    <!-- Who is Filling This Form -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Who is filling this form? (यह फॉर्म कौन भर रहा है?) *</label>
                        <select name="filled_by" required class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                            <option value="">Select Option</option>
                            <option value="Candidate" <?= (($current_user['filled_by'] ?? '') == 'Candidate') ? 'selected' : '' ?>>Candidate (स्वयं प्रत्याशी)</option>
                            <option value="Father" <?= (($current_user['filled_by'] ?? '') == 'Father') ? 'selected' : '' ?>>Father (पिता)</option>
                            <option value="Mother" <?= (($current_user['filled_by'] ?? '') == 'Mother') ? 'selected' : '' ?>>Mother (माता)</option>
                            <option value="Brother" <?= (($current_user['filled_by'] ?? '') == 'Brother') ? 'selected' : '' ?>>Brother (भाई)</option>
                            <option value="Sister" <?= (($current_user['filled_by'] ?? '') == 'Sister') ? 'selected' : '' ?>>Sister (बहन)</option>
                            <option value="Guardian" <?= (($current_user['filled_by'] ?? '') == 'Guardian') ? 'selected' : '' ?>>Guardian (अभिभावक)</option>
                            <option value="Other" <?= (($current_user['filled_by'] ?? '') == 'Other') ? 'selected' : '' ?>>Other (अन्य)</option>
                        </select>
                    </div>

                    <!-- Gender (moved BEFORE candidate name) -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Gender (लिंग) *</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center"><input type="radio" name="gender" value="male" required class="mr-2" <?= (($current_user['gender'] ?? '') == 'male') ? 'checked' : '' ?>> Male (पुरुष)</label>
                            <label class="inline-flex items-center"><input type="radio" name="gender" value="female" class="mr-2" <?= (($current_user['gender'] ?? '') == 'female') ? 'checked' : '' ?>> Female (महिला)</label>
                        </div>
                    </div>

                    <!-- Candidate Full Name (editable, user must type the name) -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Candidate Full Name (प्रत्याशी का नाम) *</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($current_user['full_name'] ?? '') ?>" required placeholder="Enter candidate's full name" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    
                    <!-- Country Code & Mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <?php if (!isset($coreFieldsSettings['mobile']) || $coreFieldsSettings['mobile']['is_visible']): ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mobile Number *</label>
                            <input type="tel" name="mobile" value="<?= htmlspecialchars(preg_replace('/^\+?91/', '', $current_user['mobile'] ?? '')) ?>" required pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            <p class="text-xs text-gray-500 mt-1">10 digits only number</p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Email *</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($current_user['email']) ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                        <?php 
                        if (!empty($customFieldsByGroup['Section 1: Basic Information'])) {
                            foreach ($customFieldsByGroup['Section 1: Basic Information'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-end mt-6">
                        <button type="button" class="bg-primary hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition next-btn">Save & Continue <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>
                
                <!-- Section 2: Personal Details -->
                <div class="form-section mb-8 pb-4 border-b border-gray-200" data-step="2">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 2: Personal Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Date *</label><input type="date" name="birth_date" value="<?= htmlspecialchars($current_user['birth_date'] ?? '') ?>" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Time *</label>
                            <?php 
                            $db_time = $current_user['birth_time'] ?? '';
                            $bt_hh = ''; $bt_mm = ''; $bt_ampm = '';
                            if($db_time) {
                                if (preg_match('/([0-9]{1,2}):([0-9]{1,2})\s*(AM|PM)?/i', $db_time, $matches)) {
                                    $h = (int)$matches[1];
                                    $bt_mm = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                                    if (isset($matches[3]) && $matches[3]) {
                                        $bt_ampm = strtoupper($matches[3]);
                                        $bt_hh = str_pad($h, 2, '0', STR_PAD_LEFT);
                                    } else {
                                        if($h >= 12) {
                                            $bt_ampm = 'PM';
                                            if($h > 12) $h -= 12;
                                        } else {
                                            $bt_ampm = 'AM';
                                            if($h == 0) $h = 12;
                                        }
                                        $bt_hh = str_pad($h, 2, '0', STR_PAD_LEFT);
                                    }
                                }
                            }
                            ?>
                            <div class="flex gap-2">
                                <select name="birth_time_hh" required class="w-1/3 border rounded-lg px-2 py-2">
                                    <option value="">HH</option>
                                    <?php for($i=1; $i<=12; $i++) { $val = str_pad($i, 2, '0', STR_PAD_LEFT); echo "<option value='$val' ".($bt_hh == $val ? 'selected' : '').">$val</option>"; } ?>
                                </select>
                                <select name="birth_time_mm" required class="w-1/3 border rounded-lg px-2 py-2">
                                    <option value="">MM</option>
                                    <?php for($i=0; $i<=59; $i++) { $val = str_pad($i, 2, '0', STR_PAD_LEFT); echo "<option value='$val' ".($bt_mm == $val ? 'selected' : '').">$val</option>"; } ?>
                                </select>
                                <select name="birth_time_ampm" required class="w-1/3 border rounded-lg px-2 py-2">
                                    <option value="">AM/PM</option>
                                    <option value="AM" <?= $bt_ampm == 'AM' ? 'selected' : '' ?>>AM</option>
                                    <option value="PM" <?= $bt_ampm == 'PM' ? 'selected' : '' ?>>PM</option>
                                </select>
                            </div>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Place *</label><input type="text" name="birth_place" value="<?= htmlspecialchars($current_user['birth_place'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Native (परिवार का मूल स्थान) *</label><input type="text" name="native" value="<?= htmlspecialchars($current_user['native'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Cast (जाति) *</label>
                            <select name="cast" id="cast" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Cast</option>
                                <option value="Digambar Jain" <?= (($current_user['cast'] ?? '') == 'Digambar Jain') ? 'selected' : '' ?>>Digambar Jain</option>
                                <option value="Other" <?= (!in_array(($current_user['cast'] ?? ''), ['', 'Digambar Jain'])) ? 'selected' : '' ?>>Other</option>
                            </select>
                            <input type="text" name="custom_cast" id="custom_cast" value="<?= (!in_array(($current_user['cast'] ?? ''), ['', 'Digambar Jain'])) ? htmlspecialchars($current_user['cast']) : '' ?>" placeholder="Please specify cast" class="w-full border rounded-lg px-4 py-2 mt-2 <?= (!in_array(($current_user['cast'] ?? ''), ['', 'Digambar Jain'])) ? '' : 'hidden' ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Sub-Cast (उपजाति)</label>
                            <select name="subcast" id="subcast" class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Sub-Cast</option>
                                <?php 
                                $predefined_subcasts = ['Khandelwal', 'Agrawal', 'Oswal', 'Porwal', 'Golalare', 'Humad', 'Bagherwal', 'Chaturth', 'Pancham'];
                                $current_subcast = $current_user['subcast'] ?? '';
                                $is_other_subcast = !empty($current_subcast) && !in_array($current_subcast, $predefined_subcasts);
                                foreach ($predefined_subcasts as $sc) {
                                    echo '<option value="' . $sc . '" ' . ($current_subcast == $sc ? 'selected' : '') . '>' . $sc . '</option>';
                                }
                                ?>
                                <option value="Other" <?= $is_other_subcast ? 'selected' : '' ?>>Other (अन्य)</option>
                            </select>
                            <input type="text" name="custom_subcast" id="custom_subcast" value="<?= $is_other_subcast ? htmlspecialchars($current_subcast) : '' ?>" placeholder="Please specify sub-cast" class="w-full border rounded-lg px-4 py-2 mt-2 <?= $is_other_subcast ? '' : 'hidden' ?>">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Gotra (गोत्र) *</label><input type="text" name="gotra" value="<?= htmlspecialchars($current_user['gotra'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mama Gotra (मामा का गोत्र) *</label><input type="text" name="mama_gotra" value="<?= htmlspecialchars($current_user['mama_gotra'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        
                        <!-- Manglik -->
                        <div><label class="block text-gray-700 font-medium mb-2">Manglik (मांगलिक) *</label>
                            <?php $mg = $current_user['manglik'] ?? ''; ?>
                            <div class="flex gap-4"><label><input type="radio" name="manglik" value="yes" required <?= $mg == 'yes' ? 'checked' : '' ?>> Yes / हाँ</label><label><input type="radio" name="manglik" value="no" <?= $mg == 'no' ? 'checked' : '' ?>> No / ना</label></div>
                        </div>
                        
                        <!-- Height Dropdown -->
                        <div><label class="block text-gray-700 font-medium mb-2">Height (ऊंचाई) *</label>
                            <?php $ht = $current_user['height'] ?? ''; ?>
                            <select name="height" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Height</option>
                                <?php 
                                $heights = [
                                    '4 ft 8 inch','4 ft 9 inch','4 ft 10 inch','4 ft 11 inch',
                                    '5 ft','5 ft 1 inch','5 ft 2 inch','5 ft 3 inch',
                                    '5 ft 4 inch','5 ft 5 inch','5 ft 6 inch','5 ft 7 inch',
                                    '5 ft 8 inch','5 ft 9 inch','5 ft 10 inch','5 ft 11 inch',
                                    '6 ft','6 ft 1 inch','6 ft 2 inch','6 ft 3 inch',
                                    '6 ft 4 inch','6 ft 5 inch'
                                ];
                                foreach($heights as $h) {
                                    echo '<option value="'.$h.'" '.($ht == $h ? 'selected' : '').'>'.$h.'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Weight Dropdown -->
                        <div><label class="block text-gray-700 font-medium mb-2">Weight *</label>
                            <select name="weight" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Weight (kg)</option>
                                <?php 
                                for($i=35; $i<=120; $i++) { 
                                    $w = $i . ' kg'; 
                                    $weight = $current_user['weight'] ?? '';
                                    $sel = ($weight === $w || $weight == $i) ? 'selected' : '';
                                    echo "<option value='$w' $sel>$w</option>"; 
                                } 
                                ?>
                            </select>
                        </div>
                        
                        <!-- Permanent Address -->
                        <div><label class="block text-gray-700 font-medium mb-2">Permanent Full Address (स्थायी पता) *</label><textarea name="permanent_address" id="permanent_address" required rows="2" class="w-full border rounded-lg px-4 py-2"><?= htmlspecialchars($current_user['permanent_address'] ?? '') ?></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Pin Code of Permanent Address *</label><input type="text" name="pin_code" value="<?= htmlspecialchars($current_user['pin_code'] ?? '') ?>" pattern="[0-9]{4,6}" maxlength="6" minlength="4" title="Please enter a valid 4 to 6 digit pin code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2"></div>
                        
                        <!-- Same as Permanent Address Checkbox -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="same_as_permanent" name="same_as_permanent" value="1" <?= (!empty($current_user['same_as_permanent'])) ? 'checked' : '' ?> class="mr-2 rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-gray-700 font-medium">Current Address is same as Permanent Address (वर्तमान पता स्थायी पता जैसा ही है)</span>
                            </label>
                        </div>
                        <div id="current_address_container"><label class="block text-gray-700 font-medium mb-2">Candidate Current Address (वर्तमान पता) *</label><textarea name="current_address" id="current_address" required rows="2" class="w-full border rounded-lg px-4 py-2"><?= htmlspecialchars($current_user['current_address'] ?? '') ?></textarea></div>

                        <div><label class="block text-gray-700 font-medium mb-2">Higher Education *</label><input type="text" name="education" value="<?= htmlspecialchars($current_user['education'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Hobbies *</label><textarea name="hobbies" required rows="2" class="w-full border rounded-lg px-4 py-2"><?= htmlspecialchars($current_user['hobbies'] ?? '') ?></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Your Specific Preference for the Partner *</label><textarea name="partner_preference" required rows="2" class="w-full border rounded-lg px-4 py-2"><?= htmlspecialchars($current_user['partner_preference'] ?? '') ?></textarea></div>
                        
                        <!-- Widow/Divorce -->
                        <div><label class="block text-gray-700 font-medium mb-2">Widow / Divorce *</label>
                            <?php $ms = $current_user['marital_status'] ?? ''; ?>
                            <select name="marital_status" required class="w-full border rounded-lg px-4 py-2">
                                <option value="Not Applicable" <?= $ms == 'Not Applicable' ? 'selected' : '' ?>>Not Applicable</option>
                                <option value="Widow" <?= $ms == 'Widow' ? 'selected' : '' ?>>Widow</option>
                                <option value="Divorce" <?= $ms == 'Divorce' ? 'selected' : '' ?>>Divorce</option>
                            </select>
                        </div>
                        
                        <!-- Handicapped -->
                        <div><label class="block text-gray-700 font-medium mb-2">Handicapped / Physical Deficiency *</label>
                            <?php $hc = $current_user['handicapped'] ?? ''; ?>
                            <div class="flex gap-4"><label><input type="radio" name="handicapped" value="yes" required <?= $hc == 'yes' ? 'checked' : '' ?>> Yes</label><label><input type="radio" name="handicapped" value="no" <?= $hc == 'no' ? 'checked' : '' ?>> No</label></div>
                        </div>
                        
                        <!-- Language Known -->
                        <div><label class="block text-gray-700 font-medium mb-2">Language Known *</label>
                            <?php 
                            $curr_langs = !empty($current_user['languages']) ? explode(',', $current_user['languages']) : [];
                            $other_langs = array_diff($curr_langs, ['Gujarati', 'Hindi', 'English']);
                            $has_other_lang = !empty($other_langs);
                            ?>
                            <div class="grid grid-cols-2 gap-2"><label><input type="checkbox" name="languages[]" value="Gujarati" <?= in_array('Gujarati', $curr_langs) ? 'checked' : '' ?>> Gujarati</label><label><input type="checkbox" name="languages[]" value="Hindi" <?= in_array('Hindi', $curr_langs) ? 'checked' : '' ?>> Hindi</label><label><input type="checkbox" name="languages[]" value="English" <?= in_array('English', $curr_langs) ? 'checked' : '' ?>> English</label><label><input type="checkbox" name="languages[]" id="language_other_checkbox" value="Other" <?= $has_other_lang ? 'checked' : '' ?>> Other</label></div>
                            <input type="text" name="other_language" id="other_language_input" value="<?= $has_other_lang ? htmlspecialchars(implode(',', $other_langs)) : '' ?>" placeholder="Specify other language" class="w-full border rounded-lg px-4 py-2 mt-2 <?= $has_other_lang ? '' : 'hidden' ?>">
                        </div>
                        
                        <!-- Occupation, Income, Company, Designation grouped together -->
                        <div class="col-span-1 md:col-span-2 border-t border-dashed border-gray-200 pt-4 mt-2">
                            <h3 class="text-lg font-bold text-primary mb-3"><i class="fas fa-briefcase mr-2"></i>Candidate Occupation & Income Details</h3>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Occupation (व्यवसाय) *</label>
                            <?php $curr_occ = $current_user['occupation'] ?? ''; ?>
                            <div class="flex gap-4">
                                <label><input type="radio" name="occupation" value="Job" required <?= $curr_occ == 'Job' ? 'checked' : '' ?>> Job</label>
                                <label><input type="radio" name="occupation" value="Business" <?= $curr_occ == 'Business' ? 'checked' : '' ?>> Business</label>
                                <label><input type="radio" name="occupation" value="Other" <?= $curr_occ == 'Other' ? 'checked' : '' ?>> Other</label>
                            </div>
                            <input type="text" name="occupation_details" id="occupation_details" value="<?= htmlspecialchars($current_user['occupation_other'] ?? '') ?>" placeholder="Please specify occupation" class="w-full border rounded-lg px-4 py-2 mt-2 <?= $curr_occ == 'Other' ? '' : 'hidden' ?>">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Annual Income (वार्षिक आय) *</label><input type="number" name="annual_income" value="<?= htmlspecialchars($current_user['annual_income'] ?? '') ?>" min="0" step="1" required placeholder="Yearly income amount (e.g., 500000)" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Company/Firm Name (Optional)</label><input type="text" name="company_name" value="<?= htmlspecialchars($current_user['company_name'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Designation (Optional)</label><input type="text" name="designation" value="<?= htmlspecialchars($current_user['designation'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2"></div>
                        <?php 
                        if (!empty($customFieldsByGroup['Section 2: Personal Details'])) {
                            foreach ($customFieldsByGroup['Section 2: Personal Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg transition prev-btn"><i class="fas fa-arrow-left mr-2"></i> Previous</button>
                        <button type="button" class="bg-primary hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition next-btn">Save & Continue <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>
                
                <!-- Family Details Section -->
                <div class="form-section mb-8 pb-4 border-b border-gray-200" data-step="3">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 3: Family Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-gray-700 font-medium mb-2">Father Name *</label><input type="text" name="father_name" value="<?= htmlspecialchars($current_user['father_name'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Father Mobile Number *</label>
                            <input type="tel" name="father_mobile" value="<?= htmlspecialchars(preg_replace('/^\\+?91/', '', $current_user['father_mobile'] ?? '')) ?>" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2">
                            <p class="text-xs text-gray-500 mt-1">10 digits only number</p>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Father Income (Optional)</label><input type="number" name="father_income" value="<?= htmlspecialchars($current_user['father_income'] ?? '') ?>" min="0" step="1" placeholder="Optional" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Father Occupation *</label>
                            <?php $f_occ = $current_user['father_occupation'] ?? ''; ?>
                            <select name="father_occupation" id="father_occupation" required class="w-full border rounded-lg px-4 py-2">
                                <option value="Job" <?= $f_occ == 'Job' ? 'selected' : '' ?>>Job</option>
                                <option value="Business" <?= $f_occ == 'Business' ? 'selected' : '' ?>>Business</option>
                                <option value="Retired" <?= $f_occ == 'Retired' ? 'selected' : '' ?>>Retired</option>
                                <option value="Other" <?= $f_occ == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <input type="text" name="father_occupation_details" id="father_occupation_details" value="<?= htmlspecialchars($current_user['father_occupation_other'] ?? '') ?>" placeholder="Please specify details" class="w-full border rounded-lg px-4 py-2 mt-2 <?= $f_occ == 'Other' ? '' : 'hidden' ?>">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mother Name *</label><input type="text" name="mother_name" value="<?= htmlspecialchars($current_user['mother_name'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mother Mobile Number (Optional)</label>
                            <input type="tel" name="mother_mobile" value="<?= htmlspecialchars(preg_replace('/^\\+?91/', '', $current_user['mother_mobile'] ?? '')) ?>" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border rounded-lg px-4 py-2">
                            <p class="text-xs text-gray-500 mt-1">10 digits only number</p>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mother Occupation (Optional)</label>
                            <?php $m_occ = $current_user['mother_occupation'] ?? ''; ?>
                            <select name="mother_occupation" id="mother_occupation" class="w-full border rounded-lg px-4 py-2">
                                <option value="House Wife" <?= $m_occ == 'House Wife' ? 'selected' : '' ?>>House Wife</option>
                                <option value="Job" <?= $m_occ == 'Job' ? 'selected' : '' ?>>Job</option>
                                <option value="Business" <?= $m_occ == 'Business' ? 'selected' : '' ?>>Business</option>
                                <option value="Other" <?= $m_occ == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <input type="text" name="mother_occupation_details" id="mother_occupation_details" value="<?= htmlspecialchars($current_user['mother_occupation_other'] ?? '') ?>" placeholder="Please specify details" class="w-full border rounded-lg px-4 py-2 mt-2 <?= ($m_occ != '' && $m_occ != 'House Wife') ? '' : 'hidden' ?>">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Brothers Married Count (Optional)</label>
                            <?php $bm = $current_user['brothers_married'] ?? ''; ?>
                            <select name="brothers_married" class="w-full border rounded-lg px-4 py-2"><option value="0" <?= $bm == '0' ? 'selected' : '' ?>>0</option><?php for($i=1;$i<=5;$i++) echo "<option value='$i' ".($bm == $i ? 'selected' : '').">$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Brothers Unmarried Count (Optional)</label>
                            <?php $bum = $current_user['brothers_unmarried'] ?? ''; ?>
                            <select name="brothers_unmarried" class="w-full border rounded-lg px-4 py-2"><option value="0" <?= $bum == '0' ? 'selected' : '' ?>>0</option><?php for($i=1;$i<=5;$i++) echo "<option value='$i' ".($bum == $i ? 'selected' : '').">$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Total Brothers *</label>
                            <?php $tb = $current_user['brothers'] ?? ''; ?>
                            <select name="brothers" required class="w-full border rounded-lg px-4 py-2">
                                <?php for($i=0;$i<=5;$i++) echo "<option value='$i' ".($tb == $i ? 'selected' : '').">$i</option>"; ?>
                            </select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Married Count (Optional)</label>
                            <?php $sm = $current_user['sisters_married'] ?? ''; ?>
                            <select name="sisters_married" class="w-full border rounded-lg px-4 py-2"><option value="0" <?= $sm == '0' ? 'selected' : '' ?>>0</option><?php for($i=1;$i<=5;$i++) echo "<option value='$i' ".($sm == $i ? 'selected' : '').">$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Unmarried Count (Optional)</label>
                            <?php $sum = $current_user['sisters_unmarried'] ?? ''; ?>
                            <select name="sisters_unmarried" class="w-full border rounded-lg px-4 py-2"><option value="0" <?= $sum == '0' ? 'selected' : '' ?>>0</option><?php for($i=1;$i<=5;$i++) echo "<option value='$i' ".($sum == $i ? 'selected' : '').">$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Total Sisters *</label>
                            <?php $ts = $current_user['sisters'] ?? ''; ?>
                            <select name="sisters" required class="w-full border rounded-lg px-4 py-2"><?php for($i=0;$i<=5;$i++) echo "<option value='$i' ".($ts == $i ? 'selected' : '').">$i</option>"; ?></select>
                        </div>
                        <?php 
                        if (!empty($customFieldsByGroup['Family Details'])) {
                            foreach ($customFieldsByGroup['Family Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg transition prev-btn"><i class="fas fa-arrow-left mr-2"></i> Previous</button>
                        <button type="button" class="bg-primary hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition next-btn">Save & Continue <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>
                
                <!-- Section 4: Temple Association Details -->
                <div class="form-section mb-8 pb-4 border-b border-gray-200" data-step="4">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 4: Which Digambar Jain temple are you associated with? (आप किस दिगम्बर जैन मंदिर से जुड़े है?)</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Subcast Select -->
                        <!-- <div>
                            <label class="block text-gray-700 font-medium mb-2">Subcast (उपजाति) *</label>
                            <select name="subcast" id="subcast" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Subcast</option>
                                <?php 
                                $subcasts = !empty($coreFieldsSettings['subcast']['field_options']) ? explode(',', $coreFieldsSettings['subcast']['field_options']) : [];
                                foreach ($subcasts as $sc): 
                                    $sc = trim($sc);
                                    if(empty($sc)) continue;
                                ?>
                                    <option value="<?= htmlspecialchars($sc) ?>"><?= htmlspecialchars($sc) ?></option>
                                <?php endforeach; ?>
                                <option value="Other">Other Subcast (अन्य उपजाति)</option>
                            </select>
                            
                            <div id="customSubcastContainer" class="mt-2 hidden">
                                <label class="block text-xs text-gray-500 font-semibold mb-1">Please Specify Subcast *</label>
                                <input type="text" name="custom_subcast" id="custom_subcast" class="w-full border rounded-lg px-4 py-2" placeholder="Enter your subcast">
                            </div>
                        </div> -->

                        <!-- Mandir Details -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Temple Name (मंदिर का नाम) *</label>
                            <input type="text" name="mandir_name" value="<?= htmlspecialchars($current_user['mandir_name'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-2" placeholder="Shri Digambar Jain Mandir">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Temple Address (मंदिर का पता) *</label>
                            <textarea name="mandir_address" required rows="2" class="w-full border rounded-lg px-4 py-2"><?= htmlspecialchars($current_user['mandir_address'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Temple Pincode (मंदिर का पिनकोड) *</label>
                            <input type="text" name="mandir_pincode" value="<?= htmlspecialchars($current_user['mandir_pincode'] ?? '') ?>" pattern="[0-9]{4,6}" maxlength="6" minlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2">
                        </div>
                    </div>

                    <!-- Reference Persons (Always Visible Now) -->
                    <div id="referencePersonsContainer" class="mt-6 border-t border-dashed border-gray-200 pt-6">
                        <div class="mb-4 bg-blue-50/50 p-4 rounded-lg border border-primary/10">
                            <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-users text-primary"></i> 2 Reference Persons from Same Mandir/Community
                            </h3>
                            <p class="text-sm text-gray-600">Please provide details of two people from your community or same mandir who can vouch for the candidate.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Reference Person 1 -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <h4 class="font-bold text-primary mb-3 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-primary text-white text-xs font-semibold rounded-full flex items-center justify-center">1</span>
                                    Reference Person 1
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Full Name *</label>
                                        <input type="text" name="ref1_name" value="<?= htmlspecialchars($current_user['ref1_name'] ?? '') ?>" required class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Mobile Number *</label>
                                        <input type="tel" name="ref1_mobile" value="<?= htmlspecialchars(preg_replace('/^\\+?91/', '', $current_user['ref1_mobile'] ?? '')) ?>" required pattern="[0-9]{10}" maxlength="10" minlength="10" title="Exactly 10 digit mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                </div>
                            </div>

                            <!-- Reference Person 2 -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <h4 class="font-bold text-primary mb-3 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-primary text-white text-xs font-semibold rounded-full flex items-center justify-center">2</span>
                                    Reference Person 2
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Full Name *</label>
                                        <input type="text" name="ref2_name" value="<?= htmlspecialchars($current_user['ref2_name'] ?? '') ?>" required class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Mobile Number *</label>
                                        <input type="tel" name="ref2_mobile" value="<?= htmlspecialchars(preg_replace('/^\\+?91/', '', $current_user['ref2_mobile'] ?? '')) ?>" required pattern="[0-9]{10}" maxlength="10" minlength="10" title="Exactly 10 digit mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <?php 
                        if (!empty($customFieldsByGroup['Section 4: Mandir Verification Details'])) {
                            foreach ($customFieldsByGroup['Section 4: Mandir Verification Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>

                    <!-- Photos Section (Now part of Section 4) -->
                    <div class="mt-6 border-t border-dashed border-gray-200 pt-6">
                        <h3 class="text-lg font-bold text-primary mb-4">Photos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Candidate Photo is ALWAYS required -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Candidate Photo <?= !empty($current_user['profile_photo']) ? '' : '*' ?> (Passport size photo, max 10MB)</label>
                                <?php if (!empty($current_user['profile_photo'])): ?>
                                    <div class="mb-2">
                                        <img src="image.php?file=<?= urlencode(str_replace('../', '', $current_user['profile_photo'])) ?>" class="w-24 h-24 object-cover border rounded" alt="Profile Photo">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="photo" accept="image/*" <?= !empty($current_user['profile_photo']) ? '' : 'required' ?> class="w-full border rounded-lg px-4 py-2">
                            </div>
                            
                            <?php if (!isset($coreFieldsSettings['family_photo']) || $coreFieldsSettings['family_photo']['is_visible']): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Family Photo <?= (isset($coreFieldsSettings['family_photo']) && $coreFieldsSettings['family_photo']['is_required'] && empty($current_user['family_photo'])) ? '*' : '(Optional)' ?> (Max 10MB)</label>
                                <?php if (!empty($current_user['family_photo'])): ?>
                                    <div class="mb-2">
                                        <img src="image.php?file=<?= urlencode(str_replace('../', '', $current_user['family_photo'])) ?>" class="w-32 h-24 object-cover border rounded" alt="Family Photo">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="family_photo" accept="image/*" <?= (isset($coreFieldsSettings['family_photo']) && $coreFieldsSettings['family_photo']['is_required'] && empty($current_user['family_photo'])) ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                            </div>
                            <?php endif; ?>

                            <?php if (isset($coreFieldsSettings['profile_photo_drive_url']) && $coreFieldsSettings['profile_photo_drive_url']['is_visible']): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Profile Photo Drive URL <?= $coreFieldsSettings['profile_photo_drive_url']['is_required'] ? '*' : '(Optional)' ?></label>
                                <input type="url" name="profile_photo_drive_url" <?= $coreFieldsSettings['profile_photo_drive_url']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                            </div>
                            <?php endif; ?>

                            <!-- ID Proof Upload -->
                            <div class="col-span-1 md:col-span-2 border-t mt-4 pt-4">
                                <h3 class="text-lg font-bold text-primary mb-2">ID Proof Verification</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Select ID Proof Type *</label>
                                        <select name="id_proof_type" required class="w-full border rounded-lg px-4 py-2">
                                            <option value="">Select Option</option>
                                            <option value="Aadhaar Card">Aadhaar Card</option>
                                            <option value="PAN Card">PAN Card</option>
                                            <option value="Voter ID">Voter ID</option>
                                            <option value="Driving Licence">Driving Licence</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Upload ID Proof <?= !empty($current_user['id_proof_path']) ? '' : '*' ?> (Max 5MB)</label>
                                        <?php if (!empty($current_user['id_proof_path'])): ?>
                                            <div class="mb-2">
                                                <a href="image.php?file=<?= urlencode(str_replace('../', '', $current_user['id_proof_path'])) ?>" target="_blank" class="text-blue-500 underline text-sm">View Current ID Proof</a>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="id_proof_path" accept="image/*,.pdf" <?= !empty($current_user['id_proof_path']) ? '' : 'required' ?> class="w-full border rounded-lg px-4 py-2">
                                    </div>
                                </div>
                            </div>

                            <?php 
                            if (!empty($customFieldsByGroup['Photos'])) {
                                foreach ($customFieldsByGroup['Photos'] as $f) echo renderCustomFieldHTML($f);
                            }
                            ?>
                        </div>
                    </div>
                    
                <!-- Documents & Payment -->
                <div class="mt-8 mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-2">Documents & Payment (Presently not compulsory)</h2>
                    <p class="text-gray-500 text-sm mb-4">You can optionally make a payment and upload the screenshot. This is not mandatory at the moment.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        
                        <!-- QR Code Display -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col items-center">
                            <h3 class="font-bold text-gray-700 mb-2">Payment QR Code</h3>
                            <img src="image.php?file=<?= urlencode($payment_qr_code) ?>" alt="Payment QR Code" class="w-48 h-48 border border-yellow-300 rounded shadow-sm object-cover bg-white">
                            <p class="text-xs text-gray-500 mt-2 text-center">Scan to pay securely.</p>
                        </div>

                        <div class="space-y-4">
                            <?php if (!isset($coreFieldsSettings['payment_screenshot']) || $coreFieldsSettings['payment_screenshot']['is_visible']): ?>
                            <div id="payment_screenshot_container">
                                <label class="block text-gray-700 font-medium mb-2">Payment Screenshot (Transaction ID) (Optional)</label>
                                <?php if (!empty($current_user['payment_screenshot'])): ?>
                                    <div class="mb-2">
                                        <a href="image.php?file=<?= urlencode(str_replace('../', '', $current_user['payment_screenshot'])) ?>" target="_blank" class="text-blue-500 underline text-sm"><i class="fas fa-external-link-alt"></i> View Current Payment Screenshot</a>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="payment_screenshot" id="payment_screenshot" accept="image/*" class="w-full border rounded-lg px-4 py-2 bg-white">
                            </div>
                            <?php endif; ?>

                            <?php if (isset($coreFieldsSettings['payment_proof_drive_url']) && $coreFieldsSettings['payment_proof_drive_url']['is_visible']): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Payment Proof Drive URL (Optional)</label>
                                <input type="url" name="payment_proof_drive_url" class="w-full border rounded-lg px-4 py-2 bg-white">
                            </div>
                            <?php endif; ?>
                            
                            <?php 
                            if (!empty($customFieldsByGroup['Documents & Payment'])) {
                                foreach ($customFieldsByGroup['Documents & Payment'] as $f) echo renderCustomFieldHTML($f);
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($customFieldsByGroup['Additional Information'])): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-primary mb-4">Additional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($customFieldsByGroup['Additional Information'] as $f) echo renderCustomFieldHTML($f); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-6">
                    <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg transition prev-btn"><i class="fas fa-arrow-left mr-2"></i> Previous</button>
                    <!-- Submit button is below -->
                </div>
            </div>
                
                <button id="submitBtn" type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition disabled:opacity-75 disabled:cursor-not-allowed"><?= $is_edit ? 'Update Profile' : 'Register Now' ?></button>
            </form>
        </div>
    </div>
</section>

<script>
const currentUserData = <?= json_encode($current_user ?: []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate form fields with user data on load
    if (currentUserData && Object.keys(currentUserData).length > 0) {
        for (const [key, value] of Object.entries(currentUserData)) {
            if (value === null || value === '') continue;
            
            // Handle radio buttons
            const radios = document.querySelectorAll(`input[type="radio"][name="${key}"]`);
            if (radios.length > 0) {
                radios.forEach(r => {
                    if (r.value.toLowerCase() === value.toString().toLowerCase()) r.checked = true;
                });
                continue;
            }
            
            // Handle checkboxes (e.g. languages)
            if (key === 'languages') {
                const langs = value.split(',');
                const checkboxes = document.querySelectorAll(`input[type="checkbox"][name="${key}[]"]`);
                let hasOther = false;
                checkboxes.forEach(cb => {
                    if (langs.includes(cb.value)) cb.checked = true;
                });
                
                // If any language in the list doesn't match predefined options, it's 'Other'
                langs.forEach(l => {
                    const cb = document.querySelector(`input[type="checkbox"][name="${key}[]"][value="${l}"]`);
                    if (!cb && l.trim() !== '') {
                        hasOther = true;
                        const otherInput = document.querySelector(`input[name="other_language"]`);
                        if(otherInput) {
                            otherInput.value = l;
                            otherInput.classList.remove('hidden');
                        }
                    }
                });
                if(hasOther) {
                    const otherCb = document.querySelector(`input[type="checkbox"][name="${key}[]"][value="Other"]`);
                    if(otherCb) otherCb.checked = true;
                }
                continue;
            }
            
            // Special handling for occupation dropdowns
            if (['occupation', 'father_occupation', 'mother_occupation'].includes(key)) {
                const select = document.querySelector(`select[name="${key}"]`);
                if (select) {
                    // Check if value is one of the options
                    let optionExists = false;
                    Array.from(select.options).forEach(opt => {
                        if (opt.value === value || (opt.value === '' && value === '')) optionExists = true;
                    });
                    
                    if (!optionExists && value) {
                        select.value = 'Other';
                        const detailsInput = document.querySelector(`input[name="${key}_details"]`);
                        if (detailsInput) {
                            detailsInput.value = value;
                            detailsInput.classList.remove('hidden');
                        }
                    } else {
                        select.value = value;
                    }
                }
                continue;
            }
            
            // Special handling for cast / subcast / mandir custom fields
            if (['cast', 'subcast', 'mandir'].includes(key)) {
                const select = document.querySelector(`select[name="${key}"]`);
                if (select) {
                    let optionExists = false;
                    Array.from(select.options).forEach(opt => {
                        if (opt.value === value || (opt.value === '' && value === '')) optionExists = true;
                    });
                    
                    if (!optionExists && value) {
                        select.value = 'Other';
                        const detailsInput = document.querySelector(`input[name="custom_${key}"]`);
                        if (detailsInput) {
                            detailsInput.value = value;
                            detailsInput.classList.remove('hidden');
                        }
                    } else {
                        select.value = value;
                    }
                }
                continue;
            }

            // Normal inputs, selects, textareas
            const input = document.querySelector(`[name="${key}"]`);
            if (input && input.type !== 'file') {
                input.value = value;
            }
        }
        
        // Special handling for birth_time 
        if (currentUserData.birth_time) {
            const timeParts = currentUserData.birth_time.match(/([0-9]{1,2}):([0-9]{1,2})\s*(AM|PM)?/i);
            if (timeParts) {
                const hh = document.querySelector('[name="birth_time_hh"]');
                const mm = document.querySelector('[name="birth_time_mm"]');
                const ampm = document.querySelector('[name="birth_time_ampm"]');
                let h = parseInt(timeParts[1], 10);
                let m = timeParts[2].padStart(2, '0');
                let p = timeParts[3] ? timeParts[3].toUpperCase() : '';
                if (!p) {
                    if (h >= 12) { p = 'PM'; if (h > 12) h -= 12; }
                    else { p = 'AM'; if (h === 0) h = 12; }
                }
                if (hh) hh.value = h.toString().padStart(2, '0');
                if (mm) mm.value = m;
                if (ampm) ampm.value = p;
            }
        }
        
        // Special handling for weight
        if (currentUserData.weight) {
            const wInput = document.querySelector('[name="weight"]');
            if (wInput) {
                const wVal = parseInt(currentUserData.weight, 10);
                if (!isNaN(wVal)) {
                    wInput.value = wVal + ' kg';
                }
            }
        }

        // Special handling for mobile (remove +91)
        if (currentUserData.mobile) {
            const mobileInput = document.querySelector('[name="mobile"]');
            if (mobileInput) mobileInput.value = currentUserData.mobile.replace(/^\+?91/, '');
        }
        
        // Special mapping for field names that don't match DB columns
        const mappings = {
            'native_place': 'native',
            'monthly_income': 'annual_income',
            'higher_education': 'education'
        };
        
        Object.keys(mappings).forEach(dbKey => {
            if (currentUserData[dbKey]) {
                const input = document.querySelector(`[name="${mappings[dbKey]}"]`);
                if (input) input.value = currentUserData[dbKey];
            }
        });
        
        // Special handling for languages[] checkbox array
        if (currentUserData.languages) {
            const langs = currentUserData.languages.split(',');
            const langCheckboxes = document.querySelectorAll('input[name="languages[]"]');
            langs.forEach(lang => {
                lang = lang.trim();
                let matched = false;
                langCheckboxes.forEach(cb => {
                    if (cb.value === lang) {
                        cb.checked = true;
                        matched = true;
                    }
                });
                if (!matched && lang) {
                    const otherCb = document.getElementById('language_other_checkbox');
                    if (otherCb) otherCb.checked = true;
                    const otherInput = document.getElementById('other_language_input');
                    if (otherInput) {
                        otherInput.value = lang;
                        otherInput.classList.remove('hidden');
                    }
                }
            });
        }
    }
    const form = document.getElementById('registrationForm');
    const sections = Array.from(document.querySelectorAll('.form-section'));
    const stepIndicators = Array.from(document.querySelectorAll('.step-indicator'));
    const progressLine = document.getElementById('progressLine');
    const submitBtn = document.getElementById('submitBtn');
    
    // Default to 1 if no step found, otherwise fetch from hidden input
    let currentStep = parseInt(document.getElementById('registration_step').value) || 1;
    if (currentStep > sections.length) currentStep = sections.length;
    if (currentStep < 1) currentStep = 1;

    function showStep(step) {
        // Update Sections
        sections.forEach((sec, idx) => {
            if (idx + 1 === step) {
                sec.style.display = 'block';
            } else {
                sec.style.display = 'none';
            }
        });

        // Update indicators and progress line
        stepIndicators.forEach((ind, idx) => {
            const circle = ind.querySelector('.step-circle');
            const text = ind.querySelector('.step-text');
            if (idx + 1 < step) {
                // completed
                circle.classList.remove('bg-gray-200', 'text-gray-500', 'bg-primary', 'text-white');
                circle.classList.add('bg-green-500', 'text-white');
                circle.innerHTML = '<i class="fas fa-check"></i>';
                text.classList.remove('text-gray-500', 'text-primary');
                text.classList.add('text-green-500');
            } else if (idx + 1 === step) {
                // current
                circle.classList.remove('bg-gray-200', 'text-gray-500', 'bg-green-500');
                circle.classList.add('bg-primary', 'text-white');
                circle.innerHTML = (idx + 1);
                text.classList.remove('text-gray-500', 'text-green-500');
                text.classList.add('text-primary');
            } else {
                // upcoming
                circle.classList.remove('bg-primary', 'bg-green-500', 'text-white');
                circle.classList.add('bg-gray-200', 'text-gray-500');
                circle.innerHTML = (idx + 1);
                text.classList.remove('text-primary', 'text-green-500');
                text.classList.add('text-gray-500');
            }
        });

        const progressPercent = ((step - 1) / (sections.length - 1)) * 100;
        progressLine.style.width = progressPercent + '%';

        // Toggle submit button visibility
        if (step === sections.length) {
            submitBtn.style.display = 'block';
        } else {
            submitBtn.style.display = 'none';
        }
    }

    async function autoSave(stepIndex) {
        const formData = new FormData(form);
        formData.append('ajax_save', 'true');
        formData.append('registration_step', stepIndex);

        try {
            const res = await fetch('', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if(!data.success) {
                console.error('Auto save failed:', data.message);
                Swal.fire({
                    title: 'Error Saving Progress',
                    text: data.message || 'There was a problem saving your data. Please check your inputs.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
                return false;
            }
            return true;
        } catch (err) {
            console.error('Network error during auto save:', err);
            Swal.fire({
                title: 'Network Error',
                text: 'Could not connect to the server to save your progress.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ef4444'
            });
            return false;
        }
    }

    // Attach Next Button Events
    sections.forEach((sec, idx) => {
        const nextBtn = sec.querySelector('.next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', async () => {
                // Validate current section fields
                const inputs = sec.querySelectorAll('input, select, textarea');
                let isValid = true;
                for (let input of inputs) {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        isValid = false;
                        break;
                    }
                }
                
                if (isValid) {
                    const nextBtnOriginalHtml = nextBtn.innerHTML;
                    nextBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    nextBtn.disabled = true;

                    const targetStep = idx + 2;
                    const isSaved = await autoSave(targetStep);
                    
                    nextBtn.innerHTML = nextBtnOriginalHtml;
                    nextBtn.disabled = false;
                    
                    if (isSaved) {
                        currentStep = targetStep;
                        document.getElementById('registration_step').value = currentStep;
                        showStep(currentStep);
                        window.scrollTo({ top: document.getElementById('progressBarContainer').offsetTop - 20, behavior: 'smooth' });
                    }
                }
            });
        }

        const prevBtn = sec.querySelector('.prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', async () => {
                const prevBtnOriginalHtml = prevBtn.innerHTML;
                prevBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                prevBtn.disabled = true;

                // Save current progress before going back
                const targetStep = idx;
                await autoSave(targetStep);

                prevBtn.innerHTML = prevBtnOriginalHtml;
                prevBtn.disabled = false;

                currentStep = targetStep;
                document.getElementById('registration_step').value = currentStep;
                showStep(currentStep);
                window.scrollTo({ top: document.getElementById('progressBarContainer').offsetTop - 20, behavior: 'smooth' });
            });
        }
    });

    // Initialize step
    showStep(currentStep);

    // Prevent form submission on Enter key
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            if (currentStep < sections.length) {
                e.preventDefault();
                const currentSection = sections[currentStep - 1];
                const nextBtn = currentSection.querySelector('.next-btn');
                if (nextBtn) nextBtn.click();
            }
        }
    });

    // Auto-save on any input change so data isn't lost on accidental refresh
    form.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('change', () => {
            autoSave(currentStep);
        });
    });

    // Handle form submit explicitly
    form.addEventListener('submit', function(e) {
        if (currentStep !== sections.length) {
            e.preventDefault();
            return false;
        }
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
    });
});

document.getElementById('registrationForm').addEventListener('submit', function(e) {
    if (currentStep !== sections.length) {
        e.preventDefault();
        return false;
    }

    const mobileInput = document.querySelector('input[name="mobile"]');
    if (!mobileInput) return; // If mobile is not visible/required, proceed
    
    e.preventDefault();
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Checking...';
    submitBtn.disabled = true;

    fetch('api_check_mobile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ mobile: mobileInput.value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'duplicate') {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            Swal.fire({
                title: 'Already Registered',
                text: 'This mobile number is already registered.\nPlease enter a different mobile number.',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#eab308'
            });
        } else {
            // If OK or error from check, just proceed with normal submission
            // Using submit() bypasses the event listener
            document.getElementById('registrationForm').submit();
        }
    })
    .catch(err => {
        document.getElementById('registrationForm').submit();
    });
});

document.querySelectorAll('input[name="is_digambar"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const formElements = document.querySelectorAll('#registrationForm input:not([name="is_digambar"]), #registrationForm select, #registrationForm textarea, #registrationForm button[type="submit"]');
        if (this.value === 'no') {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Sorry, this registration is strictly for Digambar Jains only.'});
            formElements.forEach(el => el.disabled = true);
            document.getElementById('registrationForm').classList.add('opacity-50');
        } else {
            formElements.forEach(el => el.disabled = false);
            document.getElementById('registrationForm').classList.remove('opacity-50');
        }
    });
});

// Same as Permanent Address checkbox handler
document.getElementById('same_as_permanent')?.addEventListener('change', function() {
    const currentAddressContainer = document.getElementById('current_address_container');
    const currentAddressField = document.getElementById('current_address');
    const permanentAddressField = document.getElementById('permanent_address');
    
    if (this.checked) {
        // Copy permanent address to current address
        currentAddressField.value = permanentAddressField.value;
    } else {
        // Clear current address
        currentAddressField.value = '';
    }
});

// Keep current address in sync if permanent address changes while checkbox is checked
document.getElementById('permanent_address')?.addEventListener('input', function() {
    const sameCheckbox = document.getElementById('same_as_permanent');
    if (sameCheckbox && sameCheckbox.checked) {
        document.getElementById('current_address').value = this.value;
    }
});

function handleOtherWithSwal(element, hiddenInputId, otherValue) {
    const hiddenInput = document.getElementById(hiddenInputId);
    let isOtherSelected = false;
    
    if (element.type === 'radio' || element.type === 'checkbox') {
        isOtherSelected = element.checked && element.value === otherValue;
    } else {
        isOtherSelected = element.value === otherValue;
    }

    if (isOtherSelected) {
        if (!hiddenInput.value) {
            Swal.fire({
                title: 'Please Specify Details',
                input: 'text',
                inputPlaceholder: 'Enter details here...',
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write something!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    hiddenInput.value = result.value;
                    hiddenInput.classList.remove('hidden');
                    hiddenInput.required = true;
                    // Trigger input event to save to sessionStorage
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                } else {
                    if (element.type === 'radio' || element.type === 'checkbox') {
                        element.checked = false;
                    } else {
                        element.selectedIndex = 0;
                    }
                    hiddenInput.classList.add('hidden');
                    hiddenInput.required = false;
                    hiddenInput.value = '';
                }
            });
        } else {
            hiddenInput.classList.remove('hidden');
            hiddenInput.required = true;
        }
    } else {
        if ((element.type === 'radio' && element.checked) || element.type === 'select-one' || (element.type === 'checkbox' && !element.checked)) {
            hiddenInput.classList.add('hidden');
            hiddenInput.required = false;
            hiddenInput.value = '';
        }
    }
}

document.getElementById('language_other_checkbox')?.addEventListener('change', function() {
    handleOtherWithSwal(this, 'other_language_input', 'Other');
});

document.getElementById('cast')?.addEventListener('change', function() {
    handleOtherWithSwal(this, 'custom_cast', 'Other');
});

document.getElementById('subcast')?.addEventListener('change', function() {
    handleOtherWithSwal(this, 'custom_subcast', 'Other');
});

document.querySelectorAll('input[name="occupation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        handleOtherWithSwal(this, 'occupation_details', 'Other');
    });
});

document.getElementById('father_occupation')?.addEventListener('change', function(e) {
    handleOtherWithSwal(this, 'father_occupation_details', 'Other');
});

document.getElementById('mother_occupation')?.addEventListener('change', function(e) {
    const detailsInput = document.getElementById('mother_occupation_details');
    if (this.value !== 'House Wife' && this.value !== '') {
        detailsInput.classList.remove('hidden');
        detailsInput.required = true;
    } else {
        detailsInput.classList.add('hidden');
        detailsInput.required = false;
        detailsInput.value = '';
    }
});

document.getElementById('registrationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Digambar Jain validation
    const isDigambar = document.querySelector('input[name="is_digambar"]:checked')?.value;
    if (isDigambar === 'no') {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Sorry, this registration is strictly for Digambar Jains only.'});
        return;
    }

    function isValidPhone(val) {
        if (!val) return false;
        const digits = val.replace(/\D/g, '');
        return digits.length >= 7 && digits.length <= 15;
    }

    // Phone number validations (basic 7-15 digits check)
    const mobileEl = document.querySelector('input[name="mobile"]');
    if (mobileEl && mobileEl.value && !isValidPhone(mobileEl.value)) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mobile number (7 to 15 digits).'});
        return;
    }

    const fatherMobileEl = document.querySelector('input[name="father_mobile"]');
    if (fatherMobileEl && fatherMobileEl.value && !isValidPhone(fatherMobileEl.value)) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid father mobile number.'});
        return;
    }

    const motherMobileEl = document.querySelector('input[name="mother_mobile"]');
    if (motherMobileEl && motherMobileEl.value && !isValidPhone(motherMobileEl.value)) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mother mobile number.'});
        return;
    }

    // Mandir Verification Details & Reference validation
    const subcastEl = document.getElementById('subcast');
    const mandirEl = document.getElementById('mandir');
    
    if (subcastEl && mandirEl && subcastEl.value && mandirEl.value) {
        const ref1NameEl = document.getElementById('ref1_name');
        const ref1MobileEl = document.getElementById('ref1_mobile');
        const ref2NameEl = document.getElementById('ref2_name');
        const ref2MobileEl = document.getElementById('ref2_mobile');
        
        const ref1Name = ref1NameEl ? ref1NameEl.value.trim() : '';
        const ref1Mobile = ref1MobileEl ? ref1MobileEl.value.trim() : '';
        const ref2Name = ref2NameEl ? ref2NameEl.value.trim() : '';
        const ref2Mobile = ref2MobileEl ? ref2MobileEl.value.trim() : '';

        if (!ref1Name || !ref1Mobile || !ref2Name || !ref2Mobile) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Please fill out both Reference Persons\' Name and Mobile number.'});
            return;
        }

        if (!isValidPhone(ref1Mobile)) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mobile number (7 to 15 digits) for Reference Person 1.'});
            return;
        }

        if (!isValidPhone(ref2Mobile)) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mobile number (7 to 15 digits) for Reference Person 2.'});
            return;
        }

        // Duplication and sanity checks
        if (ref1Mobile === ref2Mobile) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Reference Person 1 and Reference Person 2 must have different mobile numbers.'});
            return;
        }

        const mobileVal = mobileEl ? mobileEl.value : '';
        if (mobileVal && (ref1Mobile === mobileVal || ref2Mobile === mobileVal)) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Reference mobile number cannot be the same as the candidate\'s mobile number.'});
            return;
        }

        const fatherMobileVal = fatherMobileEl ? fatherMobileEl.value : '';
        if (fatherMobileVal && (ref1Mobile === fatherMobileVal || ref2Mobile === fatherMobileVal)) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Reference mobile number cannot be the same as the father\'s mobile number.'});
            return;
        }
    }

    // File size validations (10MB = 10 * 1024 * 1024 bytes)
    const maxFileSize = 10 * 1024 * 1024;
    
    const photoInput = document.querySelector('input[name="photo"]');
    const photo = photoInput ? photoInput.files[0] : null;
    if (photo && photo.size > maxFileSize) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Candidate Photo must be less than 10MB.'});
        return;
    }

    const familyPhotoInput = document.querySelector('input[name="family_photo"]');
    const familyPhoto = familyPhotoInput ? familyPhotoInput.files[0] : null;
    if (familyPhoto && familyPhoto.size > maxFileSize) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Family Photo must be less than 10MB.'});
        return;
    }

    const paymentScreenshotInput = document.querySelector('input[name="payment_screenshot"]');
    const paymentScreenshot = paymentScreenshotInput ? paymentScreenshotInput.files[0] : null;
    if (paymentScreenshot && paymentScreenshot.size > maxFileSize) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Payment Screenshot must be less than 10MB.'});
        return;
    }

    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Processing... <i class="fas fa-spinner fa-spin ml-2"></i>';
    }
    
    this.submit();
});

// --- Auto-Save Form Data to Prevent Loss on Refresh ---
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("registrationForm");
    if (!form) return;

    // Load saved data
    const savedData = sessionStorage.getItem("registrationFormData");
    const currentName = "<?= addslashes($current_user['full_name']) ?>";
    const currentMobile = "<?= addslashes(preg_replace('/^\+?91/', '', $current_user['mobile'] ?? '')) ?>";

    // Clear saved data if it belongs to a different user
    if (savedData) {
        try {
            const parsed = JSON.parse(savedData);
            if (parsed['full_name'] !== currentName || parsed['mobile'] !== currentMobile) {
                sessionStorage.removeItem("registrationFormData");
            }
        } catch(e) {
            sessionStorage.removeItem("registrationFormData");
        }
    }

    const freshData = sessionStorage.getItem("registrationFormData");
    if (freshData) {
        try {
            const data = JSON.parse(freshData);
            Object.keys(data).forEach(key => {
                const input = form.elements[key];
                if (input) {
                    // Skip readonly fields — always use server value
                    if (input.readOnly || input.hasAttribute('readonly')) return;
                    // Handle RadioNodeList and single inputs
                    if (input instanceof RadioNodeList || (input.length && input[0].type === 'radio')) {
                        Array.from(input).forEach(radio => {
                            if (Array.isArray(data[key])) {
                                if (data[key].includes(radio.value)) radio.checked = true;
                            } else {
                                if (radio.value === data[key]) radio.checked = true;
                            }
                        });
                    } else if (input.type === 'checkbox') {
                        if (Array.isArray(data[key])) {
                            input.checked = data[key].includes(input.value);
                        } else {
                            input.checked = (data[key] === input.value || data[key] === true);
                        }
                    } else if (input.type !== 'file' && input.type !== 'password') {
                        let val = data[key];
                        if (['mobile', 'father_mobile', 'mother_mobile', 'ref1_mobile', 'ref2_mobile'].includes(key) && typeof val === 'string') {
                            val = val.replace(/^\+?91/, '');
                        }
                        input.value = val;
                    }
                }
            });
            // Trigger change events to update dependent UI
            document.querySelectorAll('select').forEach(el => el.dispatchEvent(new Event('change')));
            document.querySelectorAll('input[type="radio"]:checked').forEach(el => el.dispatchEvent(new Event('change')));
        } catch (e) {
            console.error("Error restoring form data", e);
        }
    }

    // Save data on input change
    form.addEventListener("input", function(e) {
        if (e.target.type === 'password' || e.target.type === 'file') return;
        
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (e.target.type === 'password' && e.target.name === key) continue;
            if (data[key]) {
                if (!Array.isArray(data[key])) {
                    data[key] = [data[key]];
                }
                data[key].push(value);
            } else {
                data[key] = value;
            }
        }
        sessionStorage.setItem("registrationFormData", JSON.stringify(data));
    });

    // --- Real-Time Field Validations ---
    const nameFields = ['full_name', 'father_name', 'mother_name', 'ref1_name', 'ref2_name'];
    const phoneFields = ['mobile', 'father_mobile', 'mother_mobile', 'ref1_mobile', 'ref2_mobile'];
    
    // Restrict name fields to letters and spaces
    nameFields.forEach(name => {
        const field = document.querySelector(`input[name="${name}"]`);
        if (field) {
            field.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-Z\s\.]/g, '');
            });
        }
    });

    // Restrict phone fields to numbers only
    phoneFields.forEach(name => {
        const field = document.querySelector(`input[name="${name}"]`);
        if (field) {
            field.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });

    // Restrict pin code to exactly numbers, max 6 digits
    const pinCodeField = document.querySelector('input[name="pin_code"]');
    if (pinCodeField) {
        pinCodeField.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    }

    // Dynamic Brothers and Sisters Count
    function updateSiblingTotal(type) {
        const marriedSelect = document.querySelector(`select[name="${type}_married"]`);
        const unmarriedSelect = document.querySelector(`select[name="${type}_unmarried"]`);
        const totalSelect = document.querySelector(`select[name="${type}"]`);
        
        if (marriedSelect && unmarriedSelect && totalSelect) {
            const married = parseInt(marriedSelect.value) || 0;
            const unmarried = parseInt(unmarriedSelect.value) || 0;
            let total = married + unmarried;
            if (total > 5) total = 5; // Max limit as per options
            totalSelect.value = total;
            totalSelect.setAttribute('readonly', true);
            totalSelect.classList.add('bg-gray-100'); // Make it look disabled
        }
    }
    
    ['brothers', 'sisters'].forEach(type => {
        const married = document.querySelector(`select[name="${type}_married"]`);
        const unmarried = document.querySelector(`select[name="${type}_unmarried"]`);
        if (married) married.addEventListener('change', () => updateSiblingTotal(type));
        if (unmarried) unmarried.addEventListener('change', () => updateSiblingTotal(type));
        
        // Disable manual change on total if JS is active
        const total = document.querySelector(`select[name="${type}"]`);
        if(total) {
            total.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
        }
    });

    // Disable Submit Button on Form Submit to prevent double submission
    form.addEventListener('submit', function(e) {
        // Validate 10-digit mobile numbers
        let isValid = true;
        phoneFields.forEach(name => {
            const field = document.querySelector(`input[name="${name}"]`);
            if (field && field.value && field.value.length !== 10) {
                isValid = false;
                field.classList.add('border-red-500');
                // You could also show a toast or alert here
            } else if (field) {
                field.classList.remove('border-red-500');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert("Please ensure all mobile numbers are exactly 10 digits.");
            return false;
        }

        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>