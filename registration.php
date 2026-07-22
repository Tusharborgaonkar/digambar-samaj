<?php
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
$current_user = $stmt->fetch();

// Fetch site settings for QR code
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
$payment_qr_code = $settings['payment_qr_code'] ?? 'assets/images/qr_code.jpg';

$full_name = '';

$is_edit = ($current_user !== false && $current_user['status'] === 'approved');
$new_status = $is_edit ? $current_user['status'] : 'pending';

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
    $marital_status = htmlspecialchars($_POST['marital_status'] ?? '');
    $handicapped = $_POST['handicapped'] ?? '';
    
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

    // Combine Birth Time if sent as array (hh, mm, ampm)
    if (isset($_POST['birth_time_hh'], $_POST['birth_time_mm'], $_POST['birth_time_ampm'])) {
        $birth_time = str_pad($_POST['birth_time_hh'], 2, '0', STR_PAD_LEFT) . ':' . 
                      str_pad($_POST['birth_time_mm'], 2, '0', STR_PAD_LEFT) . ' ' . 
                      $_POST['birth_time_ampm'];
    }

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
    } elseif ($monthly_income !== '' && (!is_numeric($monthly_income) || $monthly_income < 0)) {
        $error = "Candidate annual income must be a valid positive amount.";
    } elseif ($father_income !== '' && (!is_numeric($father_income) || $father_income < 0)) {
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

    if (!$error) {
        // Handle File Uploads
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $photo = $is_edit ? ($current_user['profile_photo'] ?? '') : '';
    $family_photo = $is_edit ? ($current_user['family_photo'] ?? '') : '';
    $payment_screenshot = $is_edit ? ($current_user['payment_screenshot'] ?? '') : '';

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
    
    $id_proof_path = $is_edit ? ($current_user['id_proof_path'] ?? '') : '';
    if (isset($_FILES['id_proof_path']) && $_FILES['id_proof_path']['error'] === UPLOAD_ERR_OK) {
        $id_proof_path = $upload_dir . time() . '_idproof_' . basename($_FILES['id_proof_path']['name']);
        move_uploaded_file($_FILES['id_proof_path']['tmp_name'], $id_proof_path);
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET 
            full_name=?, mobile=?, email=?, `cast`=?, birth_date=?, birth_time=?, birth_place=?, native_place=?, gotra=?, mama_gotra=?, manglik=?,
            height=?, weight=?, gender=?, permanent_address=?, pin_code=?, current_address=?, higher_education=?, hobbies=?, partner_preference=?,
            monthly_income=?, marital_status=?, handicapped=?, languages=?, occupation=?, occupation_other=?, company_name=?, designation=?, father_name=?,
            father_mobile=?, father_income=?, father_occupation=?, father_occupation_other=?, mother_name=?, mother_mobile=?, mother_occupation=?,
            mother_occupation_details=?, brothers=?, brothers_married=?, brothers_unmarried=?, sisters=?, sisters_married=?,
            sisters_unmarried=?, subcast=?, custom_subcast=?, mandir=?, custom_mandir=?, ref1_name=?, ref1_mobile=?, ref1_relation=?,
            ref2_name=?, ref2_mobile=?, ref2_relation=?, profile_photo=?, family_photo=?, payment_screenshot=?, profile_photo_drive_url=?, payment_proof_drive_url=?, status=?,
            filled_by=?, id_proof_type=?, id_proof_path=?, mandir_name=?, mandir_address=?, mandir_pincode=?
            WHERE id=?
        ");

        $stmt->execute([
            $full_name, $mobile, $email, $cast, $birth_date, $birth_time, $birth_place, $native, $gotra, $mama_gotra, $manglik,
            $height, $weight, $gender, $permanent_address, $pin_code, $current_address, $education, $hobbies, $partner_preference,
            $monthly_income, $marital_status, $handicapped, $languages, $occupation, $occupation_other, $company_name, $designation, $father_name,
            $father_mobile, $father_income, $father_occupation, $father_occupation_other, $mother_name, $mother_mobile, $mother_occupation,
            $mother_occupation_details, $brothers, $brothers_married, $brothers_unmarried, $sisters, $sisters_married,
            $sisters_unmarried, $subcast, $custom_subcast, $mandir, $custom_mandir, $ref1_name, $ref1_mobile, $ref1_relation,
            $ref2_name, $ref2_mobile, $ref2_relation, $photo, $family_photo, $payment_screenshot, $profile_photo_drive_url, $payment_proof_drive_url, $new_status,
            $filled_by, $id_proof_type, $id_proof_path, $mandir_name, $mandir_address, $mandir_pincode,
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
    } catch (PDOException $e) {
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
            
            <form id="registrationForm" method="POST" action="" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-6 md:p-8" data-aos="fade-up" data-aos-delay="200">
                <!-- Section 1: Basic Information -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 1: Basic Information</h2>
                    
                    <!-- Are You Digambar Jain -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Are You Digambar Jain? *</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center"><input type="radio" name="is_digambar" value="yes" required class="mr-2"> Yes</label>
                            <label class="inline-flex items-center"><input type="radio" name="is_digambar" value="no" required class="mr-2"> No</label>
                        </div>
                    </div>
                    
                    <!-- Who is Filling This Form -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Who is filling this form? (यह फॉर्म कौन भर रहा है?) *</label>
                        <select name="filled_by" required class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                            <option value="">Select Option</option>
                            <option value="Candidate">Candidate (स्वयं प्रत्याशी)</option>
                            <option value="Father">Father (पिता)</option>
                            <option value="Mother">Mother (माता)</option>
                            <option value="Brother">Brother (भाई)</option>
                            <option value="Sister">Sister (बहन)</option>
                            <option value="Guardian">Guardian (अभिभावक)</option>
                            <option value="Other">Other (अन्य)</option>
                        </select>
                    </div>

                    <!-- Gender (moved BEFORE candidate name) -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Gender (लिंग) *</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center"><input type="radio" name="gender" value="male" required class="mr-2"> Male (पुरुष)</label>
                            <label class="inline-flex items-center"><input type="radio" name="gender" value="female" class="mr-2"> Female (महिला)</label>
                        </div>
                    </div>

                    <!-- Candidate Full Name (editable, user must type the name) -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Candidate Full Name (प्रत्याशी का नाम) *</label>
                        <input type="text" name="full_name" value="" required placeholder="Enter candidate's full name" class="w-full border border-gray-300 rounded-lg px-4 py-2">
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
                </div>
                
                <!-- Section 2: Personal Details -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 2: Personal Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Date *</label><input type="date" name="birth_date" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" required class="w-full border rounded-lg px-4 py-2"></div>
                        
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Time *</label>
                            <div class="flex gap-2">
                                <select name="birth_time_hh" required class="w-1/3 border rounded-lg px-2 py-2">
                                    <option value="">HH</option>
                                    <?php for($i=1; $i<=12; $i++) echo "<option value='".str_pad($i, 2, '0', STR_PAD_LEFT)."'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</option>"; ?>
                                </select>
                                <select name="birth_time_mm" required class="w-1/3 border rounded-lg px-2 py-2">
                                    <option value="">MM</option>
                                    <?php for($i=0; $i<=59; $i++) echo "<option value='".str_pad($i, 2, '0', STR_PAD_LEFT)."'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</option>"; ?>
                                </select>
                                <select name="birth_time_ampm" required class="w-1/3 border rounded-lg px-2 py-2">
                                    <option value="">AM/PM</option>
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Place *</label><input type="text" name="birth_place" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Native (परिवार का मूल स्थान) *</label><input type="text" name="native" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Cast (जाति) *</label>
                            <select name="cast" id="cast" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Cast</option>
                                <option value="Digambar Jain">Digambar Jain</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" name="custom_cast" id="custom_cast" placeholder="Please specify cast" class="w-full border rounded-lg px-4 py-2 mt-2 hidden">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Sub-Cast (उपजाति)</label>
                            <select name="subcast" id="subcast" class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Sub-Cast</option>
                                <option value="Khandelwal">Khandelwal</option>
                                <option value="Agrawal">Agrawal</option>
                                <option value="Oswal">Oswal</option>
                                <option value="Porwal">Porwal</option>
                                <option value="Golalare">Golalare</option>
                                <option value="Humad">Humad</option>
                                <option value="Bagherwal">Bagherwal</option>
                                <option value="Chaturth">Chaturth</option>
                                <option value="Pancham">Pancham</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                            <input type="text" name="custom_subcast" id="custom_subcast" placeholder="Please specify sub-cast" class="w-full border rounded-lg px-4 py-2 mt-2 hidden">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Gotra (गोत्र) *</label><input type="text" name="gotra" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mama Gotra (मामा का गोत्र) *</label><input type="text" name="mama_gotra" required class="w-full border rounded-lg px-4 py-2"></div>
                        
                        <!-- Manglik -->
                        <div><label class="block text-gray-700 font-medium mb-2">Manglik (मांगलिक) *</label>
                            <div class="flex gap-4"><label><input type="radio" name="manglik" value="yes" required> Yes / हाँ</label><label><input type="radio" name="manglik" value="no"> No / ना</label></div>
                        </div>
                        
                        <!-- Height Dropdown -->
                        <div><label class="block text-gray-700 font-medium mb-2">Height (ऊंचाई) *</label>
                            <select name="height" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Height</option>
                                <option>4 ft 8 inch</option><option>4 ft 9 inch</option><option>4 ft 10 inch</option><option>4 ft 11 inch</option>
                                <option>5 ft</option><option>5 ft 1 inch</option><option>5 ft 2 inch</option><option>5 ft 3 inch</option>
                                <option>5 ft 4 inch</option><option>5 ft 5 inch</option><option>5 ft 6 inch</option><option>5 ft 7 inch</option>
                                <option>5 ft 8 inch</option><option>5 ft 9 inch</option><option>5 ft 10 inch</option><option>5 ft 11 inch</option>
                                <option>6 ft</option><option>6 ft 1 inch</option><option>6 ft 2 inch</option><option>6 ft 3 inch</option>
                                <option>6 ft 4 inch</option><option>6 ft 5 inch</option>
                            </select>
                        </div>
                        
                        <!-- Weight Dropdown -->
                        <div><label class="block text-gray-700 font-medium mb-2">Weight *</label>
                            <select name="weight" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Weight (kg)</option>
                                <?php for($i=35; $i<=120; $i++) echo "<option>$i kg</option>"; ?>
                            </select>
                        </div>
                        
                        <!-- Permanent Address -->
                        <div><label class="block text-gray-700 font-medium mb-2">Permanent Full Address (स्थायी पता) *</label><textarea name="permanent_address" id="permanent_address" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Pin Code of Permanent Address *</label><input type="text" name="pin_code" pattern="[0-9]{4,6}" maxlength="6" minlength="4" title="Please enter a valid 4 to 6 digit pin code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2"></div>
                        
                        <!-- Same as Permanent Address Checkbox -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="same_as_permanent" name="same_as_permanent" value="1" class="mr-2 rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-gray-700 font-medium">Current Address is same as Permanent Address (वर्तमान पता स्थायी पता जैसा ही है)</span>
                            </label>
                        </div>
                        <div id="current_address_container"><label class="block text-gray-700 font-medium mb-2">Candidate Current Address (वर्तमान पता) *</label><textarea name="current_address" id="current_address" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>

                        <div><label class="block text-gray-700 font-medium mb-2">Higher Education *</label><input type="text" name="education" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Hobbies *</label><textarea name="hobbies" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Your Specific Preference for the Partner *</label><textarea name="partner_preference" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>
                        
                        <!-- Widow/Divorce -->
                        <div><label class="block text-gray-700 font-medium mb-2">Widow / Divorce *</label>
                            <select name="marital_status" required class="w-full border rounded-lg px-4 py-2">
                                <option>Not Applicable</option><option>Widow</option><option>Divorce</option>
                            </select>
                        </div>
                        
                        <!-- Handicapped -->
                        <div><label class="block text-gray-700 font-medium mb-2">Handicapped / Physical Deficiency *</label>
                            <div class="flex gap-4"><label><input type="radio" name="handicapped" value="yes" required> Yes</label><label><input type="radio" name="handicapped" value="no"> No</label></div>
                        </div>
                        
                        <!-- Language Known -->
                        <div><label class="block text-gray-700 font-medium mb-2">Language Known *</label>
                            <div class="grid grid-cols-2 gap-2"><label><input type="checkbox" name="languages[]" value="Gujarati"> Gujarati</label><label><input type="checkbox" name="languages[]" value="Hindi"> Hindi</label><label><input type="checkbox" name="languages[]" value="English"> English</label><label><input type="checkbox" name="languages[]" id="language_other_checkbox" value="Other"> Other</label></div>
                            <input type="text" name="other_language" id="other_language_input" placeholder="Specify other language" class="w-full border rounded-lg px-4 py-2 mt-2 hidden">
                        </div>
                        
                        <!-- Occupation, Income, Company, Designation grouped together -->
                        <div class="col-span-1 md:col-span-2 border-t border-dashed border-gray-200 pt-4 mt-2">
                            <h3 class="text-lg font-bold text-primary mb-3"><i class="fas fa-briefcase mr-2"></i>Candidate Occupation & Income Details</h3>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Occupation (व्यवसाय) *</label>
                            <div class="flex gap-4">
                                <label><input type="radio" name="occupation" value="Job" required> Job</label>
                                <label><input type="radio" name="occupation" value="Business"> Business</label>
                                <label><input type="radio" name="occupation" value="Other"> Other</label>
                            </div>
                            <input type="text" name="occupation_details" id="occupation_details" placeholder="Please specify occupation" class="w-full border rounded-lg px-4 py-2 mt-2 hidden">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Annual Income (वार्षिक आय) *</label><input type="number" name="annual_income" min="0" step="1" required placeholder="Yearly income amount (e.g., 500000)" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Company/Firm Name (Optional)</label><input type="text" name="company_name" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Designation (Optional)</label><input type="text" name="designation" class="w-full border rounded-lg px-4 py-2"></div>
                        <?php 
                        if (!empty($customFieldsByGroup['Section 2: Personal Details'])) {
                            foreach ($customFieldsByGroup['Section 2: Personal Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Family Details Section -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 3: Family Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-gray-700 font-medium mb-2">Father Name *</label><input type="text" name="father_name" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Father Mobile Number *</label>
                            <input type="tel" name="father_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2">
                            <p class="text-xs text-gray-500 mt-1">10 digits only number</p>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Father Income (Optional)</label><input type="number" name="father_income" min="0" step="1" placeholder="Optional" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Father Occupation *</label>
                            <select name="father_occupation" id="father_occupation" required class="w-full border rounded-lg px-4 py-2">
                                <option>Job</option><option>Business</option><option>Retired</option><option>Other</option>
                            </select>
                            <input type="text" name="father_occupation_details" id="father_occupation_details" placeholder="Please specify details" class="w-full border rounded-lg px-4 py-2 mt-2 hidden">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mother Name *</label><input type="text" name="mother_name" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mother Mobile Number (Optional)</label>
                            <input type="tel" name="mother_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border rounded-lg px-4 py-2">
                            <p class="text-xs text-gray-500 mt-1">10 digits only number</p>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mother Occupation (Optional)</label>
                            <select name="mother_occupation" id="mother_occupation" class="w-full border rounded-lg px-4 py-2">
                                <option value="House Wife">House Wife</option><option value="Job">Job</option><option value="Business">Business</option><option value="Other">Other</option>
                            </select>
                            <input type="text" name="mother_occupation_details" id="mother_occupation_details" placeholder="Please specify details" class="w-full border rounded-lg px-4 py-2 mt-2 hidden">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Brothers Married Count (Optional)</label>
                            <select name="brothers_married" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Brothers Unmarried Count (Optional)</label>
                            <select name="brothers_unmarried" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Total Brothers *</label>
                            <select name="brothers" required class="w-full border rounded-lg px-4 py-2">
                                <?php for($i=0;$i<=5;$i++) echo "<option>$i</option>"; ?>
                            </select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Married Count (Optional)</label>
                            <select name="sisters_married" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Unmarried Count (Optional)</label>
                            <select name="sisters_unmarried" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Total Sisters *</label>
                            <select name="sisters" required class="w-full border rounded-lg px-4 py-2"><?php for($i=0;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <?php 
                        if (!empty($customFieldsByGroup['Family Details'])) {
                            foreach ($customFieldsByGroup['Family Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Section 4: Temple Association Details -->
                <div class="mb-8 pb-4 border-b border-gray-200">
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
                            <input type="text" name="mandir_name" required class="w-full border rounded-lg px-4 py-2" placeholder="Shri Digambar Jain Mandir">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Temple Address (मंदिर का पता) *</label>
                            <textarea name="mandir_address" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Temple Pincode (मंदिर का पिनकोड) *</label>
                            <input type="text" name="mandir_pincode" pattern="[0-9]{4,6}" maxlength="6" minlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2">
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
                                        <input type="text" name="ref1_name" required class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Mobile Number *</label>
                                        <input type="tel" name="ref1_mobile" required pattern="[0-9]{10}" maxlength="10" minlength="10" title="Exactly 10 digit mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
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
                                        <input type="text" name="ref2_name" required class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Mobile Number *</label>
                                        <input type="tel" name="ref2_mobile" required pattern="[0-9]{10}" maxlength="10" minlength="10" title="Exactly 10 digit mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
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
                                <label class="block text-gray-700 font-medium mb-2">Candidate Photo <?= $is_edit ? '' : '*' ?> (Passport size photo, max 10MB)</label>
                                <?php if ($is_edit && !empty($current_user['profile_photo'])): ?>
                                    <div class="mb-2">
                                        <img src="image.php?file=<?= urlencode(str_replace('../', '', $current_user['profile_photo'])) ?>" class="w-24 h-24 object-cover border rounded" alt="Profile Photo">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="photo" accept="image/*" <?= $is_edit ? '' : 'required' ?> class="w-full border rounded-lg px-4 py-2">
                            </div>
                            
                            <?php if (!isset($coreFieldsSettings['family_photo']) || $coreFieldsSettings['family_photo']['is_visible']): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Family Photo <?= (isset($coreFieldsSettings['family_photo']) && $coreFieldsSettings['family_photo']['is_required'] && !$is_edit) ? '*' : '(Optional)' ?> (Max 10MB)</label>
                                <?php if ($is_edit && !empty($current_user['family_photo'])): ?>
                                    <div class="mb-2">
                                        <img src="image.php?file=<?= urlencode(str_replace('../', '', $current_user['family_photo'])) ?>" class="w-32 h-24 object-cover border rounded" alt="Family Photo">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="family_photo" accept="image/*" <?= (isset($coreFieldsSettings['family_photo']) && $coreFieldsSettings['family_photo']['is_required'] && !$is_edit) ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
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
                                        <label class="block text-gray-700 font-medium mb-2">Upload ID Proof <?= $is_edit ? '' : '*' ?> (Max 5MB)</label>
                                        <?php if ($is_edit && !empty($current_user['id_proof_path'])): ?>
                                            <div class="mb-2">
                                                <a href="image.php?file=<?= urlencode(str_replace('../', '', $current_user['id_proof_path'])) ?>" target="_blank" class="text-blue-500 underline text-sm">View Current ID Proof</a>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="id_proof_path" accept="image/*,.pdf" <?= $is_edit ? '' : 'required' ?> class="w-full border rounded-lg px-4 py-2">
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
                </div>
                </div>
                
                <!-- Documents & Payment -->
                <div class="mb-8 pb-4 border-b border-gray-200">
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
                                <?php if ($is_edit && !empty($current_user['payment_screenshot'])): ?>
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

                <!-- Additional Information Section -->
                <?php if (!empty($customFieldsByGroup['Additional Information'])): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-primary mb-4">Additional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($customFieldsByGroup['Additional Information'] as $f) echo renderCustomFieldHTML($f); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <button id="submitBtn" type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition disabled:opacity-75 disabled:cursor-not-allowed"><?= $is_edit ? 'Update Profile' : 'Register Now' ?></button>
            </form>
        </div>
    </div>
</section>

<script>
document.getElementById('registrationForm').addEventListener('submit', function(e) {
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
    const currentMobile = "<?= addslashes($current_user['mobile']) ?>";

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