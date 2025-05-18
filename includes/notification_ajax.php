<?php
/**
 * Notification Ajax Handler
 * 
 * This file handles AJAX requests for notification management.
 */

session_start();
require_once('db_connection.php');
require_once('notifications_helper.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] === 'admin' ? 'staff' : $_SESSION['role'];

// Get action from request
$action = isset($_POST['action']) ? $_POST['action'] : '';

$response = ['success' => false];

switch ($action) {    case 'mark_read':
        // Mark a specific notification as read
        $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
        if ($notification_id > 0) {
            // Log the notification ID and user details for debugging
            error_log("Marking notification as read: ID={$notification_id}, user_id={$user_id}, user_type={$user_type}");
            
            // Check if notification exists
            $check_query = "SELECT id, is_read FROM notifications WHERE id = ? AND recipient_id = ? AND recipient_type = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param('iis', $notification_id, $user_id, $user_type);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $notification_data = $check_result->fetch_assoc();
                error_log("Notification found, current is_read status: " . $notification_data['is_read']);
                $check_stmt->close();
                
                // Perform update
                $success = mark_notification_read($notification_id, $user_id, $user_type);
                error_log("Mark read result: " . ($success ? "Success" : "Failed"));
                
                // Verify the update
                $verify_query = "SELECT is_read FROM notifications WHERE id = ?";
                $verify_stmt = $conn->prepare($verify_query);
                $verify_stmt->bind_param('i', $notification_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result()->fetch_assoc();
                error_log("Post-update is_read status: " . $verify_result['is_read']);
                $verify_stmt->close();
                
                $response = [
                    'success' => $success,
                    'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
                ];
            } else {
                error_log("Notification not found or doesn't belong to this user");
                $response = [
                    'success' => false,
                    'message' => 'Notification not found'
                ];
                $check_stmt->close();
            }
        } else {
            error_log("Invalid notification ID: {$notification_id}");
            $response = [
                'success' => false,
                'message' => 'Invalid notification ID'
            ];
        }
        break;
        
    case 'mark_all_read':
        // Mark all notifications as read
        $success = mark_all_notifications_read($user_id, $user_type);
        $response = [
            'success' => $success,
            'message' => $success ? 'All notifications marked as read' : 'Failed to mark notifications as read'
        ];
        break;
        
    case 'delete':
        // Delete a notification
        $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
        if ($notification_id > 0) {
            $success = delete_notification($notification_id, $user_id, $user_type);
            $response = [
                'success' => $success,
                'message' => $success ? 'Notification deleted' : 'Failed to delete notification'
            ];
        }
        break;
        
    case 'get_unread_count':
        // Get unread notification count
        $count = get_unread_notification_count($user_id, $user_type);
        $response = [
            'success' => true,
            'count' => $count
        ];
        break;
        
    default:
        $response = [
            'success' => false,
            'message' => 'Invalid action'
        ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
// Don't include closing PHP tag to prevent accidental whitespace
