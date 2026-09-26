<?php
session_start();
include 'php/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['class'])) {
    echo json_encode([]);
    exit();
}

$class = $_GET['class'];

$stmt = $conn->prepare("SELECT DISTINCT subject, chapter, topic FROM quiz_questions WHERE class = ?");
$stmt->bind_param("s", $class);
$stmt->execute();

$result = $stmt->get_result();
$quizzes = [];

while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}

echo json_encode($quizzes);
?>
