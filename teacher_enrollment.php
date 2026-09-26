<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$teacher_email = $_SESSION['email'];
$class = $_SESSION['class'];
$subject = $_SESSION['subject'];

// Handle enroll
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enroll'])) {
    $student_name = $_POST['student_name'];
    $student_email = $_POST['student_email'];

    // Ensure student belongs to the same class
    $verify_sql = "SELECT * FROM users WHERE email=? AND class=?";
    $stmt_verify = $conn->prepare($verify_sql);
    $stmt_verify->bind_param("ss", $student_email, $class);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows > 0) {
        // Proceed to enroll
        $sql_enroll = "INSERT INTO enrollments (student_name, student_email, class, subject, teacher_email)
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_enroll = $conn->prepare($sql_enroll);
        $stmt_enroll->bind_param("sssss", $student_name, $student_email, $class, $subject, $teacher_email);
        $stmt_enroll->execute();
        $stmt_enroll->close();

        echo "<script>alert('Student Enrolled Successfully!'); window.location.href='teacher_enrollment.php';</script>";
    } else {
        echo "<script>alert('Enrollment failed: Student is not in your class.'); window.location.href='teacher_enrollment.php';</script>";
    }

    $stmt_verify->close();
    exit();
}

// Handle remove
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove'])) {
    $student_email = $_POST['student_email'];

    $sql_remove = "DELETE FROM enrollments WHERE student_email=? AND subject=? AND teacher_email=?";
    $stmt_remove = $conn->prepare($sql_remove);
    $stmt_remove->bind_param("sss", $student_email, $subject, $teacher_email);
    $stmt_remove->execute();
    $stmt_remove->close();

    echo "<script>alert('Student Removed!'); window.location.href='teacher_enrollment.php';</script>";
    exit();
}

// Fetch students not yet enrolled (same class only)
$sql_students = "SELECT name, email FROM users WHERE role='student' AND class=? AND email NOT IN 
                 (SELECT student_email FROM enrollments WHERE subject=? AND teacher_email=?)";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("sss", $class, $subject, $teacher_email);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

// Fetch enrolled students
$sql_enrolled = "SELECT student_name, student_email FROM enrollments 
                 WHERE teacher_email=? AND subject=?";
$stmt_enrolled = $conn->prepare($sql_enrolled);
$stmt_enrolled->bind_param("ss", $teacher_email, $subject);
$stmt_enrolled->execute();
$result_enrolled = $stmt_enrolled->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment</title>
    <link rel="stylesheet" href="css/enrollment.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1><span class="icon icon-title"></span>Enroll Students in Class <?php echo htmlspecialchars($class); ?> - <?php echo htmlspecialchars($subject); ?></h1>

        <div class="card">
            <h2><span class="icon icon-available"></span>Available Students</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_students->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                                        <input type="hidden" name="student_email" value="<?php echo htmlspecialchars($row['email']); ?>">
                                        <button type="submit" name="enroll" class="action-btn enroll-btn"><span class="icon icon-enroll"></span>Enroll</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2><span class="icon icon-enrolled"></span>Enrolled Students</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_enrolled->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_email']); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="student_email" value="<?php echo htmlspecialchars($row['student_email']); ?>">
                                        <button type="submit" name="remove" class="action-btn remove-btn" onclick="return confirm('Are you sure to remove?');"><span class="icon icon-remove"></span>Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="teacher_dashboard.php" class="back-link"><span class="icon icon-back"></span>Back to Dashboard</a>
    </div>

    <?php
    $stmt_students->close();
    $stmt_enrolled->close();
    $conn->close();
    ?>
</body>
</html>