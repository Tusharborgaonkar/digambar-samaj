<?php
require '../includes/db.php';
$stmt = $pdo->query("SELECT * FROM advertisements ORDER BY id DESC LIMIT 5");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<html><body>";
foreach ($ads as $ad) {
    $src = "../image.php?file=" . urlencode($ad['image']);
    echo "<h3>" . htmlspecialchars($ad['title']) . "</h3>";
    echo "<p>DB Image: " . htmlspecialchars($ad['image']) . "</p>";
    echo "<p>Img Src: " . htmlspecialchars($src) . "</p>";
    echo "<img src='" . htmlspecialchars($src) . "' style='width: 200px;' />";
    echo "<hr/>";
}
echo "</body></html>";
