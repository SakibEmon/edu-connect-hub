<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    die("Unauthorized");
}

include '../php/db_connect.php';

if (!isset($_GET['quiz_id'])) {
    die("Quiz ID not provided");
}

$quizId = intval($_GET['quiz_id']);

$query = "SELECT student_name, score, attempted_at 
          FROM student_quiz_results 
          WHERE quiz_id = ? 
          ORDER BY score DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $quizId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<table class="results-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Score</th>
                    <th>Attempted At</th>
                </tr>
            </thead>
            <tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>'.htmlspecialchars($row['student_name']).'</td>
                <td>'.$row['score'].'</td>
                <td>'.$row['attempted_at'].'</td>
              </tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>No results found for this quiz.</p>';
}

$stmt->close();
$conn->close();
?>
