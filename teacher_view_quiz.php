<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.html");
    exit();
}

$teacher_id = $_SESSION['id'];
$quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);

if ($quiz_id === false || $quiz_id <= 0) {
    echo "<p class='status danger'>Invalid quiz ID.</p>";
    exit();
}

// টিচারের ক্লাস ও সাবজেক্ট ফেচ করা
$query = "SELECT class, subject FROM users WHERE id = ? AND role = 'teacher'";
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "<p class='status danger'>Database error.</p>";
    exit();
}
$stmt->bind_param("i", $teacher_id);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo "<p class='status danger'>Database error.</p>";
    exit();
}
$stmt->bind_result($class, $subject);
$stmt->fetch();
$stmt->close();

if (empty($class) || empty($subject)) {
    echo "<p class='status danger'>Error: Class or subject not assigned to your account.</p>";
    exit();
}

// কুইজের ডেটা ফেচ করা
$stmt = $conn->prepare("SELECT q.* FROM quizzes q 
                        JOIN teacher_quizzes tq ON q.id = tq.quiz_id 
                        WHERE q.id = ? AND q.class = ? AND q.subject = ? AND tq.class = ? AND tq.subject = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "<p class='status danger'>Database error.</p>";
    exit();
}
$stmt->bind_param("issss", $quiz_id, $class, $subject, $class, $subject);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo "<p class='status danger'>Database error.</p>";
    exit();
}
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='status danger'>Quiz not found or not authorized.</p>";
    exit();
}

$quiz = $result->fetch_assoc();
$questions = json_decode($quiz['questions'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode failed: " . json_last_error_msg());
    echo "<p class='status danger'>Error decoding quiz questions.</p>";
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Quiz</title>
    <link rel="stylesheet" href="css/teacher_view_quiz.css">
</head>
<body>
    <div class="container">
        <h2>Quiz Details</h2>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($quiz['subject']); ?></p>
        <p><strong>Chapter:</strong> <?php echo htmlspecialchars($quiz['chapter']); ?></p>
        <p><strong>Topic:</strong> <?php echo htmlspecialchars($quiz['topic']); ?></p>
        <p><strong>Created At:</strong> <?php echo htmlspecialchars($quiz['created_at']); ?></p>
        <hr>

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-view">
                <h4>Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($q['question']); ?></h4>
                <ul>
                    <li>A: <?php echo htmlspecialchars($q['options']['A']); ?></li>
                    <li>B: <?php echo htmlspecialchars($q['options']['B']); ?></li>
                    <li>C: <?php echo htmlspecialchars($q['options']['C']); ?></li>
                    <li>D: <?php echo htmlspecialchars($q['options']['D']); ?></li>
                </ul>
                <p><strong>Correct Answer:</strong> <?php echo htmlspecialchars($q['correct']); ?></p>
            </div>
            <hr>
        <?php endforeach; ?>

        <a href="teacher_quiz.php?class=<?php echo urlencode($class); ?>" class="back-btn">🔙 Back to Quizzes</a>
    </div>
</body>
</html>