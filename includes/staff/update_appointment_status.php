<?php
session_start();
require '../db_connection.php';
require '../notifications_helper.php'; // Add notification helper

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Check for post data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if the required parameters are provided
if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Get and validate parameters
$appointment_id = intval($_POST['appointment_id']);
$status = $_POST['status'];
$user_id = $_SESSION['user_id'];

if ($appointment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

// Validate status
$allowed_statuses = ['Scheduled', 'Completed', 'Cancelled', 'Rescheduled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Verify that the appointment belongs to the current user
$check_query = "SELECT id FROM appointments WHERE id = ? AND staff_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $appointment_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found or access denied']);
    exit();
}

// Update the appointment status
$update_query = "UPDATE appointments SET status = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("si", $status, $appointment_id);

if ($update_stmt->execute()) {
    // Get appointment details to send notification
    $fetch_query = "SELECT a.appointment_date, a.client_id, c.name as client_name 
                    FROM appointments a 
                    LEFT JOIN clients c ON a.client_id = c.id 
                    WHERE a.id = ?";
    $fetch_stmt = $conn->prepare($fetch_query);
    $fetch_stmt->bind_param("i", $appointment_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // If there's a client associated with this appointment, notify them
        if ($row['client_id']) {
            $formatted_date = date('F j, Y', strtotime($row['appointment_date']));
            notify_client_appointment_update($row['client_id'], $appointment_id, $status, $formatted_date);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$update_stmt->close();
$conn->close();
?>