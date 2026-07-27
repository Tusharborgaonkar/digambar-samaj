<?php
require 'includes/db.php';

$csvFile = __DIR__ . '/परिचय सम्मेलन 2025-26 फोर्म Parichay Sammelan 2025-26 Form (Responses) - Form Responses 1 (12).csv';

if (!file_exists($csvFile)) {
    die("CSV file not found.");
}

$handle = fopen($csvFile, "r");
if ($handle !== FALSE) {
    // skip header
    fgetcsv($handle);
    
    $count = 0;
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        if(count($data) < 42) continue;
        
        $mobileNumber = preg_replace('/[^0-9]/', '', trim($data[4]));
        if(strlen($mobileNumber) > 10) {
            $mobileNumber = substr($mobileNumber, -10);
        }

        $widowRaw = strtolower(trim($data[23]));
        $widowDivorce = 'Never Married';
        if (strpos($widowRaw, 'widow') !== false) $widowDivorce = 'Widow';
        elseif (strpos($widowRaw, 'divorce') !== false) $widowDivorce = 'Divorce';

        $handicapped = trim($data[24]);
        $languagesKnown = trim($data[25]);
        $occupation = trim($data[26]);
        $companyName = trim($data[27]);
        $designation = trim($data[28]);
        
        $heightRaw = trim($data[12]);
        $heightCm = null;
        if (preg_match('/(\d+)\s*ft\s*(\d+)?/', $heightRaw, $m)) {
            $ft = (int)$m[1];
            $in = isset($m[2]) ? (int)$m[2] : 0;
            $heightCm = $heightRaw; // Let's store the raw '5 ft 3 inch' in the 'height' column as intended for display
        }
        
        // Wait, the DB column 'height' is VARCHAR(20) and is displayed as-is in profile-details.php.
        // But import.php was converting it to cm and saving it!
        // Let's update height to string if they want strings.

        // Date mapping M/D/YYYY to YYYY-MM-DD
        $birthDateRaw = str_replace('-', '/', trim($data[5]));
        $birthDate = null;
        if (!empty($birthDateRaw)) {
            $parts = explode('/', $birthDateRaw);
            if (count($parts) === 3) {
                $m = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $d = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                $y = $parts[2];
                if (strlen($y) == 2) $y = ($y > 30 ? '19' : '20') . $y;
                $birthDate = "$y-$m-$d";
            }
        }

        $brothersTotal = (int)trim($data[36]) ?: 0;
        $brothersMarried = (int)trim($data[37]) ?: 0;
        $brothersUnmarried = (int)trim($data[38]) ?: 0;
        $sistersTotal = (int)trim($data[39]) ?: 0;
        $sistersMarried = (int)trim($data[40]) ?: 0;
        $sistersUnmarried = (int)trim($data[41]) ?: 0;

        $stmt = $pdo->prepare("UPDATE users SET 
            marital_status = :marital_status,
            handicapped = :handicapped,
            languages = :languages,
            occupation = :occupation,
            company_name = :company_name,
            designation = :designation,
            height = :height,
            birth_date = :birth_date,
            brothers = :brothers_total,
            brothers_married = :brothers_married,
            brothers_unmarried = :brothers_unmarried,
            sisters = :sisters_total,
            sisters_married = :sisters_married,
            sisters_unmarried = :sisters_unmarried
            WHERE mobile LIKE :mobile
        ");
        
        $stmt->execute([
            ':marital_status' => $widowDivorce,
            ':handicapped' => $handicapped,
            ':languages' => $languagesKnown,
            ':occupation' => $occupation,
            ':company_name' => $companyName,
            ':designation' => $designation,
            ':height' => $heightRaw,
            ':birth_date' => $birthDate,
            ':brothers_total' => $brothersTotal,
            ':brothers_married' => $brothersMarried,
            ':brothers_unmarried' => $brothersUnmarried,
            ':sisters_total' => $sistersTotal,
            ':sisters_married' => $sistersMarried,
            ':sisters_unmarried' => $sistersUnmarried,
            ':mobile' => '%' . $mobileNumber
        ]);
        
        $count++;
    }
    fclose($handle);
    echo "Successfully updated $count records based on CSV data.";
} else {
    echo "Error opening CSV.";
}
?>
