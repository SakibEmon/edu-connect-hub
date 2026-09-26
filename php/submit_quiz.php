<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "student") {
    header("Location: ../login.html");
    exit();
}

include 'db_connect.php';

$student_id = $_SESSION['id'];
$quiz_id = $_POST['quiz_id'];
$answers = $_POST['answers'];

$total_questions = count($answers);
$correct_answers = 0;

// Fetch correct answers from database
$sql = "SELECT id, correct_option FROM quiz_questions WHERE quiz_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($question_id, $correct_option);

// Check answers
while ($stmt->fetch()) {
    if (isset($answers[$question_id]) && $answers[$question_id] == $correct_option) {
        $correct_answers++;
    }
}
$stmt->close();

// Store the result
$sql = "INSERT INTO quiz_results (student_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $student_id, $quiz_id, $correct_answers, $total_questions);
$stmt->execute();
$stmt->close();

header("Location: ../student_dashboard.php?quiz_result=success");
exit();
?>
