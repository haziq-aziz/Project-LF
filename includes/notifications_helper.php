<?php
/**
 * Notifications Helper Functions
 * 
 * This file contains functions for creating, retrieving, and managing notifications
 * for both staff and client users in the law firm management system.
 */

/**
 * Add a new notification for a user
 *
 * @param int $recipient_id The ID of the user receiving the notification
 * @param string $recipient_type The type of recipient (staff or client)
 * @param string $title The notification title
 * @param string $message The notification message
 * @param string $link Optional link to redirect the user when clicking the notification
 * @return bool True on success, false on failure
 */
function add_notification($recipient_id, $recipient_type, $title, $message, $link = null) {
    global $conn;
    
    if (!in_array($recipient_type, ['staff', 'client'])) {
        return false;
    }
    
    $sql = "INSERT INTO notifications (recipient_id, recipient_type, title, message, link) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        // Store the error message for debugging
        $_SESSION['notification_error'] = "SQL Prepare Error: " . $conn->error;
        return false;
    }
    
    $stmt->bind_param('issss', $recipient_id, $recipient_type, $title, $message, $link);
    $result = $stmt->execute();
    
    if (!$result) {
        // Store the error message for debugging
        $_SESSION['notification_error'] = "SQL Execute Error: " . $stmt->error;
    }
    
    $stmt->close();
    
    return $result;
}

/**
 * Get unread notification count for a user
 *
 * @param int $user_id The ID of the user
 * @param string $user_type The type of user (staff or client)
 * @return int The number of unread notifications
 */
function get_unread_notification_count($user_id, $user_type) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM notifications 
            WHERE recipient_id = ? AND recipient_type = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param('is', $user_id, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    return (int)$count;
}

/**
 * Get notifications for a user
 *
 * @param int $user_id The ID of the user
 * @param string $user_type The type of user (staff or client)
 * @param int $limit Optional limit of notifications to retrieve (default 10)
 * @param bool $unread_only Whether to get only unread notifications (default false)
 * @return array Array of notification objects
 */
