<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../staff/case_view.php');
    exit();
}

// Validate & sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$progress_id = isset($_POST['progress_id']) ? intval($_POST['progress_id']) : 0;
$case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : 0;
$progress_date = sanitize_input($_POST['progress_date'] ?? '');
$title = sanitize_input($_POST['title'] ?? '');
$description = sanitize_input($_POST['description'] ?? '');

// Validate required fields
if (empty($progress_id) || empty($case_id) || empty($progress_date) || empty($title) || empty($description)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../../staff/case_detail.php?id=$case_id");
    exit();
}

// Validate progress entry exists and belongs to the case
$check_query = "SELECT id FROM case_progress WHERE id = ? AND case_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $progress_id, $case_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid progress entry or case.";
    header("Location: ../../staff/case_detail.php?id=$case_id");
    exit();
}

// Update progress entry
$query = "UPDATE case_progress SET progress_date = ?, title = ?, description = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssi", $progress_date, $title, $description, $progress_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Progress entry updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update progress entry: " . $conn->error;
}

header("Location: ../../staff/case_detail.php?id=$case_id");
exit();