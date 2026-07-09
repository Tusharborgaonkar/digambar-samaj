<?php
require_once '../includes/db.php';
// Include Composer autoload
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    die("Please install phpoffice/phpspreadsheet via composer: composer require phpoffice/phpspreadsheet");
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Members');

// Set Header Row
$columns = [
    'A' => 'Profile ID', 'B' => 'Photo', 'C' => 'Full Name', 'D' => 'Email', 'E' => 'Mobile', 
    'F' => 'Gender', 'G' => 'Birth Date', 'H' => 'Birth Time', 'I' => 'Birth Place', 
    'J' => 'Native Place', 'K' => 'Gotra', 'L' => 'Marital Status', 'M' => 'Height', 
    'N' => 'Higher Education', 'O' => 'Occupation', 'P' => 'Company Name',
    'Q' => 'Status', 'R' => 'Registration Date'
];

foreach ($columns as $col => $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
// Set specific width for Photo column
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getStyle('A1:R1')->getFont()->setBold(true);

$rowNum = 2;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Set Row Height for images
    $sheet->getRowDimension($rowNum)->setRowHeight(80);

    $sheet->setCellValue('A' . $rowNum, $row['profile_id']);
    
    // Add Image
    $photoPath = '../' . $row['profile_photo'];
    if (!empty($row['profile_photo']) && file_exists($photoPath)) {
        $drawing = new Drawing();
        $drawing->setName('Profile Photo');
        $drawing->setDescription('Profile Photo');
        $drawing->setPath($photoPath);
        $drawing->setCoordinates('B' . $rowNum);
        $drawing->setHeight(100);
        $drawing->setOffsetY(5);
        $drawing->setOffsetX(5);
        $drawing->setWorksheet($sheet);
    } else {
        $sheet->setCellValue('B' . $rowNum, 'No Image');
    }

    $sheet->setCellValue('C' . $rowNum, $row['full_name']);
    $sheet->setCellValue('D' . $rowNum, $row['email']);
    $sheet->setCellValue('E' . $rowNum, $row['mobile']);
    $sheet->setCellValue('F' . $rowNum, $row['gender']);
    $sheet->setCellValue('G' . $rowNum, $row['birth_date']);
    $sheet->setCellValue('H' . $rowNum, $row['birth_time']);
    $sheet->setCellValue('I' . $rowNum, $row['birth_place']);
    $sheet->setCellValue('J' . $rowNum, $row['native_place']);
    $sheet->setCellValue('K' . $rowNum, $row['gotra']);
    $sheet->setCellValue('L' . $rowNum, $row['marital_status']);
    $sheet->setCellValue('M' . $rowNum, $row['height']);
    $sheet->setCellValue('N' . $rowNum, $row['higher_education']);
    $sheet->setCellValue('O' . $rowNum, $row['occupation']);
    $sheet->setCellValue('P' . $rowNum, $row['company_name']);
    $sheet->setCellValue('Q' . $rowNum, ucfirst(str_replace('_', ' ', $row['status'])));
    $sheet->setCellValue('R' . $rowNum, date('Y-m-d H:i:s', strtotime($row['created_at'])));

    $rowNum++;
}

// Ensure output buffers are clean
if (ob_get_length()) {
    ob_clean();
}

$filename = "members_export_" . date('Y-m-d_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
