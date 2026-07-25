<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("
        SELECT id, current_address 
        FROM users 
        WHERE current_address REGEXP '[[:<:]](USA|Canada|Dubai|UK|UAE|Australia|London|Singapore|New Zealand|Germany|Kuwait|Oman|Qatar|Toronto)[[:>:]]'
        LIMIT 50
    ");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
