<?php
include 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN occupation_other VARCHAR(255) DEFAULT NULL, ADD COLUMN father_occupation_other VARCHAR(255) DEFAULT NULL, ADD COLUMN mother_occupation_other VARCHAR(255) DEFAULT NULL");
    echo "Columns added successfully";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
