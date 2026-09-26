<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

// টিচারের ক্লাস ও সাবজেক্ট বের করো
$teacher_id = $_SESSION['id'];
$query = "SELECT class, subject FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$stmt->bind_result($class, $subject);
$stmt->fetch();
$stmt->close();

// কুইজ যুক্ত করার মেসেজ
$status_msg = '';
if (isset($_GET['added']) && $_GET['added'] == '1') {
    $status_msg = "<p style='color: green;'>Quiz added to your list successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Quiz from Admin</title>
    <link rel="stylesheet" href="css/teacher_dashboard.css">
</head>
<body>
<div class="main">
    <h2>Available Quizzes for Class <?= htmlspecialchars($class) ?> - <?= htmlspecialchars($subject) ?></h2>
    <?= $status_msg ?>

    <div class="quiz-list">
        <?php
        $query = "SELECT q.id, q.chapter, q.topic, q.created_at 
                  FROM quizzes q 
                  WHERE q.class = ? AND q.subject = ?
                  ORDER BY q.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $class, $subject);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "<p>No quizzes available from admin for your subject.</p>";
        } else {
            while ($quiz = $result->fetch_assoc()) {
                echo "<div class='quiz-box'>";
                echo "<strong>Chapter:</strong> " . htmlspecialchars($quiz['chapter']) . "<br>";
                echo "<strong>Topic:</strong> " . htmlspecialchars($quiz['topic']) . "<br>";
                echo "<strong>Created:</strong> " . htmlspecialchars($quiz['created_at']) . "<br>";

                // Check if already assigned using class + subject + quiz_id
                $checkStmt = $conn->prepare("SELECT 1 FROM teacher_quizzes WHERE class = ? AND subject = ? AND quiz_id = ?");
                $checkStmt->bind_param("ssi", $class, $subject, $quiz['id']);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    echo "<em>✅ Already Added</em>";
                } else {
                    echo "<form method='POST' action='assign_quiz.php'>";
                    echo "<input type='hidden' name='quiz_id' value='" . $quiz['id'] . "'>";
                    echo "<input type='hidden' name='class' value='" . $class . "'>";
                    echo "<input type='hidden' name='subject' value='" . $subject . "'>";
                    echo "<button type='submit'>Add to My Quiz</button>";
                    echo "</form>";
                }

                $checkStmt->close();
                echo "</div><hr>";
            }
        }

        $stmt->close();
        ?>
    </div>
</div>
</body>
</html>
