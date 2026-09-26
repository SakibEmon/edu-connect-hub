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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video_type = $_POST['video_type'];
    $video_url = "";

    if ($video_type === 'youtube' && !empty($_POST['youtube_link'])) {
        $youtube_link = trim($_POST['youtube_link']);
        // Convert YouTube URL to embed format
        if (strpos($youtube_link, "watch?v=") !== false) {
            $video_url = str_replace("watch?v=", "embed/", $youtube_link);
        } elseif (strpos($youtube_link, "youtu.be/") !== false) {
            $video_id = substr(parse_url($youtube_link, PHP_URL_PATH), 1);
            $video_url = "https://www.youtube.com/embed/" . $video_id;
        } else {
            $video_url = $youtube_link; // fallback
        }
    } elseif ($video_type === 'file' && isset($_FILES['video_file'])) {
        $uploadDir = "uploads/videos/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['video_file']['name']);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetFile)) {
            $video_url = $targetFile;
        }
    }

    if (!empty($video_url)) {
        $insert = "INSERT INTO lecture_videos (teacher_id, class, subject, video_type, video_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("issss", $id, $class, $subject, $video_type, $video_url);
        $stmt->execute();
        $stmt->close();

        // ✅ Prevent duplicate submission (redirect)
        header("Location: teacher_videos.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Lecture Videos</title>
    <link rel="stylesheet" href="css/teacher_dashboard.css">
</head>
<body>
<div class="main">
    <div class="card">
        <h2>Upload Lecture Video</h2>
        <!-- Back Button -->
        <a href="teacher_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

        <?php if (isset($_GET['success'])): ?>
            <p style="color:green; font-weight:bold;">✅ Video uploaded successfully!</p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>
                <input type="radio" name="video_type" value="youtube" checked> YouTube Link
            </label>
            <input type="text" name="youtube_link" placeholder="Enter YouTube Video Link" style="width:100%; padding:8px; margin:10px 0;">

            <label>
                <input type="radio" name="video_type" value="file"> Upload File
            </label>
            <input type="file" name="video_file" accept="video/*" style="margin:10px 0;">

            <button type="submit" class="upload-btn">Upload</button>
        </form>
    </div>

    <div class="card">
        <h2>My Lecture Videos</h2>
        <?php
        $result = $conn->query("SELECT * FROM lecture_videos WHERE teacher_id=$id ORDER BY uploaded_at DESC");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['video_type'] === 'youtube') {
                    echo "<div class='video-box'>
                            <iframe width='100%' height='315' src='{$row['video_url']}' frameborder='0' allowfullscreen></iframe>
                          </div>";
                } else {
                    echo "<div class='video-box'>
                            <video width='100%' controls>
                                <source src='{$row['video_url']}' type='video/mp4'>
                                Your browser does not support the video tag.
                            </video>
                          </div>";
                }
            }
        } else {
            echo "<p>No videos uploaded yet.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
