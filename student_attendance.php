<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$student_email = $_SESSION['email'];
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build SQL with conditions
$sql = "SELECT subject, date, status FROM attendance WHERE student_email=?";
$params = [$student_email];
$types = "s";

if (!empty($subject_filter)) {
    $sql .= " AND subject=?";
    $params[] = $subject_filter;
    $types .= "s";
}

if (!empty($start_date)) {
    $sql .= " AND date >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if (!empty($end_date)) {
    $sql .= " AND date <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$sql .= " ORDER BY date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance</title>
    <link rel="stylesheet" href="css/student_attendence.css">
</head>
<body>

    <h2>📚 My Attendance Record</h2>

    <form method="get" class="filter-form">
        <label for="subject">Subject:</label>
        <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($subject_filter); ?>">

        <label for="start_date">From:</label>
        <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>">

        <label for="end_date">To:</label>
        <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

        <input type="submit" value="Filter">
        <a href="student_attendance.php" class="reset-link">Reset</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo strtolower($row['status']); ?>">
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">No attendance records found for this filter.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
