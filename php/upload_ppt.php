<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != "teacher") {
    header("Location: login.html");
    exit();
}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["ppt_file"])) {
    $teacher_id = $_SESSION['id'];
    $class = $_SESSION['class'];
    $subject = $_SESSION['subject'];

    $file_name = $_FILES["ppt_file"]["name"];
    $file_tmp = $_FILES["ppt_file"]["tmp_name"];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    
    // Only allow PPT and PPTX files
    if ($file_ext != "ppt" && $file_ext != "pptx") {
        echo "<script>alert('Invalid file format! Only .ppt and .pptx allowed.'); window.location.href = '../teacher_ppt.php';</script>";
        exit();
    }

    $upload_dir = "../uploads/ppt/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_path = $upload_dir . time() . "_" . $file_name;
    move_uploaded_file($file_tmp, $file_path);

    // Insert file info into the database
    $sql = "INSERT INTO ppt_files (teacher_id, class, subject, file_name, file_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $teacher_id, $class, $subject, $file_name, $file_path);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('PPT uploaded successfully!'); window.location.href = '../teacher_ppt.php';</script>";
} else {
    echo "<script>alert('File upload failed!'); window.location.href = '../teacher_ppt.php';</script>";
}
?>
