<?php
include 'includes/db.php';
$columns = [
    'permanent_address' => 'TEXT',
    'pin_code' => 'VARCHAR(20)',
    'current_address' => 'TEXT',
    'father_name' => 'VARCHAR(255)',
    'father_mobile' => 'VARCHAR(50)',
    'father_income' => 'VARCHAR(100)',
    'father_occupation' => 'VARCHAR(255)',
    'mother_name' => 'VARCHAR(255)',
    'mother_mobile' => 'VARCHAR(50)',
    'mother_occupation' => 'VARCHAR(255)',
    'mother_occupation_details' => 'VARCHAR(255)',
    'brothers' => 'INT DEFAULT 0',
    'brothers_married' => 'INT DEFAULT 0',
    'brothers_unmarried' => 'INT DEFAULT 0',
    'sisters' => 'INT DEFAULT 0',
    'sisters_married' => 'INT DEFAULT 0',
    'sisters_unmarried' => 'INT DEFAULT 0',
    'subcast' => 'VARCHAR(255)',
    'custom_subcast' => 'VARCHAR(255)',
    'mandir' => 'VARCHAR(255)',
    'custom_mandir' => 'VARCHAR(255)',
    'ref1_name' => 'VARCHAR(255)',
    'ref1_mobile' => 'VARCHAR(50)',
    'ref1_relation' => 'VARCHAR(100)',
    'ref2_name' => 'VARCHAR(255)',
    'ref2_mobile' => 'VARCHAR(50)',
    'ref2_relation' => 'VARCHAR(100)'
];

foreach ($columns as $col => $type) {
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN $col $type");
        echo "Added $col\n";
    } catch (Exception $e) {
        echo "Error on $col: " . $e->getMessage() . "\n";
    }
}
?>
