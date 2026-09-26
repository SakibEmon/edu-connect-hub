<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['students'])) {
    $students = $_POST['students'];
    $class = $_SESSION['class'];
    $subject = $_SESSION['subject'];

    // Enroll each student
    foreach ($students as $student_id) {
        $sql = "UPDATE users SET subject=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $subject, $student_id);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>alert('Students enrolled successfully!'); window.location.href = '../teacher_dashboard.php';</script>";
} else {
    echo "<script>alert('No students selected!'); window.location.href = '../teacher_enrollment.php';</script>";
}
?>
