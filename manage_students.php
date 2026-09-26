<?php
session_start();
include 'db_connection.php'; // Ensure database connection

// Check if the teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Get teacher's assigned class and subject
$query = "SELECT class, subject FROM teachers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

$class = $teacher['class'];
$subject = $teacher['subject'];

// Get students under this class
$query = "SELECT * FROM students WHERE class = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $class);
$stmt->execute();
$students = $stmt->get_result();

// Handle student enrollment or removal
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['enroll'])) {
        $student_id = $_POST['student_id'];
        $query = "UPDATE students SET enrolled_subjects = CONCAT(enrolled_subjects, ', ', ?) WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $subject, $student_id);
        $stmt->execute();
    } elseif (isset($_POST['remove'])) {
        $student_id = $_POST['student_id'];
        $query = "UPDATE students SET enrolled_subjects = REPLACE(enrolled_subjects, ?, '') WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $subject, $student_id);
        $stmt->execute();
    }
    header("Location: manage_students.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add styles if needed -->
</head>
<body>

<h2>Manage Students for Class: <?php echo $class; ?> - Subject: <?php echo $subject; ?></h2>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Enrolled Subjects</th>
        <th>Action</th>
    </tr>
    <?php while ($student = $students->fetch_assoc()): ?>
    <tr>
        <td><?php echo $student['id']; ?></td>
        <td><?php echo $student['name']; ?></td>
        <td><?php echo $student['enrolled_subjects']; ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                <button type="submit" name="enroll">Enroll</button>
                <button type="submit" name="remove">Remove</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="teacher_dashboard.php">Back to Dashboard</a>

</body>
</html>
