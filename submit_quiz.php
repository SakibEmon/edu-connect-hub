<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_name = $_POST['student_name'];
$quiz_id = $_POST['quiz_id'];
$subject = $_POST['subject'];
$class = $_POST['class'];
$topic = $_POST['topic'];

// Check if already attempted
$sql_check = "SELECT id FROM student_quiz_results WHERE student_name = ? AND quiz_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("si", $student_name, $quiz_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows > 0) {
    header("Location: student_course_view.php?subject=" . urlencode($subject) . "&class=" . urlencode($class) . "&status=already_attempted");
    exit();
}
$stmt_check->close();

// Fetch quiz questions
$sql_quiz = "SELECT questions FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
$quiz = $result_quiz->fetch_assoc();
$questions = json_decode($quiz['questions'], true);
$stmt_quiz->close();

// Calculate score and store selected answers
$score = 0;
$selected_answers = [];
foreach ($questions as $index => $q) {
    $answer_key = "q" . ($index + 1);
    $selected = $_POST[$answer_key] ?? null;
    $selected_answers[$index + 1] = $selected;
    if ($selected && $selected === $q['correct']) {
        $score++;
    }
}
$selected_answers_json = json_encode($selected_answers);

// Store result
$sql_insert = "INSERT INTO student_quiz_results (student_name, class, subject, topic, quiz_id, score, selected_answers) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("sssssis", $student_name, $class, $subject, $topic, $quiz_id, $score, $selected_answers_json);
$stmt_insert->execute();
$stmt_insert->close();

$conn->close();

// Redirect to student_course_view.php
header("Location: student_course_view.php?subject=" . urlencode($subject) . "&class=" . urlencode($class) . "&status=submitted");
exit();
?>