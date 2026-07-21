<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;
// We read from php://input since we'll use fetch POST with JSON, or standard POST
$input = json_decode(file_get_contents('php://input'), true);
$mobile = $input['mobile'] ?? $_POST['mobile'] ?? '';

if (empty($mobile)) {
    echo json_encode(['status' => 'error', 'message' => 'Mobile number is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? AND id != ? LIMIT 1");
    $stmt->execute([$mobile, $user_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'duplicate', 'message' => 'This mobile number is already registered. Please enter a different mobile number.']);
    } else {
        echo json_encode(['status' => 'ok']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
