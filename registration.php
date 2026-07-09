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

if (!$current_user || $current_user['status'] !== 'account_approved') {
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
    $permanent_address = htmlspecialchars($_POST['permanent_address'] ?? '');
    $pin_code = htmlspecialchars($_POST['pin_code'] ?? '');
    $current_address = htmlspecialchars($_POST['current_address'] ?? '');
    $education = htmlspecialchars($_POST['education'] ?? '');
    $hobbies = htmlspecialchars($_POST['hobbies'] ?? '');
    $partner_preference = htmlspecialchars($_POST['partner_preference'] ?? '');
    $monthly_income = htmlspecialchars($_POST['monthly_income'] ?? '');
    $marital_status = htmlspecialchars($_POST['marital_status'] ?? '');
    $handicapped = $_POST['handicapped'] ?? '';
    $languages = isset($_POST['languages']) ? implode(',', $_POST['languages']) : '';
    $occupation = $_POST['occupation'] ?? '';
    $company_name = htmlspecialchars($_POST['company_name'] ?? '');
    $designation = htmlspecialchars($_POST['designation'] ?? '');
    $father_name = htmlspecialchars($_POST['father_name'] ?? '');
    $father_mobile = htmlspecialchars($_POST['father_mobile'] ?? '');
    $father_income = htmlspecialchars($_POST['father_income'] ?? '');
    $father_occupation = htmlspecialchars($_POST['father_occupation'] ?? '');
    $mother_name = htmlspecialchars($_POST['mother_name'] ?? '');
    $mother_mobile = htmlspecialchars($_POST['mother_mobile'] ?? '');
    $mother_occupation = htmlspecialchars($_POST['mother_occupation'] ?? '');
    $mother_occupation_details = htmlspecialchars($_POST['mother_occupation_details'] ?? '');
    $brothers = (int)($_POST['brothers'] ?? 0);
    $brothers_married = (int)($_POST['brothers_married'] ?? 0);
    $brothers_unmarried = (int)($_POST['brothers_unmarried'] ?? 0);
    $sisters = (int)($_POST['sisters'] ?? 0);
    $sisters_married = (int)($_POST['sisters_married'] ?? 0);
    $sisters_unmarried = (int)($_POST['sisters_unmarried'] ?? 0);
    $cast = htmlspecialchars($_POST['cast'] ?? '');
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
    if ($pin_code && !preg_match('/^[0-9]{4,6}$/', $pin_code)) {
        $error = "Pin code must be 4 to 6 digits.";
    } elseif ($monthly_income !== '' && (!is_numeric($monthly_income) || $monthly_income < 0)) {
        $error = "Candidate monthly income must be a valid positive amount.";
    } elseif ($father_income !== '' && (!is_numeric($father_income) || $father_income < 0)) {
        $error = "Father monthly income must be a valid positive amount.";
    } elseif ($father_mobile && !preg_match('/^[0-9]{10}$/', $father_mobile)) {
        $error = "Father mobile number must be exactly 10 digits.";
    } elseif ($mother_mobile && !preg_match('/^[0-9]{10}$/', $mother_mobile)) {
        $error = "Mother mobile number must be exactly 10 digits.";
    } elseif ($ref1_mobile && !preg_match('/^[0-9]{10}$/', $ref1_mobile)) {
        $error = "Reference 1 mobile number must be exactly 10 digits.";
    } elseif ($ref2_mobile && !preg_match('/^[0-9]{10}$/', $ref2_mobile)) {
        $error = "Reference 2 mobile number must be exactly 10 digits.";
    } elseif ($ref1_mobile && $ref1_mobile === $ref2_mobile) {
        $error = "Reference 1 and 2 mobile numbers must be different.";
    }

    if (!$error) {
        // Handle File Uploads
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $photo = '';
    $family_photo = '';
    $payment_screenshot = '';

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
    
    $id_proof_path = '';
    if (isset($_FILES['id_proof_path']) && $_FILES['id_proof_path']['error'] === UPLOAD_ERR_OK) {
        $id_proof_path = $upload_dir . time() . '_idproof_' . basename($_FILES['id_proof_path']['name']);
        move_uploaded_file($_FILES['id_proof_path']['tmp_name'], $id_proof_path);
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET 
            cast=?, birth_date=?, birth_time=?, birth_place=?, native_place=?, gotra=?, mama_gotra=?, manglik=?,
            height=?, weight=?, gender=?, permanent_address=?, pin_code=?, current_address=?, higher_education=?, hobbies=?, partner_preference=?,
            monthly_income=?, marital_status=?, handicapped=?, languages=?, occupation=?, company_name=?, designation=?, father_name=?,
            father_mobile=?, father_income=?, father_occupation=?, mother_name=?, mother_mobile=?, mother_occupation=?,
            mother_occupation_details=?, brothers=?, brothers_married=?, brothers_unmarried=?, sisters=?, sisters_married=?,
            sisters_unmarried=?, subcast=?, custom_subcast=?, mandir=?, custom_mandir=?, ref1_name=?, ref1_mobile=?, ref1_relation=?,
            ref2_name=?, ref2_mobile=?, ref2_relation=?, profile_photo=?, family_photo=?, payment_screenshot=?, profile_photo_drive_url=?, payment_proof_drive_url=?, status='pending',
            filled_by=?, id_proof_type=?, id_proof_path=?, mandir_name=?, mandir_address=?, mandir_pincode=?
            WHERE id=?
        ");

        $stmt->execute([
            $cast, $birth_date, $birth_time, $birth_place, $native, $gotra, $mama_gotra, $manglik,
            $height, $weight, $gender, $permanent_address, $pin_code, $current_address, $education, $hobbies, $partner_preference,
            $monthly_income, $marital_status, $handicapped, $languages, $occupation, $company_name, $designation, $father_name,
            $father_mobile, $father_income, $father_occupation, $mother_name, $mother_mobile, $mother_occupation,
            $mother_occupation_details, $brothers, $brothers_married, $brothers_unmarried, $sisters, $sisters_married,
            $sisters_unmarried, $subcast, $custom_subcast, $mandir, $custom_mandir, $ref1_name, $ref1_mobile, $ref1_relation,
            $ref2_name, $ref2_mobile, $ref2_relation, $photo, $family_photo, $payment_screenshot, $profile_photo_drive_url, $payment_proof_drive_url,
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
            $subject = "Profile Submitted for Approval";
            $body = "<h2>Hello " . htmlspecialchars($full_name) . "</h2><p>Your profile has been successfully submitted and is currently pending approval by the admin. We will notify you once it is approved.</p>";
            $mailer->send($userEmail, $subject, $body);
            
            // Notify Admin
            $adminSubject = "New Profile Registration";
            $adminBody = "<h2>New Profile Submitted</h2><p>A new profile for <b>" . htmlspecialchars($full_name) . "</b> has been submitted and is pending approval.</p>";
            $mailer->send('help@digambarjainparichay.com', $adminSubject, $adminBody);
        }

        $success = "Registration successful! Your profile has been sent for approval.";
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
            <h1 class="text-3xl md:text-4xl font-bold text-center text-dark mb-4" data-aos="fade-up">Registration Form</h1>
            <p class="text-center text-gray-600 mb-8" data-aos="fade-up" data-aos-delay="100">Join the most trusted Digambar Jain Matrimony platform</p>
            
            <?php if ($success): ?>
                <script>
                    sessionStorage.removeItem("registrationFormData");
                    window.location.href = "waiting-approval.php";
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
                    
                    <!-- Who is Filling -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Who is filling this form? *</label>
                        <select name="filled_by" required class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                            <option value="">Select Option</option>
                            <option value="Candidate">Candidate</option>
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Brother">Brother</option>
                            <option value="Sister">Sister</option>
                            <option value="Guardian">Guardian</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Candidate Full Name -->
                    <?php if (!isset($coreFieldsSettings['full_name']) || $coreFieldsSettings['full_name']['is_visible']): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Candidate Full Name (प्रत्याशी का नाम) *</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($current_user['full_name']) ?>" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Country Code & Mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <?php if (!isset($coreFieldsSettings['mobile']) || $coreFieldsSettings['mobile']['is_visible']): ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                            <input type="tel" name="mobile" value="<?= htmlspecialchars($current_user['mobile']) ?>" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                        <?php endif; ?>
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
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Date *</label><input type="date" name="birth_date" required class="w-full border rounded-lg px-4 py-2"></div>
                        
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
                            <select name="cast" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Cast</option>
                                <option value="Digambar Jain">Digambar Jain</option>
                                <option value="Other">Other</option>
                            </select>
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
                        
                        <!-- Gender -->
                        <div><label class="block text-gray-700 font-medium mb-2">Gender *</label>
                            <div class="flex gap-4"><label><input type="radio" name="gender" value="male" required> Male</label><label><input type="radio" name="gender" value="female"> Female</label></div>
                        </div>
                        
                        <div><label class="block text-gray-700 font-medium mb-2">Permanent Full Address *</label><textarea name="permanent_address" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Pin Code of Permanent Address *</label><input type="text" name="pin_code" pattern="[0-9]{4,6}" maxlength="6" minlength="4" title="Please enter a valid 4 to 6 digit pin code" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Current Address *</label><textarea name="current_address" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Email *</label><input type="email" name="email" value="<?= htmlspecialchars($current_user['email']) ?>" readonly class="w-full border rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed"></div>

                        <div><label class="block text-gray-700 font-medium mb-2">Higher Education *</label><input type="text" name="education" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Hobbies *</label><textarea name="hobbies" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Your Specific Preference for the Partner *</label><textarea name="partner_preference" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Monthly Income *</label><input type="number" name="monthly_income" min="0" step="1" required placeholder="Only Amount (e.g., 100000)" class="w-full border rounded-lg px-4 py-2"></div>
                        
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
                            <div class="grid grid-cols-2 gap-2"><label><input type="checkbox" name="languages[]" value="Gujarati"> Gujarati</label><label><input type="checkbox" name="languages[]" value="Hindi"> Hindi</label><label><input type="checkbox" name="languages[]" value="English"> English</label><label><input type="checkbox" name="languages[]" value="Other"> Other</label></div>
                        </div>
                        
                        <!-- Occupation -->
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Occupation *</label>
                            <div class="flex gap-4"><label><input type="radio" name="occupation" value="Job" required> Job</label><label><input type="radio" name="occupation" value="Business"> Business</label><label><input type="radio" name="occupation" value="Other"> Other</label></div>
                        </div>
                        
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
                        <div><label class="block text-gray-700 font-medium mb-2">Father Mobile Number *</label><input type="tel" name="father_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Father Monthly Income *</label><input type="number" name="father_income" min="0" step="1" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Father Occupation *</label>
                            <select name="father_occupation" required class="w-full border rounded-lg px-4 py-2">
                                <option>Job</option><option>Business</option><option>Retired</option><option>Other</option>
                            </select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mother Name *</label><input type="text" name="mother_name" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mother Mobile Number (Optional)</label><input type="tel" name="mother_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mother Occupation (Optional)</label>
                            <select name="mother_occupation" id="mother_occupation" class="w-full border rounded-lg px-4 py-2">
                                <option value="House Wife">House Wife</option><option value="Job">Job</option><option value="Business">Business</option><option value="Other">Other</option>
                            </select>
                            <input type="text" name="mother_occupation_details" id="mother_occupation_details" placeholder="Please specify details" class="w-full border rounded-lg px-4 py-2 mt-2 hidden">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Brothers *</label>
                            <select name="brothers" required class="w-full border rounded-lg px-4 py-2">
                                <?php for($i=0;$i<=5;$i++) echo "<option>$i</option>"; ?>
                            </select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Brothers Married Count (Optional)</label>
                            <select name="brothers_married" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Brothers Unmarried Count (Optional)</label>
                            <select name="brothers_unmarried" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Sisters *</label>
                            <select name="sisters" required class="w-full border rounded-lg px-4 py-2"><?php for($i=0;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Married Count (Optional)</label>
                            <select name="sisters_married" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Unmarried Count (Optional)</label>
                            <select name="sisters_unmarried" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <?php 
                        if (!empty($customFieldsByGroup['Family Details'])) {
                            foreach ($customFieldsByGroup['Family Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Section 4: Mandir Verification Details -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 4: Mandir Verification Details</h2>
                    
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
                            <label class="block text-gray-700 font-medium mb-2">Mandir Name (मंदिर का नाम) *</label>
                            <input type="text" name="mandir_name" required class="w-full border rounded-lg px-4 py-2" placeholder="Shri Digambar Jain Mandir">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mandir Address *</label>
                            <textarea name="mandir_address" required rows="2" class="w-full border rounded-lg px-4 py-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mandir Pincode *</label>
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
                    <?php if (
                        (isset($coreFieldsSettings['profile_photo']) && $coreFieldsSettings['profile_photo']['is_visible']) || 
                        (isset($coreFieldsSettings['family_photo']) && $coreFieldsSettings['family_photo']['is_visible']) || 
                        (isset($coreFieldsSettings['profile_photo_drive_url']) && $coreFieldsSettings['profile_photo_drive_url']['is_visible'])
                    ): ?>
                    <div class="mt-6 border-t border-dashed border-gray-200 pt-6">
                        <h3 class="text-lg font-bold text-primary mb-4">Photos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if (isset($coreFieldsSettings['profile_photo']) && $coreFieldsSettings['profile_photo']['is_visible']): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Candidate Photo <?= $coreFieldsSettings['profile_photo']['is_required'] ? '*' : '' ?> (Passport size photo, max 10MB)</label>
                                <input type="file" name="photo" accept="image/*" <?= $coreFieldsSettings['profile_photo']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($coreFieldsSettings['family_photo']) && $coreFieldsSettings['family_photo']['is_visible']): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Family Photo <?= $coreFieldsSettings['family_photo']['is_required'] ? '*' : '' ?> (Max 10MB)</label>
                                <input type="file" name="family_photo" accept="image/*" <?= $coreFieldsSettings['family_photo']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                            </div>
                            <?php endif; ?>

                            <?php if (isset($coreFieldsSettings['profile_photo_drive_url']) && $coreFieldsSettings['profile_photo_drive_url']['is_visible']): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Profile Photo Drive URL <?= $coreFieldsSettings['profile_photo_drive_url']['is_required'] ? '*' : '' ?></label>
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
                                        <label class="block text-gray-700 font-medium mb-2">Upload ID Proof * (Max 5MB)</label>
                                        <input type="file" name="id_proof_path" accept="image/*,.pdf" required class="w-full border rounded-lg px-4 py-2">
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
                    <?php endif; ?>
                </div>
                </div>
                
                <!-- Documents & Payment -->
                <?php if (
                    (isset($coreFieldsSettings['payment_screenshot']) && $coreFieldsSettings['payment_screenshot']['is_visible']) || 
                    (isset($coreFieldsSettings['payment_proof_drive_url']) && $coreFieldsSettings['payment_proof_drive_url']['is_visible'])
                ): ?>
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Documents & Payment</h2>
                    <div class="grid grid-cols-1 gap-4">

                        <?php if (isset($coreFieldsSettings['payment_screenshot']) && $coreFieldsSettings['payment_screenshot']['is_visible']): ?>
                        <div id="payment_screenshot_container" class="hidden">
                            <label class="block text-gray-700 font-medium mb-2">Payment Screenshot (Transaction ID) *</label>
                            <input type="file" name="payment_screenshot" id="payment_screenshot" accept="image/*" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <?php endif; ?>

                        <?php if (isset($coreFieldsSettings['payment_proof_drive_url']) && $coreFieldsSettings['payment_proof_drive_url']['is_visible']): ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Payment Proof Drive URL <?= $coreFieldsSettings['payment_proof_drive_url']['is_required'] ? '*' : '' ?></label>
                            <input type="url" name="payment_proof_drive_url" <?= $coreFieldsSettings['payment_proof_drive_url']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <?php endif; ?>
                        <?php 
                        if (!empty($customFieldsByGroup['Documents & Payment'])) {
                            foreach ($customFieldsByGroup['Documents & Payment'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Additional Information Section -->
                <?php if (!empty($customFieldsByGroup['Additional Information'])): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-primary mb-4">Additional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($customFieldsByGroup['Additional Information'] as $f) echo renderCustomFieldHTML($f); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition">Register Now</button>
            </form>
        </div>
    </div>
</section>

<script>
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

document.getElementById('subcast')?.addEventListener('change', function() {
    const custom = document.getElementById('custom_subcast');
    if (this.value === 'Other') {
        custom.classList.remove('hidden');
        custom.required = true;
    } else {
        custom.classList.add('hidden');
        custom.required = false;
        custom.value = '';
    }
});

document.getElementById('mother_occupation')?.addEventListener('change', function(e) {
    const detailsInput = document.getElementById('mother_occupation_details');
    if (this.value !== 'House Wife') {
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
                        input.value = data[key];
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
});
</script>

<?php include 'includes/footer.php'; ?>