<?php
require_once 'includes/db.php';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = 138");
$stmt->execute();
$member = $stmt->fetch(PDO::FETCH_ASSOC);
if($member) {
    echo json_encode($member, JSON_PRETTY_PRINT);
} else {
    echo "Member not found.";
}
