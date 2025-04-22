<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Get parameters - accept BOTH id/progress_id and case_id formats
$progress_id = isset($_GET['progress_id']) ? intval($_GET['progress_id']) : 
              (isset($_GET['id']) ? intval($_GET['id']) : 0);
$case_id = isset($_GET['case_id']) ? intval($_GET['case_id']) : 0;

if (empty($progress_id) || empty($case_id)) {
    $_SESSION['error'] = "Missing required parameters.";
    
    if (!empty($case_id)) {
        header("Location: ../../staff/case_detail.php?case_id=$case_id");
    } else {
        header("Location: ../../staff/case_view.php");
    }
    exit();
}

// Verify the progress entry exists and belongs to the case
$check_query = "SELECT id FROM case_progress WHERE id = ? AND case_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $progress_id, $case_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid progress entry or case.";
    header("Location: ../../staff/case_detail.php?case_id=$case_id");
    exit();
}

// Delete the progress entry
$delete_query = "DELETE FROM case_progress WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("i", $progress_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Progress entry deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete progress entry: " . $conn->error;
}

header("Location: ../../staff/case_detail.php?case_id=$case_id");
exit();