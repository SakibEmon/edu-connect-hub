<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}

$teacher_email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = $_POST['quiz_id'];
    $class = $_POST['class'];
    $subject = $_POST['subject'];

    // Check if quiz already assigned
    $checkStmt = $conn->prepare("SELECT id FROM teacher_quizzes WHERE quiz_id = ? AND class = ? AND subject = ?");
    $checkStmt->bind_param("iss", $quiz_id, $class, $subject);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        header("Location: teacher_quiz.php?added=0");
        exit();
    }
    $checkStmt->close();

    // Assign the quiz
    $stmt = $conn->prepare("INSERT INTO teacher_quizzes (teacher_email, class, subject, quiz_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $teacher_email, $class, $subject, $quiz_id);

    if ($stmt->execute()) {
        header("Location: teacher_quiz.php?added=1");
    } else {
        header("Location: teacher_quiz.php?added=0");
    }
    $stmt->close();
}

$conn->close();
?>
