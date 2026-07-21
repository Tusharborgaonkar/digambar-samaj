<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE payments 
        ADD COLUMN full_name VARCHAR(255) NULL AFTER user_id,
        ADD COLUMN phone_number VARCHAR(20) NULL AFTER full_name,
        ADD COLUMN email VARCHAR(255) NULL AFTER phone_number,
        ADD COLUMN address TEXT NULL AFTER email,
        ADD COLUMN dob DATE NULL AFTER address
    ");
    
    // For existing records, update them with data from users table
    $pdo->exec("UPDATE payments p 
        JOIN users u ON p.user_id = u.id 
        SET p.full_name = u.full_name,
            p.phone_number = u.mobile,
            p.email = u.email,
            p.address = COALESCE(u.current_address, u.permanent_address, ''),
            p.dob = u.birth_date
    ");
    
    echo "Successfully updated payments table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
