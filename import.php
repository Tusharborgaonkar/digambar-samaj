<?php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'digambar-samaj';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

$csvFile = 'C:\xampp\htdocs\digambar-jain-samaj\digambar-samaj\परिचय सम्मेलन 2025-26 फोर्म Parichay Sammelan 2025-26 Form (Responses) - Form Responses 1 (12).csv';

if (!file_exists($csvFile)) {
    die("CSV file not found: " . $csvFile);
}

$handle = fopen($csvFile, "r");
if ($handle !== FALSE) {
    // skip first row (header)
    fgetcsv($handle);
    
    $stmt = $pdo->prepare("INSERT INTO members (
        full_name, country_code, mobile_number, birth_date, birth_time, birth_place, native, gotra, mama_gotra,
        manglik, height_cm, weight_kg, gender, permanent_address, permanent_pin_code, current_address, email,
        higher_education, hobbies, partner_preferences, monthly_income, widow_divorce, handicapped_physical_deficiency,
        languages_known, occupation, company_name, designation, father_name, father_mobile, father_monthly_income,
        father_occupation, mother_name, mother_mobile, mother_occupation, brothers_total, brothers_married,
        brothers_unmarried, sisters_total, sisters_married, sisters_unmarried, profile_photo_path
    ) VALUES (
        :full_name, :country_code, :mobile_number, :birth_date, :birth_time, :birth_place, :native, :gotra, :mama_gotra,
        :manglik, :height_cm, :weight_kg, :gender, :permanent_address, :permanent_pin_code, :current_address, :email,
        :higher_education, :hobbies, :partner_preferences, :monthly_income, :widow_divorce, :handicapped_physical_deficiency,
        :languages_known, :occupation, :company_name, :designation, :father_name, :father_mobile, :father_monthly_income,
        :father_occupation, :mother_name, :mother_mobile, :mother_occupation, :brothers_total, :brothers_married,
        :brothers_unmarried, :sisters_total, :sisters_married, :sisters_unmarried, :profile_photo_path
    )");

    $count = 0;
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        if(count($data) < 42) continue; // Skip bad rows

        $fullName = trim($data[2]);
        if(empty($fullName)) continue; // skip empty rows

        $countryCode = trim(str_replace('+', '', $data[3]));
        $mobileNumber = trim($data[4]);
        
        // Date mapping M/D/YYYY to YYYY-MM-DD
        $birthDateRaw = trim($data[5]);
        $birthDate = null;
        if (!empty($birthDateRaw)) {
            $ts = strtotime($birthDateRaw);
            if ($ts !== false) $birthDate = date('Y-m-d', $ts);
        }

        // Time mapping H:MM:SS A to HH:MM:SS
        $birthTimeRaw = trim($data[6]);
        $birthTime = null;
        if (!empty($birthTimeRaw)) {
            $ts = strtotime($birthTimeRaw);
            if ($ts !== false) $birthTime = date('H:i:s', $ts);
        }

        $birthPlace = trim($data[7]);
        $native = trim($data[8]);
        $gotra = trim($data[9]);
        $mamaGotra = trim($data[10]);

        $manglikRaw = strtolower(trim($data[11]));
        $manglik = (strpos($manglikRaw, 'yes') !== false || strpos($manglikRaw, 'हाँ') !== false) ? 'yes' : 'no';

        // Height mapping
        $heightRaw = trim($data[12]);
        $heightCm = null;
        if (preg_match('/(\d+)\s*ft\s*(\d+)?/', $heightRaw, $m)) {
            $ft = (int)$m[1];
            $in = isset($m[2]) ? (int)$m[2] : 0;
            $heightCm = round(($ft * 12 + $in) * 2.54);
        }

        $weightKg = (float)trim($data[13]) ?: null;
        
        $genderRaw = ucfirst(strtolower(trim($data[14])));
        if(!in_array($genderRaw, ['Male', 'Female', 'Other'])) $genderRaw = null;

        $permanentAddress = trim($data[15]);
        $permanentPinCode = substr(trim($data[16]), 0, 6);
        $currentAddress = trim($data[17]);
        $email = trim($data[18]);
        $higherEducation = trim($data[19]);
        $hobbies = trim($data[20]);
        $partnerPreferences = trim($data[21]);

        $monthlyIncome = (float)preg_replace('/[^0-9.]/', '', trim($data[22])) ?: 0;

        $widowRaw = strtolower(trim($data[23]));
        $widowDivorce = 'none';
        if (strpos($widowRaw, 'widow') !== false) $widowDivorce = 'widow';
        elseif (strpos($widowRaw, 'divorce') !== false) $widowDivorce = 'divorcee';

        $handicapped = trim($data[24]);
        $languagesKnown = trim($data[25]);
        $occupation = trim($data[26]);
        $companyName = trim($data[27]);
        $designation = trim($data[28]);
        $fatherName = trim($data[29]);
        $fatherMobile = trim($data[30]);
        $fatherMonthlyIncome = (float)preg_replace('/[^0-9.]/', '', trim($data[31])) ?: 0;
        $fatherOccupation = trim($data[32]);
        $motherName = trim($data[33]);
        $motherMobile = trim($data[34]);
        $motherOccupation = trim($data[35]);

        $brothersTotal = (int)trim($data[36]) ?: 0;
        $brothersMarried = (int)trim($data[37]) ?: 0;
        $brothersUnmarried = (int)trim($data[38]) ?: 0;
        $sistersTotal = (int)trim($data[39]) ?: 0;
        $sistersMarried = (int)trim($data[40]) ?: 0;
        $sistersUnmarried = (int)trim($data[41]) ?: 0;

        // Path creation logic based on names
        $photoName = str_replace(' ', '_', $fullName);
        // Sometimes the folder path varies, but we will store relative or absolute based on requirement
        // The user said: make sure each person images are stored in "C:\xampp\htdocs\digambar-jain-samaj\digambar-samaj\imports"
        // so take the names from the CSV and images from imports
        // In the user's snippet: C:\Users\Harsh\Desktop\matrimony\imports\profile_photos\mmmmmmmmmm_profile.jpg
        $photoPath = "imports/profile_photos/{$photoName}_profile.jpg";

        // Validate if file actually exists locally, though we insert anyway
        $fullPhotoPath = "C:\\xampp\\htdocs\\digambar-jain-samaj\\digambar-samaj\\$photoPath";
        if (!file_exists($fullPhotoPath)) {
            // maybe try matching with regex or glob if needed?
        }

        try {
            $stmt->execute([
                ':full_name' => $fullName,
                ':country_code' => $countryCode,
                ':mobile_number' => $mobileNumber,
                ':birth_date' => $birthDate,
                ':birth_time' => $birthTime,
                ':birth_place' => $birthPlace,
                ':native' => $native,
                ':gotra' => $gotra,
                ':mama_gotra' => $mamaGotra,
                ':manglik' => $manglik,
                ':height_cm' => $heightCm,
                ':weight_kg' => $weightKg,
                ':gender' => $genderRaw,
                ':permanent_address' => $permanentAddress,
                ':permanent_pin_code' => $permanentPinCode,
                ':current_address' => $currentAddress,
                ':email' => $email,
                ':higher_education' => $higherEducation,
                ':hobbies' => $hobbies,
                ':partner_preferences' => $partnerPreferences,
                ':monthly_income' => $monthlyIncome,
                ':widow_divorce' => $widowDivorce,
                ':handicapped_physical_deficiency' => $handicapped,
                ':languages_known' => $languagesKnown,
                ':occupation' => $occupation,
                ':company_name' => $companyName,
                ':designation' => $designation,
                ':father_name' => $fatherName,
                ':father_mobile' => $fatherMobile,
                ':father_monthly_income' => $fatherMonthlyIncome,
                ':father_occupation' => $fatherOccupation,
                ':mother_name' => $motherName,
                ':mother_mobile' => $motherMobile,
                ':mother_occupation' => $motherOccupation,
                ':brothers_total' => $brothersTotal,
                ':brothers_married' => $brothersMarried,
                ':brothers_unmarried' => $brothersUnmarried,
                ':sisters_total' => $sistersTotal,
                ':sisters_married' => $sistersMarried,
                ':sisters_unmarried' => $sistersUnmarried,
                ':profile_photo_path' => $photoPath
            ]);
            $count++;
        } catch (PDOException $e) {
            echo "Error inserting $fullName: " . $e->getMessage() . "\n";
        }
    }
    fclose($handle);
    echo "Imported $count rows.\n";
} else {
    echo "Failed to open file.";
}
?>
