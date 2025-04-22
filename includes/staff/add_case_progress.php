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

$case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : 0;
$progress_date = sanitize_input($_POST['progress_date'] ?? '');
$title = sanitize_input($_POST['title'] ?? '');
$description = sanitize_input($_POST['description'] ?? '');
$created_by = $_SESSION['user_id'];

// Validate required fields
if (empty($case_id) || empty($progress_date) || empty($title) || empty($description)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../../staff/case_detail.php?case_id=$case_id");
    exit();
}

// Validate case exists
$case_check = "SELECT id FROM cases WHERE id = ?";
$stmt = $conn->prepare($case_check);
$stmt->bind_param("i", $case_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid case.";
    header('Location: ../../staff/case_view.php');
    exit();
}

// Insert progress entry
$query = "INSERT INTO case_progress (case_id, progress_date, title, description, created_by) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("isssi", $case_id, $progress_date, $title, $description, $created_by);

if ($stmt->execute()) {
    $_SESSION['success'] = "Case progress added successfully.";
} else {
    $_SESSION['error'] = "Failed to add case progress: " . $conn->error;
}

header("Location: ../../staff/case_detail.php?case_id=$case_id");
exit();