<?php
require_once '../includes/db.php';

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$whereConditions = [];
$params = [];

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $whereConditions[] = "status = ?";
    $params[] = $_GET['status'];
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $whereConditions[] = "(full_name LIKE ? OR email LIKE ? OR profile_id LIKE ?)";
    $params[] = "%" . trim($_GET['search']) . "%";
    $params[] = "%" . trim($_GET['search']) . "%";
    $params[] = "%" . trim($_GET['search']) . "%";
}
if (isset($_GET['gender']) && !empty($_GET['gender'])) {
    $whereConditions[] = "gender = ?";
    $params[] = $_GET['gender'];
}

$whereClause = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$filename = "members_export_" . date('Y-m-d_His') . ".csv";

// Set headers for download
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");
// Add BOM for proper UTF-8 handling in Excel
fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Define columns
$columns = [
    'Profile ID', 'Full Name', 'Email', 'Mobile', 'Gender', 'Birth Date',
    'Birth Time', 'Birth Place', 'Native Place', 'Gotra', 'Marital Status',
    'Height', 'Higher Education', 'Occupation', 'Company Name',
    'Status', 'Registration Date'
];

fputcsv($output, $columns);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data = [
        $row['profile_id'],
        $row['full_name'],
        $row['email'],
        $row['mobile'],
        $row['gender'],
        $row['birth_date'],
        $row['birth_time'],
        $row['birth_place'],
        $row['native_place'],
        $row['gotra'],
        $row['marital_status'],
        $row['height'],
        $row['higher_education'],
        $row['occupation'],
        $row['company_name'],
        ucfirst(str_replace('_', ' ', $row['status'])),
        date('Y-m-d H:i:s', strtotime($row['created_at']))
    ];
    fputcsv($output, $data);
}

fclose($output);
exit;
