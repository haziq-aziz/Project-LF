<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

// Check if case ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid request. No case specified for deletion.";
    header('Location: ../../staff/case_view.php');
    exit();
}

$case_id = $_GET['id'];

// Start transaction
$conn->begin_transaction();

try {
    // First, delete any associated case files
    // 1. Get list of files to delete physically
    $files_query = "SELECT file_path FROM case_files WHERE case_id = ?";
    $stmt = $conn->prepare($files_query);
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    $files_result = $stmt->get_result();
    
    // 2. Delete physical files
    while ($file = $files_result->fetch_assoc()) {
        $file_path = '../../uploads/case_files/' . $file['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // 3. Delete all case files records
    $delete_files_query = "DELETE FROM case_files WHERE case_id = ?";
    $stmt = $conn->prepare($delete_files_query);
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    
    // Next, delete the case itself
    $delete_case_query = "DELETE FROM cases WHERE id = ?";
    $stmt = $conn->prepare($delete_case_query);
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    
    // Check if any rows were affected (case actually existed)
    if ($stmt->affected_rows === 0) {
        throw new Exception("Case not found or already deleted.");
    }
    
    // Commit transaction if everything is successful
    $conn->commit();
    $_SESSION['success'] = "Case and all associated files deleted successfully.";
    
} catch (Exception $e) {
    // Rollback transaction if there's any error
    $conn->rollback();
    $_SESSION['error'] = "Failed to delete case: " . $e->getMessage();
}

// Redirect back to cases page
header('Location: ../../staff/case_view.php');
exit();
?>