<?php
require 'includes/db.php';
$stmt = $pdo->query("SELECT VERSION()");
echo $stmt->fetchColumn();