function get_notifications($user_id, $user_type, $limit = 10, $unread_only = false) {
    global $conn;
    
    $sql = "SELECT * FROM notifications 
            WHERE recipient_id = ? AND recipient_type = ?";
    
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param('isi', $user_id, $user_type, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    return $notifications;
}

/**
 * Mark a notification as read
 *
 * @param int $notification_id The ID of the notification to mark as read
 * @param int $user_id The ID of the user (for security)
 * @param string $user_type The type of user (staff or client)
 * @return bool True on success, false on failure
 */
function mark_notification_read($notification_id, $user_id, $user_type) {
    global $conn;
    
    // Force parameters to correct types
    $notification_id = (int)$notification_id;
    $user_id = (int)$user_id;
    
    // Debug log
    error_log("mark_notification_read function called: notification_id={$notification_id}, user_id={$user_id}, user_type={$user_type}");
    
    // Validate parameters
    if ($notification_id <= 0 || $user_id <= 0 || !in_array($user_type, ['staff', 'client'])) {
        error_log("Invalid parameters for mark_notification_read: notification_id={$notification_id}, user_id={$user_id}, user_type={$user_type}");
        return false;
    }
    
    // Update with explicit timestamp
    $sql = "UPDATE notifications SET is_read = 1
            WHERE id = ? AND recipient_id = ? AND recipient_type = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("SQL Prepare Error in mark_notification_read: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('iis', $notification_id, $user_id, $user_type);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("SQL Execute Error in mark_notification_read: " . $stmt->error);
    } else {
        // Check if any rows were affected
        if ($stmt->affected_rows == 0) {
            error_log("No rows affected when marking notification as read. Notification may not exist or already read.");
            // Still return true since no error occurred
        } else {
            error_log("Successfully marked notification {$notification_id} as read. Rows affected: {$stmt->affected_rows}");
        }
    }
    
    $stmt->close();
    return $result;
}

/**
 * Mark all notifications as read for a user
 *
 * @param int $user_id The ID of the user
 * @param string $user_type The type of user (staff or client)
 * @return bool True on success, false on failure
 */
function mark_all_notifications_read($user_id, $user_type) {
    global $conn;
    
    // Force parameters to correct types
    $user_id = (int)$user_id;
    
    // Debug log
    error_log("mark_all_notifications_read function called: user_id={$user_id}, user_type={$user_type}");
    
    // Validate parameters
    if ($user_id <= 0 || !in_array($user_type, ['staff', 'client'])) {
        error_log("Invalid parameters for mark_all_notifications_read: user_id={$user_id}, user_type={$user_type}");
        return false;
    }
    
    // Update with explicit timestamp
    $sql = "UPDATE notifications SET is_read = 1 
            WHERE recipient_id = ? AND recipient_type = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("SQL Prepare Error in mark_all_notifications_read: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('is', $user_id, $user_type);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("SQL Execute Error in mark_all_notifications_read: " . $stmt->error);
    } else {
        // Check if any rows were affected
        $affected = $stmt->affected_rows;
        error_log("Marked {$affected} notifications as read for user {$user_id}, type {$user_type}");
    }
    
    $stmt->close();
    return $result;
}

/**
 * Delete a notification
 *
 * @param int $notification_id The ID of the notification to delete
 * @param int $user_id The ID of the user (for security)
 * @param string $user_type The type of user (staff or client)
 * @return bool True on success, false on failure
 */
function delete_notification($notification_id, $user_id, $user_type) {
    global $conn;
    
    $sql = "DELETE FROM notifications 
            WHERE id = ? AND recipient_id = ? AND recipient_type = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param('iis', $notification_id, $user_id, $user_type);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Format notification date to readable format
 *
 * @param string $datetime MySQL datetime string
 * @return string Formatted date string
 */
function format_notification_date($datetime) {
    // Get timestamp for MySQL datetime and current time
    $timestamp = strtotime($datetime);
    $now = time();
    
    // Direct calculation of time difference in seconds
    $diff_seconds = $now - $timestamp;
    
    // Handle future dates (server time issues)
    if ($diff_seconds < 0) {
        return 'Just now';
    }
    
    // Calculate time differences directly from seconds
    $diff_minutes = floor($diff_seconds / 60);
    $diff_hours = floor($diff_seconds / 3600);
    $diff_days = floor($diff_seconds / 86400);
    $diff_months = floor($diff_days / 30);
    $diff_years = floor($diff_days / 365);
    
    // Format the output based on time difference
      if ($diff_years > 0) {
        return $diff_years . ' year' . ($diff_years > 1 ? 's' : '') . ' ago';
    }
    if ($diff_months > 0) {
        return $diff_months . ' month' . ($diff_months > 1 ? 's' : '') . ' ago';
    }
    if ($diff_days > 0) {
        if ($diff_days == 1) {
            return 'Yesterday';
        }
        return $diff_days . ' days ago';
    }
    if ($diff_hours > 0) {
        return $diff_hours . ' hour' . ($diff_hours > 1 ? 's' : '') . ' ago';
    }
    if ($diff_minutes > 0) {
        return $diff_minutes . ' minute' . ($diff_minutes > 1 ? 's' : '') . ' ago';
    }
    
    return 'Just now';
}

/**
 * Create notification for a client when a new case is created
 * 
 * @param int $client_id The client's ID
 * @param int $case_no The case number
 * @param string $case_title The case title
 * @return bool Success status
 */
function notify_client_new_case($client_id, $case_no, $case_title) {
    $title = "New Case Created";
    $message = "A new case '{$case_title}' has been created for you.";
    $link = "/client/case_detail.php?case_no={$case_no}";
    
    return add_notification($client_id, 'client', $title, $message, $link);
}

/**
 * Create notification for a client when an invoice is created
 * 
 * @param int $client_id The client's ID
 * @param int $invoice_id The invoice ID
 * @param float $amount The invoice amount
 * @return bool Success status
 */
function notify_client_new_invoice($client_id, $invoice_id, $amount) {
    $title = "New Invoice";
    $message = "A new invoice for RM{$amount} has been created for you.";
    $link = "/client/view_invoice.php?invoice={$invoice_id}";
    
    return add_notification($client_id, 'client', $title, $message, $link);
}

/**
 * Create notification for a staff member when a client makes a payment
 * 
 * @param int $staff_id The staff member's ID
 * @param int $client_id The client's ID
 * @param string $client_name The client's name
 * @param float $amount The payment amount
 * @return bool Success status
 */
function notify_staff_payment_received($staff_id, $client_id, $client_name, $amount) {
    $title = "Payment Received";
    $message = "Client {$client_name} has made a payment of RM{$amount}.";
    $link = "/staff/client_view.php?id={$client_id}";
    
    // Validate staff ID
    if (!$staff_id || !is_numeric($staff_id) || $staff_id <= 0) {
        error_log("Invalid staff ID for payment notification: " . var_export($staff_id, true));
        return false;
    }
    
    $result = add_notification($staff_id, 'staff', $title, $message, $link);
    
    // Log the result
    error_log("Payment notification to staff {$staff_id}: " . ($result ? "Success" : "Failed"));
    
    return $result;
}

/**
 * Create notification for a staff member when a new appointment is booked
 * 
 * @param int $staff_id The staff member's ID
 * @param int $appointment_id The appointment ID
 * @param string $client_name The client's name
 * @param string $date The appointment date
 * @return bool Success status
 */
function notify_staff_new_appointment($staff_id, $appointment_id, $client_name, $date) {
    $title = "New Appointment";
    $message = "Client {$client_name} has booked an appointment on {$date}.";
    $link = "/staff/edit_appointment.php?id={$appointment_id}";
    
    return add_notification($staff_id, 'staff', $title, $message, $link);
}

/**
 * Get all notifications for a user without limit
 *
 * @param int $user_id The ID of the user
 * @param string $user_type The type of user (staff or client)
 * @return array Array of notifications
 */
function get_all_notifications($user_id, $user_type) {
    global $conn;
    
    $sql = "SELECT * FROM notifications 
            WHERE recipient_id = ? AND recipient_type = ? 
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param('is', $user_id, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    return $notifications;
}

/**
 * Create notification for a client when their appointment status changes
 * 
 * @param int $client_id The client's ID
 * @param int $appointment_id The appointment ID
 * @param string $status The new appointment status
 * @param string $date The appointment date
 * @return bool Success status
 */
function notify_client_appointment_update($client_id, $appointment_id, $status, $date) {
    $title = "Appointment Update";
    $message = "Your appointment on {$date} has been {$status}.";
    $link = null; // No direct link, just a notification
    
    return add_notification($client_id, 'client', $title, $message, $link);
}

/**
 * Create notification for a client when a file is uploaded to their case
 * 
 * @param int $client_id The client's ID
 * @param int $case_no The case number
 * @param string $case_title The case title
 * @param string $file_name The name of the uploaded file
 * @return bool Success status
 */
function notify_client_file_upload($client_id, $case_no, $case_title, $file_name) {
    $title = "New Document Uploaded";
    $message = "A new document '{$file_name}' has been uploaded to your case '{$case_title}'.";
    $link = "/client/case_detail.php?case_no={$case_no}";
    
    return add_notification($client_id, 'client', $title, $message, $link);
}
?>
