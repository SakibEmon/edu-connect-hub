<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$teacher_email = $_SESSION['email'];
$subject = $_SESSION['subject'];
$selected_date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// Get enrolled students
$sql_students = "SELECT u.email, u.name FROM enrollments e
                 JOIN users u ON e.student_email = u.email
                 WHERE e.teacher_email=? AND e.subject=?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("ss", $teacher_email, $subject);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

// Attendance marking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_attendance'])) {
    $today = date("Y-m-d");
    foreach ($_POST['attendance'] as $student_email => $status) {
        $check_sql = "SELECT id FROM attendance WHERE student_email=? AND teacher_email=? AND subject=? AND date=?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("ssss", $student_email, $teacher_email, $subject, $today);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows == 0) {
            $insert_sql = "INSERT INTO attendance (student_email, teacher_email, subject, status, date) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("sssss", $student_email, $teacher_email, $subject, $status, $today);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check->close();
    }

    echo "<script>alert('Attendance Marked Successfully!'); window.location.href='teacher_attendance.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1><span class="icon icon-title"></span>Take Attendance for <?php echo htmlspecialchars($subject); ?></h1>

        <!-- Attendance Form Card -->
        <div class="card">
            <h2><span class="icon icon-mark"></span>Mark Attendance</h2>
            <p class="date-info"><strong>Today: <?php echo date("Y-m-d"); ?></strong></p>
            <form method="post">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_students->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <label class="attendance-label">
                                            <input type="radio" name="attendance[<?php echo $row['email']; ?>]" value="Present" required>
                                            <span class="icon icon-present"></span> Present
                                        </label>
                                        <label class="attendance-label">
                                            <input type="radio" name="attendance[<?php echo $row['email']; ?>]" value="Absent" required>
                                            <span class="icon icon-absent"></span> Absent
                                        </label>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="mark_attendance"  class="action-btn submit-btn"><span class="icon icon-submit"></span>Submit Attendance</button>
            </form>
        </div>

        <!-- Attendance Viewer Card -->
        <div class="card">
            <h2><span class="icon icon-view"></span>View Attendance by Date</h2>
            <form method="get" class="view-form">
                <label for="date">Select Date:</label>
                <input type="date" name="date" id="date" value="<?php echo $selected_date; ?>" required>
                <button type="submit" class="action-btn view-btn"><span class="icon icon-view"></span>View</button>
            </form>

            <?php
            $sql_view = "SELECT u.name, a.status FROM attendance a
                         JOIN users u ON a.student_email = u.email
                         WHERE a.teacher_email=? AND a.subject=? AND a.date=?";
            $stmt_view = $conn->prepare($sql_view);
            $stmt_view->bind_param("sss", $teacher_email, $subject, $selected_date);
            $stmt_view->execute();
            $result_view = $stmt_view->get_result();

            $present_students = [];
            $absent_students = [];

            while ($row = $result_view->fetch_assoc()) {
                if ($row['status'] === "Present") {
                    $present_students[] = $row['name'];
                } else {
                    $absent_students[] = $row['name'];
                }
            }
            $stmt_view->close();
            ?>

            <h3><span class="icon icon-date"></span>Attendance on: <?php echo htmlspecialchars($selected_date); ?></h3>
            <div class="attendance-lists">
                <div class="list-section">
                    <h4><span class="icon icon-present"></span>Present Students</h4>
                    <ul>
                        <?php foreach ($present_students as $name) echo "<li>" . htmlspecialchars($name) . "</li>"; ?>
                    </ul>
                </div>
                <div class="list-section">
                    <h4><span class="icon icon-absent"></span>Absent Students</h4>
                    <ul>
                        <?php foreach ($absent_students as $name) echo "<li>" . htmlspecialchars($name) . "</li>"; ?>
                    </ul>
                </div>
            </div>
        </div>

        <a href="teacher_dashboard.php" class="back-link"><span class="icon icon-back"></span>Back to Dashboard</a>
    </div>

    <?php
    $stmt_students->close();
    $conn->close();
    ?>
</body>
</html>