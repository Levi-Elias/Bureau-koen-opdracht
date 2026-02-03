<?php
require_once __DIR__ . '/assets/includes/conn.php';

$dbConnection = new DbhConnection();
$pdo = $dbConnection->connect();

include __DIR__ . '/assets/includes/header.php';
?>
