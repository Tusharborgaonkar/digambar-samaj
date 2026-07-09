<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to like profiles']);
    exit;
}

$user_id = $_SESSION['user_id'];
$liked_user_id = isset($_POST['liked_user_id']) ? (int)$_POST['liked_user_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$liked_user_id || ($action !== 'like' && $action !== 'unlike')) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    if ($action === 'like') {
        $stmt = $pdo->prepare("INSERT INTO user_likes (user_id, liked_user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE id=id");
        $stmt->execute([$user_id, $liked_user_id]);
        echo json_encode(['success' => true, 'action' => 'liked']);
    } else {
        $stmt = $pdo->prepare("DELETE FROM user_likes WHERE user_id = ? AND liked_user_id = ?");
        $stmt->execute([$user_id, $liked_user_id]);
        echo json_encode(['success' => true, 'action' => 'unliked']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
