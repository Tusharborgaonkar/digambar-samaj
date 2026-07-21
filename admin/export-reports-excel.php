<?php
require_once '../includes/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$spreadsheet = new Spreadsheet();

// ---------------------------------------------------------
// Sheet 1: Signups (Last 30 Days)
// ---------------------------------------------------------
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Signups (30 Days)');

$stmt = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM users 
    WHERE status != 'blocked' 
      AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) 
    GROUP BY DATE(created_at) 
    ORDER BY date ASC
");
$signups = $stmt->fetchAll();

$sheet1->setCellValue('A1', 'Date');
$sheet1->setCellValue('B1', 'Signups');
$sheet1->getStyle('A1:B1')->getFont()->setBold(true);

$row = 2;
foreach ($signups as $s) {
    $sheet1->setCellValue('A' . $row, $s['date']);
    $sheet1->setCellValue('B' . $row, $s['count']);
    $row++;
}
$sheet1->getColumnDimension('A')->setAutoSize(true);
$sheet1->getColumnDimension('B')->setAutoSize(true);

// ---------------------------------------------------------
// Sheet 2: Professions
// ---------------------------------------------------------
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Top Professions');

$stmt = $pdo->query("
    SELECT occupation, COUNT(*) as count 
    FROM users 
    WHERE status != 'blocked' AND occupation IS NOT NULL AND occupation != ''
    GROUP BY occupation 
    ORDER BY count DESC 
    LIMIT 20
");
$professions = $stmt->fetchAll();

$sheet2->setCellValue('A1', 'Occupation');
$sheet2->setCellValue('B1', 'Count');
$sheet2->getStyle('A1:B1')->getFont()->setBold(true);

$row = 2;
foreach ($professions as $p) {
    $sheet2->setCellValue('A' . $row, $p['occupation']);
    $sheet2->setCellValue('B' . $row, $p['count']);
    $row++;
}
$sheet2->getColumnDimension('A')->setAutoSize(true);
$sheet2->getColumnDimension('B')->setAutoSize(true);

// ---------------------------------------------------------
// Sheet 3: Revenue (This Year)
// ---------------------------------------------------------
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Revenue (This Year)');

$stmt = $pdo->query("
    SELECT MONTH(created_at) as month, SUM(amount) as total 
    FROM payments 
    WHERE status = 'verified' AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
    GROUP BY MONTH(created_at)
    ORDER BY month ASC
");
$revenues = $stmt->fetchAll();
$revMap = [];
foreach($revenues as $r) {
    $revMap[$r['month']] = $r['total'];
}

$sheet3->setCellValue('A1', 'Month');
$sheet3->setCellValue('B1', 'Revenue (INR)');
$sheet3->getStyle('A1:B1')->getFont()->setBold(true);

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$row = 2;
for ($i = 1; $i <= 12; $i++) {
    $sheet3->setCellValue('A' . $row, $months[$i-1]);
    $sheet3->setCellValue('B' . $row, isset($revMap[$i]) ? $revMap[$i] : 0);
    $row++;
}
$sheet3->getColumnDimension('A')->setAutoSize(true);
$sheet3->getColumnDimension('B')->setAutoSize(true);

// ---------------------------------------------------------
// Output Excel
// ---------------------------------------------------------
$spreadsheet->setActiveSheetIndex(0); // Set active sheet to the first one

if (ob_get_length()) {
    ob_clean();
}

$filename = "reports_export_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
