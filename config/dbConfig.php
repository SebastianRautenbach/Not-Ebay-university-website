<?php
$servername = "localhost";
$username = "root";
$db = "authentication";
$password = "";
$charset = 'utf8mb4';

$databaseconnection = "mysql:host=$servername;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $conn = new PDO($databaseconnection, $username, $password, $options);
} catch (PDOException $e) {
    die("Database cold not connect: " . $e->getMessage());
}


?>