<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_email = $_SESSION['email'];

// Get class info from enrollments
$sql_class = "SELECT DISTINCT class FROM enrollments WHERE student_email=?";
$stmt_class = $conn->prepare($sql_class);
$stmt_class->bind_param("s", $student_email);
$stmt_class->execute();
$result_class = $stmt_class->get_result();

$class_name = "N/A";
if ($row = $result_class->fetch_assoc()) {
    $class_name = $row['class'];
}

// Get enrolled courses
$sql_courses = "SELECT DISTINCT subject FROM enrollments WHERE student_email=?";
$stmt_courses = $conn->prepare($sql_courses);
$stmt_courses->bind_param("s", $student_email);
$stmt_courses->execute();
$result_courses = $stmt_courses->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/student_dashboard.css">
</head>
<body>
    <header class="hero-header">
        <img src="image\Sdashboard.gif" alt="Academic Banner">
        <div class="header-overlay">
            <h1 class="main-heading">🎓 ACADEMIC CORNER</h1>
            <p class="sub-heading">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
    </header>

    <div class="container">

        <!-- Show Student Class -->
        <p class="student-class">📚 Class: <strong><?php echo htmlspecialchars($class_name); ?></strong></p>

        <!-- Attendance Button -->
        <div class="attendance-button-container">
            <a href="student_attendance.php" class="dashboard-btn">📅 View My Attendance</a>
        </div>

        <h2>Your Enrolled Courses</h2>
        <?php if ($result_courses->num_rows > 0) { ?>
            <ul class="course-list">
                <?php while ($row = $result_courses->fetch_assoc()) { ?>
                    <li>
                        <a href="student_course_view.php?subject=<?php echo urlencode($row['subject']); ?>&class=<?php echo urlencode($class_name); ?>">
                            <?php echo htmlspecialchars($row['subject']); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p class="no-courses">You are not enrolled in any courses.</p>
        <?php } ?>

        <a href="php/logout.php" class="logout-btn">🚪 Log Out</a>
    </div>
</body>
</html>

<?php
$stmt_class->close();
$stmt_courses->close();
$conn->close();
?>