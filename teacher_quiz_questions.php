<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$quiz_id = $_GET['quiz_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_option = $_POST['correct_option'];

    $sql = "INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $quiz_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Questions</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <h1>Add Questions</h1>
    <form method="POST">
        <label>Question:</label>
        <input type="text" name="question" required>

        <label>Option A:</label>
        <input type="text" name="option_a" required>

        <label>Option B:</label>
        <input type="text" name="option_b" required>

        <label>Option C:</label>
        <input type="text" name="option_c" required>

        <label>Option D:</label>
        <input type="text" name="option_d" required>

        <label>Correct Option:</label>
        <select name="correct_option">
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select>

        <button type="submit">Add Question</button>
    </form>
    <a href="teacher_dashboard.php">Finish</a>
</body>
</html>
