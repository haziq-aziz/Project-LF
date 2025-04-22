<?php
session_start();
require '../db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

$staff_id = $_SESSION['user_id'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
$today = date('Y-m-d');

// Default response
$response = [
    'html' => '',
    'total' => 0
];

// Build the base query
$baseQuery = "SELECT a.*, 
             c.name AS client_name, 
             cs.case_no
             FROM appointments a
             LEFT JOIN clients c ON a.client_id = c.id
             LEFT JOIN cases cs ON a.case_id = cs.id
             WHERE a.staff_id = ?";

// Tab-specific conditions
switch ($tab) {
    case 'upcoming':
        $condition = " AND (a.appointment_date > ? OR (a.appointment_date = ? AND a.appointment_time > CURRENT_TIME()))
                       AND a.status NOT IN ('Completed', 'Cancelled')";
        $params = [$staff_id, $today, $today];
        $types = "iss";
        $orderBy = " ORDER BY a.appointment_date ASC, a.appointment_time ASC";
        break;
        
    case 'today':
        $condition = " AND a.appointment_date = ? AND a.status NOT IN ('Cancelled')";
        $params = [$staff_id, $today];
        $types = "is";
        $orderBy = " ORDER BY a.appointment_time ASC";
        break;
        
    case 'past':
        $condition = " AND (a.appointment_date < ? OR (a.appointment_date = ? AND a.appointment_time < CURRENT_TIME()) OR a.status = 'Completed')
                       AND a.status != 'Cancelled'";
        $params = [$staff_id, $today, $today];
        $types = "iss";
        $orderBy = " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        break;
        
    case 'all':
        $condition = "";
        $params = [$staff_id];
        $types = "i";
        $orderBy = " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        break;
        
    default:
        echo json_encode(['error' => 'Invalid tab']);
        exit();
}

// Search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $condition .= " AND (a.title LIKE ? OR c.name LIKE ? OR cs.case_no LIKE ? OR a.location LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "ssss";
}

// Count total rows for pagination
$countQuery = $baseQuery . $condition;
$stmt = $conn->prepare($countQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$response['total'] = $result->num_rows;

// If it's not the 'today' tab, add pagination
if ($tab !== 'today') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
    $offset = ($page - 1) * $entriesPerPage;
    
    $query = $baseQuery . $condition . $orderBy . " LIMIT ? OFFSET ?";
    $params[] = $entriesPerPage;
    $params[] = $offset;
    $types .= "ii";
} else {
    $query = $baseQuery . $condition . $orderBy;
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Build HTML output
$html = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format date and time
        $dateTime = date('d M Y', strtotime($row['appointment_date']));
        if ($tab !== 'today') {
            $dateTime .= ' at ' . date('h:i A', strtotime($row['appointment_time']));
        } else {
            $dateTime = date('h:i A', strtotime($row['appointment_time']));
        }
        
        // Status badge
        $statusBadge = '';
        switch($row['status']) {
            case 'Scheduled':
                $statusBadge = '<span class="badge bg-primary">Scheduled</span>';
                break;
            case 'Completed':
                $statusBadge = '<span class="badge bg-success">Completed</span>';
                break;
            case 'Cancelled':
                $statusBadge = '<span class="badge bg-danger">Cancelled</span>';
                break;
            case 'Rescheduled':
                $statusBadge = '<span class="badge bg-warning">Rescheduled</span>';
                break;
        }
        
        // Format client name
        $clientName = !empty($row['client_name']) ? htmlspecialchars($row['client_name']) : 
                     (!empty($row['client_id']) ? 'Client ID: ' . $row['client_id'] : 'No client');
        
        // Format case number
        $caseNo = !empty($row['case_no']) ? htmlspecialchars($row['case_no']) : 
                 (!empty($row['case_id']) ? 'Case ID: ' . $row['case_id'] : 'No case');
                 
        // Action buttons based on status
        $actionButtons = '<div class="btn-group">';
        $actionButtons .= '<button class="btn btn-sm btn-info view-appointment" data-id="' . $row['id'] . '"><i class="fa fa-eye"></i></button>';
        
        // Only show edit/complete/cancel for upcoming or today's appointments
        if ($row['status'] !== 'Completed' && $row['status'] !== 'Cancelled') {
            $actionButtons .= ' <a href="edit_appointment.php?id=' . $row['id'] . '" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>';
            $actionButtons .= ' <button class="btn btn-sm btn-success complete-appointment" data-id="' . $row['id'] . '"><i class="fa fa-check"></i></button>';
            $actionButtons .= ' <button class="btn btn-sm btn-danger cancel-appointment" data-id="' . $row['id'] . '"><i class="fa fa-times"></i></button>';
        }
        $actionButtons .= '</div>';
        
        $html .= '<tr>
            <td><strong>' . htmlspecialchars($row['title']) . '</strong></td>
            <td>' . $dateTime . '</td>
            <td>' . $clientName . '</td>
            <td>' . $caseNo . '</td>
            <td>' . htmlspecialchars($row['location']) . '</td>
            <td>' . $statusBadge . '</td>
            <td>' . $actionButtons . '</td>
        </tr>';
    }
} else {
    $html = '<tr><td colspan="7" class="text-center">No appointments found</td></tr>';
}

$response['html'] = $html;
echo json_encode($response);
?>