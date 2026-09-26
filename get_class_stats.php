<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || !isset($_GET['class'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$class = $_GET['class'];
$admin_id = $_SESSION['admin_id'];

// Count teachers for this class
$teacherQuery = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND class = ?");
$teacherQuery->bind_param("s", $class);
$teacherQuery->execute();
$teacherQuery->bind_result($teacherCount);
$teacherQuery->fetch();
$teacherQuery->close();

// Count students enrolled by this admin for this class
$studentQuery = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'student' AND class = ? AND created_by = ?");
$studentQuery->bind_param("si", $class, $admin_id);
$studentQuery->execute();
$studentQuery->bind_result($studentCount);
$studentQuery->fetch();
$studentQuery->close();

echo json_encode([
    "teachers" => $teacherCount,
    "students" => $studentCount
]);
