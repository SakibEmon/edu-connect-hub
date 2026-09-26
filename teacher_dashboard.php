<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.html");
    exit();
}
include 'php/db_connect.php';

$id = $_SESSION['id'];
$query = "SELECT name, class, subject FROM users WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($name, $class, $subject);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="css/teacher_dashboard.css">
    <script src="js/teacher_dashboard.js" defer></script>
</head>
<body>

<div class="topbar">
    <span class="hamburger" onclick="toggleSidebar()">☰</span>
    <h1>Teacher Dashboard</h1>
</div>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile-card">
            <h2><?= htmlspecialchars($name) ?></h2>
            <p>Class: <?= htmlspecialchars($class) ?></p>
            <p>Subject: <?= htmlspecialchars($subject) ?></p>
            <a href="php/logout.php" class="logout-btn">Log Out</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">

        <!-- Quick Actions Card -->
        <div class="card">
            <h2>Quick Actions</h2>
            <div class="actions">
                <a href="teacher_enrollment.php" class="action-box enrollment">Student Enrollment<div class="desc">Enroll new students quickly and easily</div></a>
                <a href="teacher_attendance.php" class="action-box attendance">Take Attendance<div class="desc">Record daily attendance of students</div></a>
                <a href="teacher_ppt.php" class="action-box ppt">Manage PPT<div class="desc">Upload and manage presentation files</div></a>
                <a href="teacher_quiz.php" class="action-box quiz">Add Quiz<div class="desc">Add Quiz from Admin dashboard</div></a>
                <a href="teacher_videos.php" class="action-box video">Upload Lecture Video<div class="desc">Upload & manage lecture videos</div></a>

            </div>
        </div>

        <!-- Quiz Results Card -->
        <div class="card">
            <h2>Quiz Results by Topic</h2>
            <div class="topics-list">
                <ul>
                    <?php
                    $topicsQuery = "SELECT DISTINCT topic, id AS quiz_id FROM quizzes WHERE class = ? AND subject = ?";
                    $stmt = $conn->prepare($topicsQuery);
                    $stmt->bind_param("ss", $class, $subject);
                    $stmt->execute();
                    $topicsResult = $stmt->get_result();

                    if ($topicsResult->num_rows > 0) {
                        while ($row = $topicsResult->fetch_assoc()) {
                            echo '<li>
                                    <button class="topic-btn" onclick="toggleResults('.$row['quiz_id'].')">'.$row['topic'].'</button>
                                    <div id="results-'.$row['quiz_id'].'" class="results-container" style="display:none;">
                                        <p>Loading...</p>
                                    </div>
                                  </li>';
                        }
                    } else {
                        echo '<li>No quizzes found for this class & subject.</li>';
                    }
                    $stmt->close();
                    ?>
                </ul>
            </div>
        </div>

    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
