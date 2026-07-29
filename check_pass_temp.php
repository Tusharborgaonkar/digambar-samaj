<?php
$hash = '$2y$10$lee6J8Yci47tggE1M56lJOOxdqdImH38zvvK/8UrHLjGj89MR//1y';
$password = '9909023185';
echo password_verify($password, $hash) ? 'MATCH' : 'NO MATCH';
