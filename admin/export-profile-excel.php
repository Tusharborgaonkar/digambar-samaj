<?php
require_once '../includes/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    die("Member not found.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column widths
$sheet->getColumnDimension('A')->setWidth(25);
$sheet->getColumnDimension('B')->setWidth(50);

$sheet->setCellValue('A1', 'Digambar Jain Matrimony Profile');
$sheet->mergeCells('A1:B1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

// Image
$row = 2;
if (!empty($member['profile_photo']) && file_exists('../' . $member['profile_photo'])) {
    $drawing = new Drawing();
    $drawing->setName('Profile Photo');
    $drawing->setDescription('Profile Photo');
    $drawing->setPath('../' . $member['profile_photo']);
    $drawing->setHeight(150);
    $drawing->setCoordinates('B2');
    $drawing->setWorksheet($sheet);
    $sheet->getRowDimension($row)->setRowHeight(120);
} else {
    $sheet->setCellValue('B2', 'No Photo');
}
$sheet->setCellValue('A2', 'Profile Photo:');

$row = 3;
$fields = [
    'Profile ID' => $member['profile_id'] ?? '',
    'Full Name' => $member['full_name'] ?? '',
    'Form Filled By' => $member['filled_by'] ?? 'Candidate',
    'Mobile' => $member['mobile'] ?? '',
    'Email' => $member['email'] ?? '',
    'Date of Birth' => $member['birth_date'] ?? '',
    'Time of Birth' => $member['birth_time'] ?? '',
    'Age' => (!empty($member['birth_date']) ? (new DateTime())->diff(new DateTime($member['birth_date']))->y . ' Years' : ''),
    'Height' => $member['height'] ?? '',
    'Weight' => $member['weight'] ?? '',
    'Gender' => $member['gender'] ?? '',
    'Marital Status' => $member['marital_status'] ?? '',
    'Birth Place' => $member['birth_place'] ?? '',
    'Native Place' => $member['native_place'] ?? '',
    'Gotra' => $member['gotra'] ?? '',
    'Mama Gotra' => $member['mama_gotra'] ?? '',
    'Manglik' => $member['manglik'] ?? '',
    'Education' => $member['higher_education'] ?? '',
    'Occupation' => $member['occupation'] ?? '',
    'Monthly Income' => $member['monthly_income'] ?? '',
    'Language' => $member['languages'] ?? '',
    'Father Name' => $member['father_name'] ?? '',
    'Father Occupation' => $member['father_occupation'] ?? '',
    'Mother Name' => $member['mother_name'] ?? '',
    'Mother Occupation' => $member['mother_occupation'] ?? '',
    'Mandir Name' => $member['mandir_name'] ?? '',
    'Mandir Address' => $member['mandir_address'] ?? '',
    'Ref 1 Name' => $member['ref1_name'] ?? '',
    'Ref 1 Mobile' => $member['ref1_mobile'] ?? '',
    'Ref 2 Name' => $member['ref2_name'] ?? '',
    'Ref 2 Mobile' => $member['ref2_mobile'] ?? ''
];

foreach ($fields as $label => $val) {
    $sheet->setCellValue('A' . $row, $label);
    $sheet->setCellValue('B' . $row, $val);
    $sheet->getStyle('A'.$row)->getFont()->setBold(true);
    $row++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="profile_' . $member['profile_id'] . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
