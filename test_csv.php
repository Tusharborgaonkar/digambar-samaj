<?php
$csvFile = __DIR__ . '/परिचय सम्मेलन 2025-26 फोर्म Parichay Sammelan 2025-26 Form (Responses) - Form Responses 1 (12).csv';
$handle = fopen($csvFile, "r");
if ($handle !== FALSE) {
    $header = fgetcsv($handle);
    echo json_encode(array_flip($header), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    fclose($handle);
}
