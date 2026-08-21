<?php
$host  = 'localhost';
$user = 'root';
$password = '';
$database = 'forces-academy-lms';

$conn = mysqli_connect($host, $user, $password, $database);

if(!$conn){
    die('Database connection failed: ' . mysqli_connect_error());
}

// InfinityFree Database Connection Settings
// $host   = "sql301.infinityfree.com";
// $user   = "if0_42541562";
// $pass   = "aftab4049102"; // Yahan apna vPanel/Account Password likhein
// $dbname = "if0_42541562_forceslms";

// // Database Connection Establish karna
// $conn = mysqli_connect($host, $user, $pass, $dbname);

// // Connection check
// if (!$conn) {
//     die("Database Connection Failed: " . mysqli_connect_error());
// }
?>