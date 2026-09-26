<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^(6|7|8|9|10)$/"]]);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^(Bangla|English|Math|Physics|Chemistry|Biology)$/"]]);
    $chapter = filter_input(INPUT_POST, 'chapter', FILTER_SANITIZE_STRING);
    $topic = filter_input(INPUT_POST, 'topic', FILTER_SANITIZE_STRING);
    $admin_id = $_SESSION['admin_id'];

    // Validate admin_id exists in admin_users table
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed (admin_id check): " . $conn->error);
        header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=Database error");
        exit();
    }
    $stmt->bind_param("i", $admin_id);
    if (!$stmt->execute()) {
        error_log("Execute failed (admin_id check): " . $stmt->error);
        header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=Database error");
        exit();
    }
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("Invalid admin_id: $admin_id");
        header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=Invalid admin ID");
        exit();
    }
    $stmt->close();

    // Validate inputs
    if (!$class || !$subject) {
        header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=Invalid class or subject");
        exit();
    }

    $questions = [];
    for ($i = 1; $i <= 10; $i++) {
        $qText = filter_input(INPUT_POST, "question_$i", FILTER_SANITIZE_STRING);
        $optA = filter_input(INPUT_POST, "q{$i}_a", FILTER_SANITIZE_STRING);
        $optB = filter_input(INPUT_POST, "q{$i}_b", FILTER_SANITIZE_STRING);
        $optC = filter_input(INPUT_POST, "q{$i}_c", FILTER_SANITIZE_STRING);
        $optD = filter_input(INPUT_POST, "q{$i}_d", FILTER_SANITIZE_STRING);
        $correct = filter_input(INPUT_POST, "q{$i}_correct", FILTER_SANITIZE_STRING);

        if (empty($qText) || empty($optA) || empty($optB) || empty($optC) || empty($optD) || empty($correct)) {
            header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=Missing question data");
            exit();
        }

        $questions[] = [
            'question' => $qText,
            'options' => ['A' => $optA, 'B' => $optB, 'C' => $optC, 'D' => $optD],
            'correct' => $correct
        ];
    }

    $quizData = json_encode($questions, JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON encode failed: " . json_last_error_msg());
        header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=JSON encoding failed");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO quizzes (class, subject, chapter, topic, questions, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed (insert quiz): " . $conn->error);
        header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=Database error");
        exit();
    }
    $stmt->bind_param("sssssi", $class, $subject, $chapter, $topic, $quizData, $admin_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?class=" . urlencode($class) . "&status=success&msg=Quiz added successfully");
    } else {
        error_log("Execute failed (insert quiz): " . $stmt->error);
        header("Location: add_quiz.php?class=" . urlencode($class) . "&status=error&msg=" . urlencode($stmt->error));
    }
    $stmt->close();
} else {
    header("Location: admin_dashboard.php");
}
?>