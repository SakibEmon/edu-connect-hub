<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_id = $_SESSION['id'];

$sql = "SELECT q.topic, r.score, r.total_questions, r.attempted_on 
        FROM quiz_results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.student_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($topic, $score, $total_questions, $attempted_on);

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="grades.txt"');

echo "Quiz Results for " . $_SESSION['name'] . "\n\n";

while ($stmt->fetch()) {
    echo "Topic: $topic\n";
    echo "Score: $score / $total_questions\n";
    echo "Attempted On: $attempted_on\n\n";
}
?>
