<?php
session_start();
require '../db_connection.php';

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

// Get and validate parameters
$appointment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($appointment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

if (!in_array($status, ['Completed', 'Cancelled', 'Scheduled', 'Rescheduled'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Check if the user has permission to update this appointment
$check_query = "SELECT id FROM appointments WHERE id = ? AND (staff_id = ? OR created_by = ?)";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("iii", $appointment_id, $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found or you do not have permission to update it']);
    exit();
}

// Update the appointment status
$update_query = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("si", $status, $appointment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update appointment status: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>