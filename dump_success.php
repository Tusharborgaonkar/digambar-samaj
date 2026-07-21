<?php
require 'includes/db.php';
$stmt = $pdo->query('SELECT id, photo FROM success_stories');
file_put_contents('success_dump.txt', print_r($stmt->fetchAll(PDO::FETCH_ASSOC), true));
echo "Done";
