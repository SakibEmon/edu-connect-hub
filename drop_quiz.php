<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.html");
    exit();
}

$quiz_id = $_POST['quiz_id'] ?? 0;
$class = $_POST['class'] ?? '';
$subject = $_POST['subject'] ?? '';

if ($quiz_id && $class && $subject) {
    $stmt = $conn->prepare("DELETE FROM teacher_quizzes WHERE quiz_id = ? AND class = ? AND subject = ?");
    $stmt->bind_param("iss", $quiz_id, $class, $subject);
    $stmt->execute();
    $stmt->close();
}

header("Location: teacher_quiz.php?dropped=1");
exit();
