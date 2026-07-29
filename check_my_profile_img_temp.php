<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';
$stmt = $pdo->query('SELECT * FROM users WHERE id = 507');
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "User found: " . $user['full_name'] . "\n";
    echo "profile_photo in DB: " . $user['profile_photo'] . "\n";
    
    $exists = file_exists($user['profile_photo']) ? 'TRUE' : 'FALSE';
    echo "file_exists(): " . $exists . "\n";
    
    $profile_img = (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) ? 'image.php?file='.urlencode($user['profile_photo']) : 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']).'&background=random';
    
    echo "computed profile_img: " . $profile_img . "\n";
} else {
    echo "User not found.\n";
}
