<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_id = $_SESSION['id'];

// Fetch quiz results for the student
$sql_results = "
    SELECT q.topic, q.question, q.option1, q.option2, q.option3, q.option4, q.correct_answer, 
           r.selected_answer, r.score, r.submission_time
    FROM student_quiz_results r
    JOIN quizzes q ON r.quiz_id = q.id
    WHERE r.student_id = ?
    ORDER BY r.submission_time DESC";

$stmt_results = $conn->prepare($sql_results);
$stmt_results->bind_param("i", $student_id);
$stmt_results->execute();
$result_results = $stmt_results->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <h1>Your Quiz Results</h1>

    <?php if ($result_results->num_rows > 0) { ?>
        <table border="1">
            <tr>
                <th>Topic</th>
                <th>Question</th>
                <th>Your Answer</th>
                <th>Correct Answer</th>
                <th>Score</th>
                <th>Submission Time</th>
            </tr>
            <?php while ($row = $result_results->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['topic']); ?></td>
                    <td><?php echo htmlspecialchars($row['question']); ?></td>
                    <td><?php echo htmlspecialchars($row[$row['selected_answer']]); ?></td>
                    <td><?php echo htmlspecialchars($row[$row['correct_answer']]); ?></td>
                    <td><?php echo $row['score']; ?></td>
                    <td><?php echo $row['submission_time']; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No quiz results found.</p>
    <?php } ?>

    <a href="student_dashboard.php">Back to Dashboard</a>
</body>
</html>

<?php
$stmt_results->close();
$conn->close();
?>
