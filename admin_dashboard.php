<?php
session_start();
include 'php/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Expert Dashboard</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Teacher Expert Dashboard</h1>
        <a class="logout-btn" href="admin_logout.php">Logout</a>
    </header>

    <section class="admin-info">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h2>
        <p>Status: <span class="status-online">Online</span></p>
    </section>

    <!-- Status Message -->
    <?php if (isset($_GET['status'])): ?>
        <div class="status-msg 
            <?php echo ($_GET['status'] == 'success') ? 'status-success' : 
                        (($_GET['status'] == 'deleted') ? 'status-success' : 'status-error'); ?>">
            <?php
            if ($_GET['status'] == 'success') {
                echo "✅ Quiz added successfully.";
            } elseif ($_GET['status'] == 'deleted') {
                echo "✅ Quiz deleted successfully.";
            } elseif ($_GET['status'] == 'error') {
                echo "❌ Failed to " . (isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : "process the request.");
            }
            ?>
        </div>
    <?php endif; ?>

    <section id="class-selection">
        <h3>Select a Class to Manage:</h3>
        <div class="class-buttons">
            <?php
            $classes = [6, 7, 8, 9, 10];
            foreach ($classes as $class) {
                echo "<a href='?class=$class' class='class-btn'>Class $class</a>";
            }
            ?>
        </div>
    </section>

    <?php if (isset($_GET['class'])): ?>
        <?php $selectedClass = filter_input(INPUT_GET, 'class', FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^(6|7|8|9|10)$/"]]); ?>
        <?php if (!$selectedClass): ?>
            <div class="status-msg status-error">❌ Invalid class selected.</div>
        <?php else: ?>
            <section id="dashboard-options">
                <h3>Managing: Class <?php echo htmlspecialchars($selectedClass); ?></h3>

                <div class="option-card" onclick="location.href='add_quiz.php?class=<?php echo htmlspecialchars($selectedClass); ?>'">
                    <h3>Quiz Add</h3>
                    <p>Create and manage quizzes for selected class.</p>
                </div>

                <button class="switch-btn" onclick="location.href='admin_dashboard.php'">Switch to Different Class</button>
            </section>

            <!-- Quiz List -->
            <section id="quiz-list">
                <h3>Existing Quizzes for Class <?php echo htmlspecialchars($selectedClass); ?>:</h3>
                <div id="quiz-container">
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE class = ? ORDER BY created_at DESC");
                    if (!$stmt) {
                        error_log("Prepare failed (fetch quizzes): " . $conn->error);
                        echo "<div class='status-msg status-error'>❌ Database error while fetching quizzes.</div>";
                    } else {
                        $stmt->bind_param("s", $selectedClass);
                        if (!$stmt->execute()) {
                            error_log("Execute failed (fetch quizzes): " . $stmt->error);
                            echo "<div class='status-msg status-error'>❌ Database error while fetching quizzes.</div>";
                        } else {
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($quiz = $result->fetch_assoc()) {
                                    echo "<div class='quiz-box'>";
                                    echo "<strong>Subject:</strong> " . htmlspecialchars($quiz['subject']) . "<br>";
                                    echo "<strong>Chapter:</strong> " . htmlspecialchars($quiz['chapter']) . "<br>";
                                    echo "<strong>Topic:</strong> " . htmlspecialchars($quiz['topic']) . "<br>";
                                    echo "<strong>Created At:</strong> " . htmlspecialchars($quiz['created_at']) . "<br>";

                                    echo "<div class='quiz-actions'>";
                                    echo "<form action='view_quiz.php' method='GET'>";
                                    echo "<input type='hidden' name='id' value='" . $quiz['id'] . "'>";
                                    echo "<button class='view-btn' type='submit'>View</button>";
                                    echo "</form>";

                                    echo "<form action='delete_quiz.php' method='POST' onsubmit=\"return confirm('Are you sure to delete this quiz?');\">";
                                    echo "<input type='hidden' name='quiz_id' value='" . $quiz['id'] . "'>";
                                    echo "<input type='hidden' name='class' value='" . htmlspecialchars($selectedClass) . "'>";
                                    echo "<button class='delete-btn' type='submit'>Delete</button>";
                                    echo "</form>";
                                    echo "</div>";

                                    echo "</div><hr>";
                                }
                            } else {
                                echo "<p>No quizzes found for this class.</p>";
                            }
                        }
                        $stmt->close();
                    }
                    ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>