<?php
$host = 'localhost';
$user = 'root';
$password = 'root'; // For MAMP
$dbname = 'airline_db'; // Corrected database name

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
