<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.html");
    exit();
}

$teacher_id = $_SESSION['id'];

// Fetch teacher's class and subject
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

// Success message
$status_msg = '';
if (isset($_GET['added']) && $_GET['added'] === '1') {
    $status_msg = "<p class='status success'>Quiz added successfully to your list!</p>";
} elseif (isset($_GET['dropped']) && $_GET['dropped'] === '1') {
    $status_msg = "<p class='status danger'>Quiz removed from your list.</p>";
}

// Fetch assigned quizzes
$assigned_quizzes = [];
$checkStmt = $conn->prepare("SELECT quiz_id FROM teacher_quizzes WHERE class = ? AND subject = ?");
if (!$checkStmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "<p class='status danger'>Database error.</p>";
    exit();
}
$checkStmt->bind_param("ss", $class, $subject);
if (!$checkStmt->execute()) {
    error_log("Execute failed: " . $checkStmt->error);
    echo "<p class='status danger'>Database error.</p>";
    exit();
}
$checkResult = $checkStmt->get_result();
while ($row = $checkResult->fetch_assoc()) {
    $assigned_quizzes[] = $row['quiz_id'];
}
$checkStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quizzes</title>
    <link rel="stylesheet" href="css/teacher_quiz.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1><span class="icon icon-title"></span>Available Quizzes for Class <?php echo htmlspecialchars($class); ?> - <?php echo htmlspecialchars($subject); ?></h1>
        <?php echo $status_msg; ?>

        <div class="quiz-list">
            <?php
            $query = "SELECT q.id, q.chapter, q.topic, q.created_at 
                      FROM quizzes q 
                      WHERE q.class = ? AND q.subject = ?
                      ORDER BY q.created_at DESC";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                echo "<p class='status danger'>Database error.</p>";
                exit();
            }
            $stmt->bind_param("ss", $class, $subject);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                echo "<p class='status danger'>Database error.</p>";
                exit();
            }
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo "<p class='no-quizzes'>No quizzes available for your subject. <a href='contact_admin.php' class='contact-link'>Contact Admin to add quizzes</a>.</p>";
            } else {
                while ($quiz = $result->fetch_assoc()) {
                    $quiz_id = $quiz['id'];

                    echo "<div class='card quiz-box'>";
                    echo "<h3><span class='icon icon-quiz'></span>Quiz Details</h3>";
                    echo "<p><strong>Chapter:</strong> " . htmlspecialchars($quiz['chapter']) . "</p>";
                    echo "<p><strong>Topic:</strong> " . htmlspecialchars($quiz['topic']) . "</p>";
                    echo "<p><strong>Created:</strong> " . htmlspecialchars($quiz['created_at']) . "</p>";
                    echo "<div class='button-group'>";
                    // View button
                    echo "<form method='GET' action='teacher_view_quiz.php' class='inline-form'>";
                    echo "<input type='hidden' name='quiz_id' value='" . $quiz_id . "'>";
                    echo "<button type='submit' class='action-btn view-btn'>View</button>";
                    echo "</form>";

                    // Add or Drop button
                    if (in_array($quiz_id, $assigned_quizzes)) {
                        echo "<form method='POST' action='drop_quiz.php' class='inline-form'>";
                        echo "<input type='hidden' name='quiz_id' value='" . $quiz_id . "'>";
                        echo "<input type='hidden' name='class' value='" . htmlspecialchars($class) . "'>";
                        echo "<input type='hidden' name='subject' value='" . htmlspecialchars($subject) . "'>";
                        echo "<button type='submit' class='action-btn drop-btn'>Drop</button>";
                        echo "</form>";
                    } else {
                        echo "<form method='POST' action='assign_quiz.php' class='inline-form'>";
                        echo "<input type='hidden' name='quiz_id' value='" . $quiz_id . "'>";
                        echo "<input type='hidden' name='class' value='" . htmlspecialchars($class) . "'>";
                        echo "<input type='hidden' name='subject' value='" . htmlspecialchars($subject) . "'>";
                        echo "<button type='submit' class='action-btn add-btn'>Add</button>";
                        echo "</form>";
                    }
                    echo "</div>";
                    echo "</div>";
                }
            }

            $stmt->close();
            ?>
        </div>
        <a href="teacher_dashboard.php" class="back-link"><span class="icon icon-back"></span>Back to Dashboard</a>
    </div>
</body>
</html>