<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php';

$teacher_id = $_SESSION['id'];
$subject = $_SESSION['subject'];

// Handle PPT Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["ppt_file"])) {
    $file_name = $_FILES["ppt_file"]["name"];
    $file_tmp = $_FILES["ppt_file"]["tmp_name"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ["ppt", "pptx", "pdf"];

    if (in_array($file_ext, $allowed_ext)) {
        $upload_dir = "Uploads/ppt/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_path = $upload_dir . time() . "_" . $file_name;
        if (move_uploaded_file($file_tmp, $file_path)) {
            $sql = "INSERT INTO teacher_ppt (teacher_id, subject, file_name, file_path) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                echo "<script>alert('Database error: Unable to prepare statement.');</script>";
            } else {
                $stmt->bind_param("isss", $teacher_id, $subject, $file_name, $file_path);
                $stmt->execute();
                $stmt->close();
                echo "<script>alert('PPT uploaded successfully!'); window.location.href='teacher_ppt.php';</script>";
            }
        } else {
            echo "<script>alert('Failed to upload file.');</script>";
        }
    } else {
        echo "<script>alert('Invalid file format. Only PPT, PPTX, and PDF are allowed.');</script>";
    }
}

// Fetch Uploaded PPTs
$sql_ppt = "SELECT id, file_name, file_path FROM teacher_ppt WHERE teacher_id=? AND subject=?";
$stmt_ppt = $conn->prepare($sql_ppt);
if ($stmt_ppt === false) {
    echo "<script>alert('Database error: Unable to prepare statement.');</script>";
} else {
    $stmt_ppt->bind_param("is", $teacher_id, $subject);
    $stmt_ppt->execute();
    $result_ppt = $stmt_ppt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage PPT</title>
    <link rel="stylesheet" href="css/teacher_ppt.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1><span class="icon icon-title"></span>Manage PPT/PDF for <?php echo htmlspecialchars($_SESSION['subject']); ?></h1>

        <div class="card">
            <h2><span class="icon icon-upload"></span>Upload PPT/PDF</h2>
            <form method="post" enctype="multipart/form-data" class="upload-form">
                <label for="ppt_file">Select File:</label>
                <input type="file" name="ppt_file" id="ppt_file" accept=".ppt,.pptx,.pdf" required>
                <button type="submit" class="action-btn upload-btn">Upload</button>
            </form>
        </div>

        <div class="card">
            <h2><span class="icon icon-ppt"></span>Uploaded PPT/PDF </h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Download</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_ppt->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['file_name']); ?></td>
                                <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" download><button class="action-btn download-btn">Download</button></a></td>
                                <td><a href="view_ppt.php?file=<?php echo urlencode($row['file_path']); ?>" target="_blank"><button class="action-btn view-btn">View</button></a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="teacher_dashboard.php" class="back-link"><span class="icon icon-back"></span>Back to Dashboard</a>
    </div>

    <?php
    if (isset($stmt_ppt)) {
        $stmt_ppt->close();
    }
    $conn->close();
    ?>
</body>
</html>