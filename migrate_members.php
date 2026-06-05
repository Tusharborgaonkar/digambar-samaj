<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/db.php';

echo "<h2>Starting Migration: members -> users</h2>";

try {
    // Check if there are members to migrate
    $stmt = $pdo->query("SELECT * FROM members");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $migrated = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($members as $m) {
        // Check if email or mobile already exists in users
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR mobile = ?");
        $checkStmt->execute([$m['email'], $m['mobile_number']]);
        if ($checkStmt->rowCount() > 0) {
            echo "<p>Skipping: {$m['full_name']} (Email or Mobile already in users table)</p>";
            $skipped++;
            continue;
        }

        // Generate profile ID
        $profile_id = 'JDM' . rand(100000, 999999);
        $password_hash = password_hash('digambar123', PASSWORD_DEFAULT);

        $marital_status = 'Never Married';
        if ($m['widow_divorce'] === 'widow') $marital_status = 'Widow';
        if ($m['widow_divorce'] === 'divorcee') $marital_status = 'Divorce';

        $handicapped = 'No';
        if (strtolower($m['handicapped_physical_deficiency']) === 'yes') $handicapped = 'Yes';
        
        $gender = ($m['gender'] === 'Male' || $m['gender'] === 'Female') ? $m['gender'] : null;

        // Determine height string (users table expects string like '5 ft 9 inch', members has height_cm)
        $height = $m['height_cm'] ? $m['height_cm'] . ' cm' : null;

        $insertStmt = $pdo->prepare("
            INSERT INTO users (
                profile_id, full_name, email, mobile, country_code, password_hash, 
                are_you_digambar_jain, gender, birth_date, birth_time, birth_place, native_place, 
                gotra, mama_gotra, manglik, height, weight, marital_status, handicapped, 
                higher_education, occupation, company_name, designation, monthly_income, 
                languages, hobbies, partner_preference, profile_photo, status, verified, 
                registration_source, is_public
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 
                'Yes', ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, 'approved', 1, 
                'admin', 1
            )
        ");

        $manglik = strtolower($m['manglik']) === 'yes' ? 'Yes' : 'No';

        $success = $insertStmt->execute([
            $profile_id, 
            $m['full_name'], 
            $m['email'], 
            $m['mobile_number'], 
            $m['country_code'], 
            $password_hash,
            $gender, 
            $m['birth_date'], 
            $m['birth_time'], 
            $m['birth_place'], 
            $m['native'],
            $m['gotra'], 
            $m['mama_gotra'], 
            $manglik, 
            $height, 
            $m['weight_kg'], 
            $marital_status, 
            $handicapped,
            $m['higher_education'], 
            $m['occupation'], 
            $m['company_name'], 
            $m['designation'], 
            $m['monthly_income'],
            $m['languages_known'], 
            $m['hobbies'], 
            $m['partner_preferences'], 
            $m['profile_photo_path']
        ]);

        if ($success) {
            $migrated++;
        } else {
            $errors++;
            echo "<p>Error inserting: {$m['full_name']}</p>";
        }
    }

    echo "<h3>Migration Complete</h3>";
    echo "<p>Successfully Migrated: $migrated</p>";
    echo "<p>Skipped (Already Exists): $skipped</p>";
    echo "<p>Errors: $errors</p>";
    
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage();
}
?>
