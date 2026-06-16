<?php
require_once 'includes/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $additional_fields = [
        // Personal
        ['Basic Details', 'birth_time', 'Time of Birth', 'time', '', 0, 1, 0, 0, 11],
        ['Basic Details', 'birth_place', 'Place of Birth', 'text', '', 0, 1, 0, 0, 12],
        ['Basic Details', 'native_place', 'Native Place', 'text', '', 0, 1, 0, 0, 13],
        
        // Religious
        ['Religious Details', 'mama_gotra', 'Mama Gotra', 'text', '', 0, 1, 0, 0, 14],
        
        // Physical Attributes
        ['Physical Attributes', 'height', 'Height', 'text', '', 0, 1, 0, 0, 15],
        ['Physical Attributes', 'weight', 'Weight (kg)', 'number', '', 0, 1, 0, 0, 16],
        ['Physical Attributes', 'handicapped', 'Handicapped/Physical Deficiency', 'dropdown', 'Yes,No', 0, 1, 0, 0, 17],
        
        // Marital Status
        ['Basic Details', 'marital_status', 'Marital Status', 'dropdown', 'Never Married,Widow,Widower,Divorce', 0, 1, 0, 0, 18],

        // Education & Profession
        ['Education & Profession', 'higher_education', 'Higher Education', 'textarea', '', 0, 1, 0, 0, 19],
        ['Education & Profession', 'occupation', 'Occupation', 'text', '', 0, 1, 0, 0, 20],
        ['Education & Profession', 'company_name', 'Company Name', 'text', '', 0, 1, 0, 0, 21],
        ['Education & Profession', 'designation', 'Designation', 'text', '', 0, 1, 0, 0, 22],
        ['Education & Profession', 'monthly_income', 'Monthly Income', 'number', '', 0, 1, 0, 0, 23],

        // Lifestyle
        ['Lifestyle', 'languages', 'Languages Known', 'textarea', '', 0, 1, 0, 0, 24],
        ['Lifestyle', 'hobbies', 'Hobbies', 'textarea', '', 0, 1, 0, 0, 25],
        ['Lifestyle', 'partner_preference', 'Partner Preference', 'textarea', '', 0, 1, 0, 0, 26],

        // Media (Files)
        ['Media & Payment', 'profile_photo', 'Profile Photo', 'file', '', 0, 1, 0, 0, 27],
        ['Media & Payment', 'family_photo', 'Family Photo', 'file', '', 0, 1, 0, 0, 28],
        ['Media & Payment', 'payment_screenshot', 'Payment Screenshot', 'file', '', 0, 1, 0, 0, 29],
        
        // Drive URLs
        ['Media & Payment', 'profile_photo_drive_url', 'Profile Photo Drive URL', 'url', '', 0, 1, 0, 0, 30],
        ['Media & Payment', 'payment_proof_drive_url', 'Payment Proof Drive URL', 'url', '', 0, 1, 0, 0, 31],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO registration_fields (field_group, field_key, field_label, field_type, field_options, is_custom, is_visible, is_required, is_core, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($additional_fields as $f) {
        $stmt->execute($f);
    }

    echo "Additional fields seeded successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
