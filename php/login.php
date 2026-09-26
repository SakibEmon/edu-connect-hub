<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, email, role, class, subject, password FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $email, $role, $class, $subject, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['class'] = $class;
            $_SESSION['subject'] = $subject;

            if ($role == "teacher") {
                header("Location: ../teacher_dashboard.php");
            } else {
                header("Location: ../student_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with this email!'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
