<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "teacher") {
    header("Location: ../login.html");
    exit();
}

include 'db_connect.php';

$teacher_id = $_POST['teacher_id'];
$class = $_POST['class'];
$subject = $_POST['subject'];
$date = date("Y-m-d");
$attendance = $_POST['attendance'];

foreach ($attendance as $student_id => $status) {
    $sql = "INSERT INTO attendance (teacher_id, student_id, class, subject, date, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $teacher_id, $student_id, $class, $subject, $date, $status);
    $stmt->execute();
}

header("Location: ../teacher_attendance.php?success=1");
exit();
?>
