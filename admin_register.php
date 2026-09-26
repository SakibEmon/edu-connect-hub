<?php
require_once 'php/db_connect.php' ; // Use your existing db_connect.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $conn->real_escape_string($_POST['name']);
    $email    = $conn->real_escape_string($_POST['email']);
    $phone    = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->query("SELECT * FROM admin_users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        echo "Email already registered!";
    } else {
        $sql = "INSERT INTO admin_users (name, email, phone, password) 
                VALUES ('$name', '$email', '$phone', '$password')";

        if ($conn->query($sql) === TRUE) {
            echo "Registration successful. <a href='admin_login.html'>Login Now</a>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>
