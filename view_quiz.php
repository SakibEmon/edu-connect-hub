<?php
include 'php/db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Quiz not found.";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Quiz not found.";
    exit();
}

$quiz = $result->fetch_assoc();
$questions = json_decode($quiz['questions'], true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Quiz</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link rel="stylesheet" href="css/view_quiz.css">

</head>
<body>
    <div class="container">
        <h2>Quiz Details</h2>
        <p><strong>Subject:</strong> <?php echo $quiz['subject']; ?></p>
        <p><strong>Chapter:</strong> <?php echo $quiz['chapter']; ?></p>
        <p><strong>Topic:</strong> <?php echo $quiz['topic']; ?></p>
        <hr>

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-view">
                <h4>Q<?php echo $index + 1; ?>: <?php echo $q['question']; ?></h4>
                <ul>
                    <li>A: <?php echo $q['options']['A']; ?></li>
                    <li>B: <?php echo $q['options']['B']; ?></li>
                    <li>C: <?php echo $q['options']['C']; ?></li>
                    <li>D: <?php echo $q['options']['D']; ?></li>
                </ul>
                <p><strong>Correct Answer:</strong> <?php echo $q['correct']; ?></p>
            </div>
            <hr>
        <?php endforeach; ?>

        <a href="admin_dashboard.php?class=<?php echo $quiz['class']; ?>" class="back-btn">🔙 Back to Dashboard</a>
    </div>
</body>
</html>
