<?php
session_start();
include 'includes/db.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get POST data
    $full_name = htmlspecialchars($_POST['full_name']);
    $mobile = $_POST['country_code'] . $_POST['mobile'];
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birth_date = $_POST['birth_date'];
    $birth_time = $_POST['birth_time'];
    $birth_place = htmlspecialchars($_POST['birth_place']);
    $native = htmlspecialchars($_POST['native']);
    $gotra = htmlspecialchars($_POST['gotra']);
    $mama_gotra = htmlspecialchars($_POST['mama_gotra'] ?? '');
    $manglik = $_POST['manglik'];
    $height = htmlspecialchars($_POST['height']);
    $weight = htmlspecialchars($_POST['weight']);
    $gender = $_POST['gender'];
    $permanent_address = htmlspecialchars($_POST['permanent_address']);
    $pin_code = htmlspecialchars($_POST['pin_code']);
    $current_address = htmlspecialchars($_POST['current_address']);
    $education = htmlspecialchars($_POST['education']);
    $hobbies = htmlspecialchars($_POST['hobbies']);
    $partner_preference = htmlspecialchars($_POST['partner_preference']);
    $monthly_income = htmlspecialchars($_POST['monthly_income']);
    $marital_status = htmlspecialchars($_POST['marital_status']);
    $handicapped = $_POST['handicapped'];
    $languages = isset($_POST['languages']) ? implode(',', $_POST['languages']) : '';
    $occupation = $_POST['occupation'];
    $company_name = htmlspecialchars($_POST['company_name'] ?? '');
    $designation = htmlspecialchars($_POST['designation'] ?? '');
    $father_name = htmlspecialchars($_POST['father_name']);
    $father_mobile = htmlspecialchars($_POST['father_mobile']);
    $father_income = htmlspecialchars($_POST['father_income']);
    $father_occupation = htmlspecialchars($_POST['father_occupation']);
    $mother_name = htmlspecialchars($_POST['mother_name']);
    $mother_mobile = htmlspecialchars($_POST['mother_mobile'] ?? '');
    $mother_occupation = htmlspecialchars($_POST['mother_occupation'] ?? '');
    $mother_occupation_details = htmlspecialchars($_POST['mother_occupation_details'] ?? '');
    $brothers = (int)$_POST['brothers'];
    $brothers_married = (int)($_POST['brothers_married'] ?? 0);
    $brothers_unmarried = (int)($_POST['brothers_unmarried'] ?? 0);
    $sisters = (int)$_POST['sisters'];
    $sisters_married = (int)($_POST['sisters_married'] ?? 0);
    $sisters_unmarried = (int)($_POST['sisters_unmarried'] ?? 0);
    $subcast = htmlspecialchars($_POST['subcast']);
    $custom_subcast = htmlspecialchars($_POST['custom_subcast'] ?? '');
    $mandir = htmlspecialchars($_POST['mandir']);
    $custom_mandir = htmlspecialchars($_POST['custom_mandir'] ?? '');
    $ref1_name = htmlspecialchars($_POST['ref1_name'] ?? '');
    $ref1_mobile = htmlspecialchars($_POST['ref1_mobile'] ?? '');
    $ref1_relation = htmlspecialchars($_POST['ref1_relation'] ?? '');
    $ref2_name = htmlspecialchars($_POST['ref2_name'] ?? '');
    $ref2_mobile = htmlspecialchars($_POST['ref2_mobile'] ?? '');
    $ref2_relation = htmlspecialchars($_POST['ref2_relation'] ?? '');

    $ref2_relation = htmlspecialchars($_POST['ref2_relation'] ?? '');

    // PHP Validations
    if (!preg_match('/^[0-9]{10}$/', $_POST['mobile'])) {
        $error = "Candidate mobile number must be exactly 10 digits.";
    } elseif (!preg_match('/^[0-9]{4,6}$/', $pin_code)) {
        $error = "Pin code must be 4 to 6 digits.";
    } elseif (!is_numeric($monthly_income) || $monthly_income < 0) {
        $error = "Candidate monthly income must be a valid positive amount.";
    } elseif (!is_numeric($father_income) || $father_income < 0) {
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

    try {
        $stmt = $pdo->prepare("INSERT INTO users (
            full_name, mobile, email, password, birth_date, birth_time, birth_place, native_place, gotra, mama_gotra, manglik,
            height, weight, gender, permanent_address, pin_code, current_address, higher_education, hobbies, partner_preference,
            monthly_income, marital_status, handicapped, languages, occupation, company_name, designation, father_name,
            father_mobile, father_income, father_occupation, mother_name, mother_mobile, mother_occupation,
            mother_occupation_details, brothers, brothers_married, brothers_unmarried, sisters, sisters_married,
            sisters_unmarried, subcast, custom_subcast, mandir, custom_mandir, ref1_name, ref1_mobile, ref1_relation,
            ref2_name, ref2_mobile, ref2_relation, profile_photo, family_photo, payment_screenshot, status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending'
        )");

        $stmt->execute([
            $full_name, $mobile, $email, $password, $birth_date, $birth_time, $birth_place, $native, $gotra, $mama_gotra, $manglik,
            $height, $weight, $gender, $permanent_address, $pin_code, $current_address, $education, $hobbies, $partner_preference,
            $monthly_income, $marital_status, $handicapped, $languages, $occupation, $company_name, $designation, $father_name,
            $father_mobile, $father_income, $father_occupation, $mother_name, $mother_mobile, $mother_occupation,
            $mother_occupation_details, $brothers, $brothers_married, $brothers_unmarried, $sisters, $sisters_married,
            $sisters_unmarried, $subcast, $custom_subcast, $mandir, $custom_mandir, $ref1_name, $ref1_mobile, $ref1_relation,
            $ref2_name, $ref2_mobile, $ref2_relation, $photo, $family_photo, $payment_screenshot
        ]);

        $user_id = $pdo->lastInsertId();
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $full_name;

        $success = "Registration successful! Welcome to the community.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Integrity constraint violation: duplicate email
            $error = "Email address is already registered.";
        } else {
            $error = "Registration failed: " . $e->getMessage();
        }
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
                    window.location.href = "my-profile.php";
                </script>
            <?php endif; ?>
            
            <form id="registrationForm" method="POST" action="" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-6 md:p-8" data-aos="fade-up" data-aos-delay="200">
                <!-- Section 1: Basic Information -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 1: Basic Information</h2>
                    
                    <!-- Are You Digambar Jain -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Are You Digambar Jain? (परिचय सम्मेलन सिर्फ दिगम्बर जैन के लिये है) *</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center"><input type="radio" name="is_digambar" value="yes" required class="mr-2"> Yes</label>
                            <label class="inline-flex items-center"><input type="radio" name="is_digambar" value="no" required class="mr-2"> No</label>
                        </div>
                    </div>
                    
                    <!-- Candidate Full Name -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Candidate Full Name (प्रत्याशी का नाम) *</label>
                        <input type="text" name="full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-primary">
                    </div>
                    
                    <!-- Country Code & Mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Country Code *</label>
                            <select name="country_code" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                <option value="+91">+91 (India)</option>
                                <option value="+1">+1 (USA)</option>
                                <option value="+44">+44 (UK)</option>
                                <option value="+61">+61 (Australia)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mobile Number (WhatsApp) *</label>
                            <input type="tel" name="mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                    </div>
                </div>
                
                <!-- Section 2: Personal Details -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 2: Personal Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Date *</label><input type="date" name="birth_date" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Time *</label><input type="time" name="birth_time" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Birth Place *</label><input type="text" name="birth_place" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Native (परिवार का मूल स्थान) *</label><input type="text" name="native" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Gotra (गोत्र) *</label><input type="text" name="gotra" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Mama Gotra (मामा का गोत्र) (Optional)</label><input type="text" name="mama_gotra" class="w-full border rounded-lg px-4 py-2"></div>
                        
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
                        <div><label class="block text-gray-700 font-medium mb-2">Email *</label><input type="email" name="email" required class="w-full border rounded-lg px-4 py-2"></div>
                        
                        <!-- Password -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Password *</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required class="w-full border rounded-lg px-4 py-2 pr-10">
                                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="togglePassword('password', 'eye_icon_pass')">
                                    <svg id="eye_icon_pass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Confirm Password *</label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirm_password" required class="w-full border rounded-lg px-4 py-2 pr-10">
                                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="togglePassword('confirm_password', 'eye_icon_conf')">
                                    <svg id="eye_icon_conf" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

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
                    </div>
                </div>
                
                <!-- Family Details Section -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Family Details</h2>
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
                    </div>
                </div>
                
                <!-- Section 4: Mandir Verification Details -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Section 4: Mandir Verification Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Subcast Select -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Subcast (उपजाति) *</label>
                            <select name="subcast" id="subcast" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Subcast</option>
                                <option value="Khandelwal">Khandelwal (खण्डेलवाल)</option>
                                <option value="Parwar">Parwar (परवार)</option>
                                <option value="Golapurva">Golapurva (गोलापूर्व)</option>
                                <option value="Golalare">Golalare (गोलालाड़े)</option>
                                <option value="Saitwal">Saitwal (सैतवाल)</option>
                                <option value="Chaturtha">Chaturtha (चतुर्थ)</option>
                                <option value="Panchama">Panchama (पंचम)</option>
                                <option value="Lad">Lad (लाड़)</option>
                                <option value="Bagherwal">Bagherwal (बाघेरवाल)</option>
                                <option value="Humbad">Humbad (हुम्बड़)</option>
                                <option value="Narsingpura">Narsingpura (नरसिंगपुरा)</option>
                                <option value="Jaiswal">Jaiswal (जायसवाल)</option>
                                <option value="Padmavati Purwal">Padmavati Purwal (पद्मावती पुरवाल)</option>
                                <option value="Other">Other Subcast (अन्य उपजाति)</option>
                            </select>
                            
                            <!-- Custom Subcast Text Input (Hidden initially) -->
                            <div id="customSubcastContainer" class="mt-2 hidden">
                                <label class="block text-xs text-gray-500 font-semibold mb-1">Please Specify Subcast *</label>
                                <input type="text" name="custom_subcast" id="custom_subcast" class="w-full border rounded-lg px-4 py-2" placeholder="Enter your subcast">
                            </div>
                        </div>

                        <!-- Mandir Select -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Select Your Mandir (मंदिर) *</label>
                            <select name="mandir" id="mandir" required class="w-full border rounded-lg px-4 py-2">
                                <option value="">Select Mandir</option>
                                <option value="Shri Digambar Jain Lal Mandir, Chandni Chowk, Delhi">Shri Digambar Jain Lal Mandir, Chandni Chowk, Delhi</option>
                                <option value="Shri Digambar Jain Atishaya Kshetra, Tijara, Rajasthan">Shri Digambar Jain Atishaya Kshetra, Tijara, Rajasthan</option>
                                <option value="Shri Digambar Jain Siddhakshetra, Sonagiri, Madhya Pradesh">Shri Digambar Jain Siddhakshetra, Sonagiri, Madhya Pradesh</option>
                                <option value="Shri Digambar Jain Atishaya Kshetra, Padampura, Rajasthan">Shri Digambar Jain Atishaya Kshetra, Padampura, Rajasthan</option>
                                <option value="Shravanabelagola Gommateshwara Mandir, Karnataka">Shravanabelagola Gommateshwara Mandir, Karnataka</option>
                                <option value="Shri Digambar Jain Siddhakshetra, Kundalpur, Madhya Pradesh">Shri Digambar Jain Siddhakshetra, Kundalpur, Madhya Pradesh</option>
                                <option value="Shri Sammed Shikharji Digambar Jain Mandir, Jharkhand">Shri Sammed Shikharji Digambar Jain Mandir, Jharkhand</option>
                                <option value="Shri Digambar Jain Mandir, Bada Mandir, Indore">Shri Digambar Jain Mandir, Bada Mandir, Indore</option>
                                <option value="Shri Digambar Jain Atishaya Kshetra, Mahavirji, Rajasthan">Shri Digambar Jain Atishaya Kshetra, Mahavirji, Rajasthan</option>
                                <option value="Other Mandir">Other Mandir (अन्य मंदिर)</option>
                            </select>

                            <!-- Custom Mandir Text Input (Hidden initially) -->
                            <div id="customMandirContainer" class="mt-2 hidden">
                                <label class="block text-xs text-gray-500 font-semibold mb-1">Please Specify Mandir Name & Location *</label>
                                <input type="text" name="custom_mandir" id="custom_mandir" class="w-full border rounded-lg px-4 py-2" placeholder="e.g., Shri Digambar Jain Mandir, Sector 4, Rohini, Delhi">
                            </div>
                        </div>
                    </div>

                    <!-- Reference Persons (Hidden initially, dynamic slide down) -->
                    <div id="referencePersonsContainer" class="mt-6 border-t border-dashed border-gray-200 pt-6 hidden opacity-0 transition-all duration-500 transform translate-y-2">
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
                                        <input type="text" name="ref1_name" id="ref1_name" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Mobile Number *</label>
                                        <input type="tel" name="ref1_mobile" id="ref1_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Exactly 10 digit mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Relationship (Optional)</label>
                                        <select name="ref1_relation" id="ref1_relation" class="w-full border bg-white rounded-lg px-3 py-2 text-sm">
                                            <option value="">Select Relationship</option>
                                            <option value="Panditji / Temple Priest">Panditji / Temple Priest (पंडितजी)</option>
                                            <option value="Mandir Trustee / Committee Member">Mandir Trustee / Committee Member (ट्रस्टी)</option>
                                            <option value="Community Leader">Community Leader (समाज श्रेष्ठी)</option>
                                            <option value="Family Friend">Family Friend (पारिवारिक मित्र)</option>
                                            <option value="Relative">Relative (रिश्तेदार)</option>
                                            <option value="Neighbor">Neighbor (पड़ोसी)</option>
                                            <option value="Other">Other (अन्य)</option>
                                        </select>
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
                                        <input type="text" name="ref2_name" id="ref2_name" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Mobile Number *</label>
                                        <input type="tel" name="ref2_mobile" id="ref2_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Exactly 10 digit mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border bg-white rounded-lg px-3 py-2 text-sm focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 font-medium mb-1">Relationship (Optional)</label>
                                        <select name="ref2_relation" id="ref2_relation" class="w-full border bg-white rounded-lg px-3 py-2 text-sm">
                                            <option value="">Select Relationship</option>
                                            <option value="Panditji / Temple Priest">Panditji / Temple Priest (पंडितजी)</option>
                                            <option value="Mandir Trustee / Committee Member">Mandir Trustee / Committee Member (ट्रस्टी)</option>
                                            <option value="Community Leader">Community Leader (समाज श्रेष्ठी)</option>
                                            <option value="Family Friend">Family Friend (पारिवारिक मित्र)</option>
                                            <option value="Relative">Relative (रिश्तेदार)</option>
                                            <option value="Neighbor">Neighbor (पड़ोसी)</option>
                                            <option value="Other">Other (अन्य)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos Section -->
                <div class="mb-8 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-primary mb-4">Photos</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-gray-700 font-medium mb-2">Candidate Photo * (Passport size photo, max 10MB)</label><input type="file" name="photo" accept="image/*" required class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Family Photo * (Max 10MB)</label><input type="file" name="family_photo" accept="image/*" required class="w-full border rounded-lg px-4 py-2"></div>
                    </div>
                </div>
                
                <!-- Documents & Payment -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-primary mb-4">Documents & Payment</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="text-center p-4 bg-light rounded-lg">
                            <p class="font-semibold mb-2">Pay Rs. 1000/- (Candidate + 2 persons allowed)</p>
                            <p class="text-sm text-gray-600">Kindly mention Mobile No. in Payment Remarks</p>
                            <img src="https://via.placeholder.com/200x200?text=QR+Code" alt="Payment QR" class="mx-auto mt-2 w-48">
                        </div>
                        <div><label class="block text-gray-700 font-medium mb-2">Payment Screenshot (Transaction ID) *</label><input type="file" name="payment_screenshot" accept="image/*" required class="w-full border rounded-lg px-4 py-2"></div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition">Register Now</button>
            </form>
        </div>
    </div>
</section>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
    } else {
        input.type = 'password';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
    }
}

document.querySelectorAll('input[name="is_digambar"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const formElements = document.querySelectorAll('#registrationForm input:not([name="is_digambar"]), #registrationForm select, #registrationForm textarea, #registrationForm button[type="submit"]');
        if (this.value === 'no') {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Sorry, this registration is strictly for Digambar Jains only.'});
            formElements.forEach(el => el.disabled = true);
            
            // visually dim the form to indicate it's disabled
            document.getElementById('registrationForm').classList.add('opacity-50');
        } else {
            formElements.forEach(el => el.disabled = false);
            document.getElementById('registrationForm').classList.remove('opacity-50');
        }
    });
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

document.getElementById('subcast')?.addEventListener('change', function() {
    const customSubcast = document.getElementById('customSubcastContainer');
    const input = document.getElementById('custom_subcast');
    if (this.value === 'Other') {
        customSubcast.classList.remove('hidden');
        input.required = true;
    } else {
        customSubcast.classList.add('hidden');
        input.required = false;
        input.value = '';
    }
    checkReferenceSection();
});

document.getElementById('mandir')?.addEventListener('change', function() {
    const customMandir = document.getElementById('customMandirContainer');
    const input = document.getElementById('custom_mandir');
    if (this.value === 'Other Mandir') {
        customMandir.classList.remove('hidden');
        input.required = true;
    } else {
        customMandir.classList.add('hidden');
        input.required = false;
        input.value = '';
    }
    checkReferenceSection();
});

function checkReferenceSection() {
    const subcast = document.getElementById('subcast').value;
    const mandir = document.getElementById('mandir').value;
    const refContainer = document.getElementById('referencePersonsContainer');
    const refInputs = [
        document.getElementById('ref1_name'),
        document.getElementById('ref1_mobile'),
        document.getElementById('ref2_name'),
        document.getElementById('ref2_mobile')
    ];

    if (subcast && mandir) {
        refContainer.classList.remove('hidden');
        // Trigger reflow for transition
        refContainer.offsetHeight;
        refContainer.classList.remove('opacity-0', 'translate-y-2');
        refContainer.classList.add('opacity-100', 'translate-y-0');
        refInputs.forEach(el => el.required = true);
    } else {
        refContainer.classList.add('opacity-0', 'translate-y-2');
        refContainer.classList.remove('opacity-100', 'translate-y-0');
        // Hide after animation finishes
        setTimeout(() => {
            if (!document.getElementById('subcast').value || !document.getElementById('mandir').value) {
                refContainer.classList.add('hidden');
            }
        }, 500);
        refInputs.forEach(el => {
            el.required = false;
            el.value = '';
        });
        document.getElementById('ref1_relation').value = '';
        document.getElementById('ref2_relation').value = '';
    }
}

document.getElementById('registrationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Digambar Jain validation
    const isDigambar = document.querySelector('input[name="is_digambar"]:checked')?.value;
    if (isDigambar === 'no') {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Sorry, this registration is strictly for Digambar Jains only.'});
        return;
    }

    // Password validation
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    if (password !== confirmPassword) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Password and Confirm Password do not match.'});
        return;
    }

    // Phone number validations (basic 7-15 digits check)
    const mobile = document.querySelector('input[name="mobile"]').value;
    if (!/^\d{7,15}$/.test(mobile)) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mobile number (7 to 15 digits).'});
        return;
    }

    const fatherMobile = document.querySelector('input[name="father_mobile"]').value;
    if (!/^\d{7,15}$/.test(fatherMobile)) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid father mobile number.'});
        return;
    }

    const motherMobile = document.querySelector('input[name="mother_mobile"]').value;
    if (motherMobile && !/^\d{7,15}$/.test(motherMobile)) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mother mobile number.'});
        return;
    }

    // Mandir Verification Details & Reference validation
    const subcastVal = document.getElementById('subcast').value;
    const mandirVal = document.getElementById('mandir').value;
    if (subcastVal && mandirVal) {
        const ref1Name = document.getElementById('ref1_name').value.trim();
        const ref1Mobile = document.getElementById('ref1_mobile').value.trim();
        const ref2Name = document.getElementById('ref2_name').value.trim();
        const ref2Mobile = document.getElementById('ref2_mobile').value.trim();

        if (!ref1Name || !ref1Mobile || !ref2Name || !ref2Mobile) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Please fill out both Reference Persons\' Name and Mobile number.'});
            return;
        }

        if (!/^\d{7,15}$/.test(ref1Mobile)) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mobile number (7 to 15 digits) for Reference Person 1.'});
            return;
        }

        if (!/^\d{7,15}$/.test(ref2Mobile)) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Please enter a valid mobile number (7 to 15 digits) for Reference Person 2.'});
            return;
        }

        // Duplication and sanity checks
        if (ref1Mobile === ref2Mobile) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Reference Person 1 and Reference Person 2 must have different mobile numbers.'});
            return;
        }

        if (ref1Mobile === mobile || ref2Mobile === mobile) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Reference mobile number cannot be the same as the candidate\'s mobile number.'});
            return;
        }

        if (ref1Mobile === fatherMobile || ref2Mobile === fatherMobile) {
            Swal.fire({icon: 'warning', title: 'Attention', text: 'Reference mobile number cannot be the same as the father\'s mobile number.'});
            return;
        }
    }

    // File size validations (10MB = 10 * 1024 * 1024 bytes)
    const maxFileSize = 10 * 1024 * 1024;
    
    const photo = document.querySelector('input[name="photo"]').files[0];
    if (photo && photo.size > maxFileSize) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Candidate Photo must be less than 10MB.'});
        return;
    }

    const familyPhoto = document.querySelector('input[name="family_photo"]').files[0];
    if (familyPhoto && familyPhoto.size > maxFileSize) {
        Swal.fire({icon: 'warning', title: 'Attention', text: 'Family Photo must be less than 10MB.'});
        return;
    }

    const paymentScreenshot = document.querySelector('input[name="payment_screenshot"]').files[0];
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
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const input = form.elements[key];
                if (input) {
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