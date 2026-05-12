<?php

$host = "localhost";
$username = "root";
$password = "";
$db = "blood_donation_system";
// $db = "dd";

try {
    // PDO
    $driver = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $con = new PDO($driver, $username, $password);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>