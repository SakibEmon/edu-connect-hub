<?php
session_start();
include 'php/db_connect.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizId = intval($_POST['quiz_id'] ?? 0);
    $class = intval($_POST['class'] ?? 0);

    if ($quizId > 0 && $class > 0) {
        $conn->begin_transaction();

        try {
            $stmt_teacher = $conn->prepare("DELETE FROM teacher_quizzes WHERE quiz_id = ?");
            $stmt_teacher->bind_param("i", $quizId);
            $stmt_teacher->execute();
            $stmt_teacher->close();
            $stmt_quiz = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
            $stmt_quiz->bind_param("i", $quizId);
            $stmt_quiz->execute();
            $stmt_quiz->close();
            $conn->commit();
            header("Location: admin_dashboard.php?class=$class&status=deleted");
            exit();
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            error_log("Error deleting quiz: " . $e->getMessage());
            header("Location: admin_dashboard.php?class=$class&status=error");
            exit();
        }
    } else {
        header("Location: admin_dashboard.php?status=invalid");
        exit();
    }
} else {
    header("Location: admin_dashboard.php?status=unauthorized");
    exit();
}
?>