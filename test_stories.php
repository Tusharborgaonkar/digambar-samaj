<?php
require_once 'includes/db.php';
$stmt = $pdo->query('SELECT id, photo FROM success_stories ORDER BY id DESC LIMIT 5');
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "SUCCESS STORIES DB:\n";
print_r($stories);

echo "\nChecking file existence:\n";
foreach ($stories as $story) {
    if ($story['photo']) {
        $cleanPath = ltrim(str_replace('../', '', $story['photo']), '/');
        $fullPath = realpath(__DIR__ . '/' . $cleanPath);
        echo "ID: " . $story['id'] . " | Path in DB: " . $story['photo'] . " | Clean Path: " . $cleanPath . " | Full Path: " . $fullPath . " | Exists: " . (file_exists(__DIR__ . '/' . $cleanPath) ? "YES" : "NO") . "\n";
    }
}
