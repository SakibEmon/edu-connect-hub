<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}
$class = $_GET['class'] ?? null;
if (!$class) {
    echo "Class not selected.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Quiz - Class <?php echo htmlspecialchars($class); ?></title>
    <link rel="stylesheet" href="css/quiz.css">
</head>
<body>
<div class="container">
    <h2>Create Quiz for Class <?php echo htmlspecialchars($class); ?></h2>

    <!-- Optional: Show success or error after redirect -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') : ?>
        <p class="success-msg">✅ Quiz saved successfully!</p>
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
        <p class="error-msg">❌
            <?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Something went wrong! Try again.'; ?></p>
    <?php endif; ?>

    <form action="save_quiz.php" method="POST">
        <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">

        <label for="subject">Select Subject:</label>
        <select name="subject" id="subject" required>
            <option value="">-- Select Subject --</option>
            <option value="Bangla">Bangla</option>
            <option value="English">English</option>
            <option value="Math">Math</option>
            <option value="Physics">Physics</option>
            <option value="Chemistry">Chemistry</option>
            <option value="Biology">Biology</option>
        </select>

        <label for="chapter">Chapter:</label>
        <input type="text" id="chapter" name="chapter" placeholder="Enter Chapter Name" required>

        <label for="topic">Topic Name:</label>
        <input type="text" id="topic" name="topic" placeholder="Enter Topic Name" required>

        <hr>

        <?php for ($i = 1; $i <= 10; $i++) : ?>
        <div class="question-block">
            <h4>Question <?php echo $i; ?></h4>
            <textarea name="question_<?php echo $i; ?>" required placeholder="Enter question text"></textarea>

            <div class="options">
                <input type="text" name="q<?php echo $i; ?>_a" required placeholder="Option A">
                <input type="text" name="q<?php echo $i; ?>_b" required placeholder="Option B">
                <input type="text" name="q<?php echo $i; ?>_c" required placeholder="Option C">
                <input type="text" name="q<?php echo $i; ?>_d" required placeholder="Option D">
            </div>

            <label>Correct Option:</label>
            <select name="q<?php echo $i; ?>_correct" required>
                <option value="">Select</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>
        <hr>
        <?php endfor; ?>

        <button type="submit" class="save-btn">Save Quiz</button>
    </form>
</div>
</body>
</html>
