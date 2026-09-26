<?php
$servername = "localhost"; // Default XAMPP server
$username = "root"; // Default MySQL username
$password = ""; // No password by default in XAMPP
$dbname = "edu_connect_hub"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>
