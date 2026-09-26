<?php
session_start();
// Successful login হলে...
$_SESSION['admin_id'] = $row['id'];
$_SESSION['admin_name'] = $row['name']; // এটিই dashboard-এ ব্যবহৃত হবে

require_once 'php/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin_users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "No admin account found with this email.";
    }
}
?>
