<?php
require 'includes/db.php';

$whereClause = "status = 'approved'";
$params = [];

// Simulate Age Filter
$age_from = 18;
$age_to = 25;

if (!empty($age_from) && is_numeric($age_from)) {
    $maxBirthDate = date('Y-m-d', strtotime('-' . (int)$age_from . ' years'));
    $whereClause .= " AND birth_date <= ?";
    $params[] = $maxBirthDate;
}
if (!empty($age_to) && is_numeric($age_to)) {
    $minBirthDate = date('Y-m-d', strtotime('-' . ((int)$age_to + 1) . ' years +1 day'));
    $whereClause .= " AND birth_date >= ?";
    $params[] = $minBirthDate;
}

$orderClause = "birth_date DESC, full_name ASC"; // Youngest first

$query = "SELECT id, full_name, birth_date, TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as age FROM users WHERE $whereClause ORDER BY $orderClause LIMIT 10";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($profiles);
?>
