<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $class = $_POST['class'];
    $subject = isset($_POST['subject']) ? $_POST['subject'] : NULL;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, role, class, subject, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $email, $role, $class, $subject, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='../login.html';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }
    
    $stmt->close();
    $conn->close();
}
?>
