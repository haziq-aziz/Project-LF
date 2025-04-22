<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Check if file ID and case ID are provided
if (!isset($_GET['id']) || !isset($_GET['case_id']) || empty($_GET['id']) || empty($_GET['case_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: cases.php');
    exit();
}

$file_id = $_GET['id'];
$case_id = $_GET['case_id'];

// Check if the file exists and belongs to the case
$stmt = $conn->prepare("SELECT file_path FROM case_files WHERE id = ? AND case_id = ?");
$stmt->bind_param("ii", $file_id, $case_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "File not found.";
    header("Location: case_detail.php?id=$case_id");
    exit();
}

$file = $result->fetch_assoc();

// Delete the physical file
$file_path = '../uploads/case_files/' . $file['file_path'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// Delete the record from the database
$stmt = $conn->prepare("DELETE FROM case_files WHERE id = ? AND case_id = ?");
$stmt->bind_param("ii", $file_id, $case_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "File deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete file: " . $conn->error;
}

// Redirect back to case detail page
header("Location: ../../staff/case_detail.php?case_id=$case_id");
exit();
?>