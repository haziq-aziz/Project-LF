<?php
/**
 * Notification Examples
 * 
 * This file contains examples of how to add notifications in your application.
 * It's for reference only and not intended to be accessed directly.
 */

// Include required files
require_once('includes/db_connection.php');
require_once('includes/notifications_helper.php');

/**
 * Example: Create notification for a client when a new case is created
 * 
 * @param int $client_id The client's ID
 * @param int $case_id The case ID
 * @param string $case_title The case title
 */
function notify_client_new_case($client_id, $case_no, $case_title) {
    $title = "New Case Created";
    $message = "A new case '{$case_title}' has been created for you.";
    $link = "/client/case_detail.php?case_no={$case_no}";
    
    add_notification($client_id, 'client', $title, $message, $link);
}

/**
 * Example: Create notification for a client when an invoice is created
 * 
 * @param int $client_id The client's ID
 * @param int $invoice_id The invoice ID
 * @param float $amount The invoice amount
 */
function notify_client_new_invoice($client_id, $invoice_id, $amount) {
    $title = "New Invoice";
    $message = "A new invoice for RM{$amount} has been created for you.";
    $link = "/client/view_invoice.php?id={$invoice_id}";
    
    add_notification($client_id, 'client', $title, $message, $link);
}

/**
 * Example: Create notification for a staff member when a client makes a payment
 * 
 * @param int $staff_id The staff member's ID
 * @param int $client_id The client's ID
 * @param string $client_name The client's name
 * @param float $amount The payment amount
 */
function notify_staff_payment_received($staff_id, $client_id, $client_name, $amount) {
    $title = "Payment Received";
    $message = "Client {$client_name} has made a payment of RM{$amount}.";
    $link = "/staff/client_view.php?id={$client_id}";
    
    add_notification($staff_id, 'staff', $title, $message, $link);
}

/**
 * Example: Create notification for a staff member when a new appointment is booked
 * 
 * @param int $staff_id The staff member's ID
 * @param int $appointment_id The appointment ID
 * @param string $client_name The client's name
 * @param string $date The appointment date
 */
function notify_staff_new_appointment($staff_id, $appointment_id, $client_name, $date) {
    $title = "New Appointment";
    $message = "Client {$client_name} has booked an appointment on {$date}.";
    $link = "/staff/edit_appointment.php?id={$appointment_id}";
    
    add_notification($staff_id, 'staff', $title, $message, $link);
}

/**
 * Example: Create notification for a client when their appointment status changes
 * 
 * @param int $client_id The client's ID
 * @param int $appointment_id The appointment ID
 * @param string $status The new appointment status
 * @param string $date The appointment date
 */
function notify_client_appointment_update($client_id, $appointment_id, $status, $date) {
    $title = "Appointment Update";
    $message = "Your appointment on {$date} has been {$status}.";
    $link = null; // No direct link, just a notification
    
    add_notification($client_id, 'client', $title, $message, $link);
}

/**
 * Example: Create notification for a client when a file is uploaded to their case
 * 
 * @param int $client_id The client's ID
 * @param int $case_id The case ID
 * @param string $case_title The case title
 * @param string $file_name The name of the uploaded file
 */
function notify_client_file_upload($client_id, $case_no, $case_title, $file_name) {
    $title = "New Document Uploaded";
    $message = "A new document '{$file_name}' has been uploaded to your case '{$case_title}'.";
    $link = "/client/case_detail.php?case_no={$case_no}";
    
    add_notification($client_id, 'client', $title, $message, $link);
}
?>
