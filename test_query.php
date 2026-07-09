<?php
require 'includes/db.php';
try {
    $user_id = 1; // Assuming 1 exists and has likes
    $query = "SELECT u.id, u.full_name, IF(ul.id IS NOT NULL, 1, 0) AS is_liked 
              FROM users u 
              LEFT JOIN user_likes ul ON ul.liked_user_id = u.id AND ul.user_id = ? 
              WHERE u.status = 'approved' AND u.is_public = 1 
              ORDER BY is_liked DESC, u.id DESC 
              LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
