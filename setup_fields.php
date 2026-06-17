<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';

$fields = [
    ['Basic Details', 'subcast', 'Subcast (उपजाति)', 'dropdown', 'Lad, Visa, Dasha', 0, 1, 1],
    ['Basic Details', 'mandir', 'Registered Mandir (मंदिर)', 'dropdown', 'N/A', 0, 1, 1],
    ['Reference Details', 'ref1_name', 'Reference 1 Name', 'text', '', 0, 1, 1],
    ['Reference Details', 'ref1_mobile', 'Reference 1 Mobile', 'text', '', 0, 1, 1],
    ['Reference Details', 'ref1_relation', 'Reference 1 Relation', 'text', '', 0, 1, 1],
    ['Reference Details', 'ref2_name', 'Reference 2 Name', 'text', '', 0, 1, 1],
    ['Reference Details', 'ref2_mobile', 'Reference 2 Mobile', 'text', '', 0, 1, 1],
    ['Reference Details', 'ref2_relation', 'Reference 2 Relation', 'text', '', 0, 1, 1],
];

foreach ($fields as $f) {
    // Check if exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registration_fields WHERE field_key = ?");
    $stmt->execute([$f[1]]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO registration_fields (field_group, field_key, field_label, field_type, field_options, is_custom, is_visible, is_required) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($f);
        echo "Inserted " . $f[1] . "\n";
    }
}
echo "Done";
