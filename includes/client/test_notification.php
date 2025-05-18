<?php
/**
 * Test script for notifications
 * This file is used to test if notifications are working properly
 */

session_start();
require_once('../db_connection.php');
require_once('../notifications_helper.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    echo "Authentication required";
    exit();
}

// Get a staff member to notify
$staff_query = "SELECT id FROM users WHERE role = 'admin' OR role = 'staff' LIMIT 1";
$staff_result = $conn->query($staff_query);

if ($staff_result && $staff_row = $staff_result->fetch_assoc()) {
    $staff_id = $staff_row['id'];
    
    // Get client name
    $client_id = $_SESSION['user_id'];
    $client_query = "SELECT name FROM clients WHERE id = ?";
    $client_stmt = $conn->prepare($client_query);
    $client_stmt->bind_param('i', $client_id);
    $client_stmt->execute();
    $client_result = $client_stmt->get_result();
    $client_data = $client_result->fetch_assoc();
    $client_name = $client_data ? $client_data['name'] : 'Unknown Client';
    
    echo "<h2>Testing Notification System</h2>";
    echo "<p>Sending test notification to staff ID: $staff_id</p>";
    
    // Try to send notification
    $notification_result = notify_staff_payment_received(
        $staff_id,
        $client_id,
        $client_name,
        99.99
    );
    
    if ($notification_result) {
        echo "<p style='color:green'>Notification sent successfully!</p>";
    } else {
        echo "<p style='color:red'>Notification failed to send.</p>";
        
        if (isset($_SESSION['notification_error'])) {
            echo "<p>Error: " . $_SESSION['notification_error'] . "</p>";
            unset($_SESSION['notification_error']);
        }
    }
    
    echo "<p><a href='../../client/dashboard.php'>Return to Dashboard</a></p>";
} else {
    echo "<p>No staff members found to notify</p>";
}
?>
