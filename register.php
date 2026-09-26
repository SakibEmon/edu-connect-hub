<?php
include 'db_connect.php';

$name     = $_POST['name'];
$email    = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role     = $_POST['role'];
$class    = $_POST['class'];
$subject  = isset($_POST['subject']) ? $_POST['subject'] : null;

// Check if email already exists
$check_sql = "SELECT * FROM users WHERE email=?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo "❌ Email already registered!";
    exit();
}

// Insert new user
$sql = "INSERT INTO users (name, email, password, role, class, subject) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $name, $email, $password, $role, $class, $subject);

if ($stmt->execute()) {
    header("Location: ../login.html?register=success");
    exit();
} else {
    echo "❌ Something went wrong during registration.";
}

$stmt->close();
$conn->close();
?>
