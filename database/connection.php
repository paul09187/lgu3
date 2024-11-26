<?php
// Database configuration
$host = 'localhost';  // Database host
$db_name = 'eco_paul';  // Database name
$username = 'root';  // Database username
$password = '';  // Database password (update accordingly)

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
