<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['attendance'])) {
    $attendance = $_POST['attendance'];
    $date = date('Y-m-d');
    $class = $_SESSION['class'];
    $subject = $_SESSION['subject'];

    // Mark attendance for each student
    foreach ($attendance as $student_id) {
        $sql = "INSERT INTO attendance (student_id, class, subject, date, status) VALUES (?, ?, ?, ?, 'present')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $student_id, $class, $subject, $date);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>alert('Attendance marked successfully!'); window.location.href = '../teacher_dashboard.php';</script>";
} else {
    echo "<script>alert('No students selected!'); window.location.href = '../teacher_attendance.php';</script>";
}
?>
