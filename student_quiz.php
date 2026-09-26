<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_name = $_SESSION['name'];
$quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
$subject = isset($_GET['subject']) ? $_GET['subject'] : null;
$class = isset($_GET['class']) ? $_GET['class'] : null;

if (!$quiz_id || !$subject || !$class) {
    echo "Invalid quiz, subject, or class!";
    exit();
}

// Check if student has already attempted
$sql_check = "SELECT id FROM student_quiz_results WHERE student_name = ? AND quiz_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("si", $student_name, $quiz_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows > 0) {
    echo "You have already attempted this quiz!";
    exit();
}
$stmt_check->close();

// Fetch quiz details
$sql_quiz = "SELECT topic, questions FROM quizzes WHERE id = ?";
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
$questions = json_decode($quiz['questions'], true);
$stmt_quiz->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz: <?php echo htmlspecialchars($topic); ?></title>
    <link rel="stylesheet" href="css/student_quiz.css">
    <script>
        // Prevent back navigation
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Timer logic
        window.onload = function () {
            let timeLeft = 10 * 60; // 10 minutes in seconds
            const timerElement = document.getElementById('timer');
            const quizForm = document.getElementById('quizForm');

            const timerInterval = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.textContent = `Time Left: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Time is up! Submitting quiz...');
                    quizForm.submit();
                }
                timeLeft--;
            }, 1000);
        };
    </script>
</head>
<body>
<div class="container">
    <h2>Quiz: <?php echo htmlspecialchars($topic); ?> (<?php echo htmlspecialchars($subject); ?>)</h2>
    <div class="timer" id="timer">Time Left: 10:00</div>
    <form id="quizForm" action="submit_quiz.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?php echo htmlspecialchars($quiz_id); ?>">
        <input type="hidden" name="subject" value="<?php echo htmlspecialchars($subject); ?>">
        <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">
        <input type="hidden" name="topic" value="<?php echo htmlspecialchars($topic); ?>">
        <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>">

        <?php foreach ($questions as $index => $q) { ?>
            <div class="question-block">
                <h4>Question <?php echo ($index + 1); ?>: <?php echo htmlspecialchars($q['question']); ?></h4>
                <div class="options">
                    <label class="option-label">
                        <input type="radio" name="q<?php echo ($index + 1); ?>" value="A" required>
                        <span class="option-text">A. <?php echo htmlspecialchars($q['options']['A']); ?></span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="q<?php echo ($index + 1); ?>" value="B">
                        <span class="option-text">B. <?php echo htmlspecialchars($q['options']['B']); ?></span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="q<?php echo ($index + 1); ?>" value="C">
                        <span class="option-text">C. <?php echo htmlspecialchars($q['options']['C']); ?></span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="q<?php echo ($index + 1); ?>" value="D">
                        <span class="option-text">D. <?php echo htmlspecialchars($q['options']['D']); ?></span>
                    </label>
                </div>
            </div>
        <?php } ?>

        <button type="submit" class="save-btn">Submit Quiz</button>
    </form>
</div>
</body>
</html>

<?php
$conn->close();
?>