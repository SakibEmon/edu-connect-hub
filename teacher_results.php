<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$teacher_id = $_SESSION['id'];
$subject = $_SESSION['subject'];

// Handle result deletion
if (isset($_GET['delete_result'])) {
    $result_id = $_GET['delete_result'];
    $sql_delete = "DELETE FROM student_quiz_results WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $result_id);
    if ($stmt_delete->execute()) {
        echo "<script>alert('Result deleted successfully!'); window.location.href='teacher_results.php';</script>";
    } else {
        echo "<script>alert('Failed to delete result.');</script>";
    }
    $stmt_delete->close();
}

// Handle result editing
if (isset($_POST['edit_result'])) {
    $result_id = $_POST['result_id'];
    $new_score = $_POST['new_score'];

    $sql_edit = "UPDATE student_quiz_results SET score = ? WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("ii", $new_score, $result_id);
    if ($stmt_edit->execute()) {
        echo "<script>alert('Score updated successfully!'); window.location.href='teacher_results.php';</script>";
    } else {
        echo "<script>alert('Failed to update score.');</script>";
    }
    $stmt_edit->close();
}

// Fetch all student results for quizzes created by this teacher
$sql_results = "
    SELECT s.id AS student_id, s.username, q.topic, r.id AS result_id, r.selected_answer, r.score, r.submission_time
    FROM student_quiz_results r
    JOIN quizzes q ON r.quiz_id = q.id
    JOIN users s ON r.student_id = s.id
    WHERE q.teacher_id = ? AND q.subject = ?
    ORDER BY r.submission_time DESC";

$stmt_results = $conn->prepare($sql_results);
$stmt_results->bind_param("is", $teacher_id, $subject);
$stmt_results->execute();
$result_results = $stmt_results->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Quiz Results</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/teacher_results.css">
</head>
<body>
    <h1>Student Quiz Results for <?php echo htmlspecialchars($subject); ?></h1>

    <?php if ($result_results->num_rows > 0) { ?>
        <table border="1">
            <tr>
                <th>Student Name</th>
                <th>Topic</th>
                <th>Your Answer</th>
                <th>Score</th>
                <th>Submission Time</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result_results->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['topic']); ?></td>
                    <td><?php echo htmlspecialchars($row['selected_answer']); ?></td>
                    <td><?php echo $row['score']; ?></td>
                    <td><?php echo $row['submission_time']; ?></td>
                    <td>
                        <!-- Edit button to open a modal for score editing -->
                        <button onclick="document.getElementById('editModal<?php echo $row['result_id']; ?>').style.display='block'">Edit</button>
                        <!-- Delete button to delete the result -->
                        <a href="?delete_result=<?php echo $row['result_id']; ?>" onclick="return confirm('Are you sure you want to delete this result?');">Delete</a>
                    </td>
                </tr>

                <!-- Modal for editing score -->
                <div id="editModal<?php echo $row['result_id']; ?>" style="display:none;">
                    <div class="modal-content">
                        <h3>Edit Score</h3>
                        <form method="post">
                            <input type="hidden" name="result_id" value="<?php echo $row['result_id']; ?>">
                            <label for="new_score">New Score:</label>
                            <input type="number" name="new_score" value="<?php echo $row['score']; ?>" required>
                            <button type="submit" name="edit_result">Update Score</button>
                            <button type="button" onclick="document.getElementById('editModal<?php echo $row['result_id']; ?>').style.display='none'">Cancel</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No quiz results found for this subject.</p>
    <?php } ?>

    <a href="teacher_dashboard.php">Back to Dashboard</a>

    <style>
        /* Modal Styles */
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            margin: 50px auto;
            text-align: center;
        }
    </style>

</body>
</html>

<?php
$stmt_results->close();
$conn->close();
?>
