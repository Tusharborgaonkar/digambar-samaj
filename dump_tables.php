<?php
require 'c:/xampp/htdocs/digambar-samaj/includes/db.php';
$stmt = $pdo->query('SHOW TABLES');
$tables = [];
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}
file_put_contents('c:/xampp/htdocs/digambar-samaj/db_tables.txt', implode("\n", $tables));
echo "Done";
