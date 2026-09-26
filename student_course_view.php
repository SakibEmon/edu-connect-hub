<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_name = $_SESSION['name'];
$subject = isset($_GET['subject']) ? $_GET['subject'] : null;
$class = isset($_GET['class']) ? $_GET['class'] : null;

if (!$subject || !$class) {
    echo "Invalid subject or class!";
    exit();
}

// Get assigned quizzes for the class and subject
$sql_quiz = "SELECT DISTINCT q.id, q.topic 
             FROM quizzes q 
             INNER JOIN teacher_quizzes tq ON q.id = tq.quiz_id 
             WHERE tq.class = ? AND tq.subject = ? AND q.subject = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("sss", $class, $subject, $subject);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();

// Get PPTs
$sql_ppt = "SELECT file_name, file_path FROM teacher_ppt WHERE subject = ?";
$stmt_ppt = $conn->prepare($sql_ppt);
$stmt_ppt->bind_param("s", $subject);
$stmt_ppt->execute();
$result_ppt = $stmt_ppt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($subject); ?> - Course</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/student_dashboard.css">
    <link rel="stylesheet" href="css/student_course_view.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<h2><?php echo htmlspecialchars($subject); ?> - Course Contents (Class <?php echo htmlspecialchars($class); ?>)</h2>

<div class="cards-container">
    <!-- Study Materials Card -->
    <div class="card">
        <h3>Study Materials</h3>
        <?php if ($result_ppt->num_rows > 0) { ?>
            <ul>
                <?php while ($row = $result_ppt->fetch_assoc()) { ?>
                    <li>
                        <span class="file-name"><?php echo htmlspecialchars($row['file_name']); ?></span>
                        <div class="button-container">
                            <a href="<?php echo htmlspecialchars($row['file_path']); ?>" download class="download-btn">Download</a>
                            <a href="student_view_ppt.php?file=<?php echo urlencode($row['file_path']); ?>&subject=<?php echo urlencode($subject); ?>&class=<?php echo urlencode($class); ?>" target="_blank" class="view-btn">View</a>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>No study materials uploaded for this course.</p>
        <?php } ?>
    </div>

    <!-- Available Quizzes Card -->
    <div class="card">
        <h3>Available Quizzes</h3>
        <?php if ($result_quiz->num_rows > 0) { ?>
            <ul>
                <?php while ($row = $result_quiz->fetch_assoc()) {
                    $topic = $row['topic'];
                    $quiz_id = $row['id'];

                    // Check if student has already attempted
                    $sql_attempt = "SELECT score FROM student_quiz_results WHERE student_name = ? AND quiz_id = ?";
                    $stmt_attempt = $conn->prepare($sql_attempt);
                    $stmt_attempt->bind_param("si", $student_name, $quiz_id);
                    $stmt_attempt->execute();
                    $result_attempt = $stmt_attempt->get_result();
                    $attempt_data = $result_attempt->fetch_assoc();
                    $attempted = $result_attempt->num_rows > 0;
                    $score = $attempt_data['score'] ?? null;
                    $stmt_attempt->close();
                ?>
                    <li>
                        <span class="quiz-topic">
                            <?php echo htmlspecialchars($topic); ?>
                            <?php if ($attempted) { ?>
                                (Score: <?php echo $score !== null ? $score : 'Not graded yet'; ?>)
                            <?php } ?>
                        </span>
                        <div class="button-container">
                            <?php if ($attempted) { ?>
                                <a href="view_result.php?quiz_id=<?php echo urlencode($quiz_id); ?>&student=<?php echo urlencode($student_name); ?>" class="view-btn">View</a>
                            <?php } else { ?>
                                <a href="student_quiz.php?quiz_id=<?php echo urlencode($quiz_id); ?>&subject=<?php echo urlencode($subject); ?>&class=<?php echo urlencode($class); ?>" class="attend-btn">Attend Quiz</a>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>No quizzes assigned for this course.</p>
        <?php } ?>
    </div>
</div>

<a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>

</body>
</html>

<?php
$stmt_quiz->close();
$stmt_ppt->close();
$conn->close();
?>
