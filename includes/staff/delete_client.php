<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Check if client ID is provided
if (!isset($_POST['client_id']) || empty($_POST['client_id'])) {
    $_SESSION['error'] = "No client specified for deletion.";
    header('Location: ../../staff/client_view.php');
    exit();
}

$client_id = intval($_POST['client_id']);

// Start transaction to ensure data consistency
mysqli_begin_transaction($conn);

try {
    // Check if client exists
    $check_query = "SELECT name FROM clients WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $client_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) === 0) {
        throw new Exception("Client not found.");
    }
    
    // Bind the result variable
    mysqli_stmt_bind_result($check_stmt, $client_name);
    mysqli_stmt_fetch($check_stmt);
    mysqli_stmt_close($check_stmt);
    
    // Check if client has associated cases
    $case_check_query = "SELECT COUNT(*) FROM cases WHERE client_id = ?";
    $case_check_stmt = mysqli_prepare($conn, $case_check_query);
    mysqli_stmt_bind_param($case_check_stmt, "i", $client_id);
    mysqli_stmt_execute($case_check_stmt);
    mysqli_stmt_bind_result($case_check_stmt, $case_count);
    mysqli_stmt_fetch($case_check_stmt);
    mysqli_stmt_close($case_check_stmt);
    
    if ($case_count > 0) {
        // Option 1: Prevent deletion if cases exist
        if (!isset($_POST['force_delete']) || $_POST['force_delete'] !== 'yes') {
            throw new Exception("Cannot delete client: $client_name has $case_count associated case(s). 
                               You must first transfer or delete these cases.");
        }
        
        // Option 2: If force_delete is set, nullify the client_id in cases
        $update_cases_query = "UPDATE cases SET client_id = NULL WHERE client_id = ?";
        $update_cases_stmt = mysqli_prepare($conn, $update_cases_query);
        mysqli_stmt_bind_param($update_cases_stmt, "i", $client_id);
        
        if (!mysqli_stmt_execute($update_cases_stmt)) {
            throw new Exception("Error updating cases: " . mysqli_error($conn));
        }
        mysqli_stmt_close($update_cases_stmt);
    }
    
    // Delete the client
    $delete_query = "DELETE FROM clients WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $client_id);
    
    if (!mysqli_stmt_execute($delete_stmt)) {
        throw new Exception("Error deleting client: " . mysqli_error($conn));
    }
    mysqli_stmt_close($delete_stmt);
    
    // If everything is successful, commit the transaction
    mysqli_commit($conn);
    
    $_SESSION['success'] = "Client '$client_name' has been successfully deleted.";
    
    // Return JSON response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => "Client '$client_name' has been successfully deleted."]);
        exit();
    }
    
    header('Location: ../../staff/client_view.php');
    exit();
    
} catch (Exception $e) {
    // Roll back the transaction on error
    mysqli_rollback($conn);
    
    $_SESSION['error'] = $e->getMessage();
    
    // Return JSON response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
    
    header('Location: ../../staff/client_view.php');
    exit();
}
?>