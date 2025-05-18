<?php
session_start();
require_once('../db_connection.php');
require_once('../notifications_helper.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate data
    $required = ['invoice_id', 'amount', 'payment_method'];
    $errors = [];
    
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (empty($errors)) {
        // Sanitize input
        $invoice_id = intval($_POST['invoice_id']);
        $amount = floatval($_POST['amount']);
        $payment_method = htmlspecialchars($_POST['payment_method']);
        $payment_reference = isset($_POST['payment_reference']) ? htmlspecialchars($_POST['payment_reference']) : '';
        $payment_date = isset($_POST['payment_date']) ? htmlspecialchars($_POST['payment_date']) : date('Y-m-d');
        $notes = isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '';
        
        // Get invoice data to verify it belongs to this client
        $invoice_query = "SELECT i.*, s.id as staff_id, s.name as staff_name 
                          FROM invoices i
                          LEFT JOIN users s ON i.created_by = s.id
                          WHERE i.id = ? AND i.client_id = ?";
        $stmt = $conn->prepare($invoice_query);
        $stmt->bind_param('ii', $invoice_id, $user_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        
        if (!$invoice) {
            $_SESSION['error'] = "Invoice not found or access denied";
            header('Location: ../../client/invoice.php');
            exit();
        }
        
        // Process payment
        try {
            $conn->begin_transaction();
            
            // Insert payment record
            $payment_query = "INSERT INTO payments (invoice_id, client_id, amount, payment_method, payment_reference, 
                             payment_date, notes, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
            $payment_stmt = $conn->prepare($payment_query);
            $status = 'Pending'; // Default status
            $payment_stmt->bind_param('iidsss', $invoice_id, $user_id, $amount, $payment_method, $payment_reference, $payment_date);
            
            if (!$payment_stmt->execute()) {
                throw new Exception("Payment record creation failed: " . $conn->error);
            }
            
            $payment_id = $payment_stmt->insert_id;
            
            // Update invoice payment status if needed
            $remaining = $invoice['total_amount'] - $amount;
            $invoice_status = ($remaining <= 0) ? 'Paid' : 'Partially Paid';
            
            $update_query = "UPDATE invoices SET status = ?, paid_amount = paid_amount + ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('sdi', $invoice_status, $amount, $invoice_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Invoice update failed: " . $conn->error);
            }
            
            $conn->commit();
              // Send notification to staff about payment
            if (isset($invoice['staff_id'])) {
                // Get client name
                $client_query = "SELECT name FROM clients WHERE id = ?";
                $client_stmt = $conn->prepare($client_query);
                $client_stmt->bind_param('i', $user_id);
                $client_stmt->execute();
                $client_result = $client_stmt->get_result();
                $client_data = $client_result->fetch_assoc();
                $client_name = $client_data ? $client_data['name'] : 'Unknown Client';
                
                // Close client statement
                $client_stmt->close();
                
                // Debug info for notification
                error_log("Sending payment notification to staff ID: {$invoice['staff_id']}, from client ID: {$user_id}, name: {$client_name}, amount: {$amount}");
                
                // Send notification
                $notification_result = notify_staff_payment_received(
                    $invoice['staff_id'],
                    $user_id,
                    $client_name,
                    $amount
                );
                
                // Log notification result
                if (!$notification_result) {
                    error_log("Payment notification failed to send");
                    if (isset($_SESSION['notification_error'])) {
                        error_log("Notification error: " . $_SESSION['notification_error']);
                        unset($_SESSION['notification_error']);
                    }
                } else {
                    error_log("Payment notification sent successfully");
                }
            }
            
            $_SESSION['success'] = "Payment processed successfully. It will be reviewed and confirmed.";
            header('Location: ../../client/payment_history.php');
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Payment processing failed: " . $e->getMessage();
            header('Location: ../../client/payment.php?invoice_id=' . $invoice_id);
            exit();
        }
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: ../../client/payment.php?invoice_id=' . $invoice_id);
        exit();
    }
} else {
    header('Location: ../../client/invoice.php');
    exit();
}
?>
