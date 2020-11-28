<?php

$pdo = new PDO('mysql:host=192.168.2.6;dbname=doge', 'root', 'secret');
$pdo->prepare("INSERT INTO visitors (visit_date) VALUES (?)")->execute([(new DateTime())->format(DATE_RFC3339)]);

$query = $pdo->prepare('SELECT COUNT(*) FROM visitors');
$query->execute();
echo 'No. visitors: ' . $query->fetchColumn();

return '123';