<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_name = $_GET['student'] ?? null;
$quiz_id = $_GET['quiz_id'] ?? null;

if (!$student_name || !$quiz_id) {
    echo "Invalid request!";
    exit();
}

// Fetch quiz details
$sql_quiz = "SELECT topic, subject, questions FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
if ($result_quiz->num_rows == 0) {
    echo "Quiz not found!";
    exit();
}
$quiz = $result_quiz->fetch_assoc();
$topic = $quiz['topic'];
$subject = $quiz['subject'];
$questions = json_decode($quiz['questions'], true);
$stmt_quiz->close();

// Fetch class for this quiz
$sql_class = "SELECT tq.class 
              FROM teacher_quizzes tq 
              INNER JOIN quizzes q ON tq.quiz_id = q.id 
              WHERE q.id = ? LIMIT 1";
$stmt_class = $conn->prepare($sql_class);
$stmt_class->bind_param("i", $quiz_id);
$stmt_class->execute();
$result_class = $stmt_class->get_result();
if ($result_class->num_rows == 0) {
    echo "Class not found!";
    exit();
}
$class_data = $result_class->fetch_assoc();
$class = $class_data['class'];
$stmt_class->close();

// Fetch student results
$sql_result = "SELECT score, selected_answers FROM student_quiz_results WHERE student_name = ? AND quiz_id = ?";
$stmt_result = $conn->prepare($sql_result);
$stmt_result->bind_param("si", $student_name, $quiz_id);
$stmt_result->execute();
$result_result = $stmt_result->get_result();
if ($result_result->num_rows == 0) {
    echo "No results found!";
    exit();
}
$result = $result_result->fetch_assoc();
$score = $result['score'];
$selected_answers = json_decode($result['selected_answers'], true);
$stmt_result->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results: <?php echo htmlspecialchars($topic); ?></title>
    <link rel="stylesheet" href="css/view_result.css">
</head>
<body>
<div class="container">
    <h2>Quiz Results: <?php echo htmlspecialchars($topic); ?> (<?php echo htmlspecialchars($subject); ?>)</h2>
    <p class="score"><strong>Your Score:</strong> <?php echo $score; ?> / <?php echo count($questions); ?></p>

    <?php foreach ($questions as $index => $q) { ?>
        <div class="question-block">
            <h4>Question <?php echo ($index + 1); ?>: <?php echo htmlspecialchars($q['question']); ?></h4>
            <div class="options">
                <?php foreach ($q['options'] as $key => $option) { ?>
                    <p class="option <?php echo ($key == $q['correct']) ? 'correct' : 'incorrect'; ?>">
                        <?php echo htmlspecialchars($option); ?>
                        <?php 
                        $selected = isset($selected_answers[$index + 1]) && $selected_answers[$index + 1] == $key;
                        if ($selected) { ?>
                            <span class="status"><?php echo ($key == $q['correct']) ? '✔ Your Answer (Correct)' : '✘ Your Answer'; ?></span>
                        <?php } ?>
                    </p>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <a href="student_course_view.php?subject=<?php echo urlencode($subject); ?>&class=<?php echo urlencode($class); ?>" class="back-btn">← Back to Course</a>

</div>
</body>
</html>

<?php
$conn->close();
?>