<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($env == 'local') {
    $host = 'mysql';
    $db   = 'coursera';
    $user = 'root';
    $pass = 'root';
    $charset = 'utf8mb4';
}

if ($env == 'production') {
    $host = 'localhost';
    $db   = 'marceloleodev';
    $user = 'marceloleodev';
    $pass = 'M4rc310LeoDev#1264';
    $charset = 'utf8mb4';
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection error: " . $e->getMessage());
}
