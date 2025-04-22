<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get pagination parameters
$entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $entriesPerPage;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query - Now joining with clients table to get client names
$query = "SELECT c.id, c.case_no, c.case_type, c.court_detail, c.first_hearing_date, 
         c.case_stage, c.respondent_name, c.petitioner_name, c.client_id, c.client_role,
         cl.name as client_name
         FROM cases c 
         LEFT JOIN clients cl ON c.client_id = cl.id
         WHERE c.lawyer_id = ?";

// Add search conditions if search term is provided
$params = [$user_id]; // Start with user_id as first param
$types = "i";        // First parameter type is integer

if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (c.case_no LIKE ? OR c.court_detail LIKE ? OR 
                    c.respondent_name LIKE ? OR c.petitioner_name LIKE ? OR
                    cl.name LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sssss"; // Add 5 string parameter types
}

// Add pagination
$query .= " ORDER BY c.first_hearing_date ASC, c.id DESC LIMIT ? OFFSET ?";
$params[] = $entriesPerPage;
$params[] = $offset;
$types .= "ii"; // Add 2 integer parameter types

$stmt = $conn->prepare($query);

// Build the parameter binding dynamically
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$output = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determine parties based on client role
        $petitioner = '';
        $respondent = '';
        
        if ($row['client_id']) {
            if ($row['client_role'] === 'Petitioner') {
                $petitioner = htmlspecialchars($row['client_name']);
                $respondent = htmlspecialchars($row['respondent_name']);
            } else {
                $petitioner = htmlspecialchars($row['petitioner_name']);
                $respondent = htmlspecialchars($row['client_name']);
            }
        } else {
            // If no client is linked, use the stored names directly
            $petitioner = htmlspecialchars($row['petitioner_name']);
            $respondent = htmlspecialchars($row['respondent_name']);
        }
        
        // Format parties display
        $parties = $petitioner;
        if ($petitioner && $respondent) {
            $parties .= " vs " . $respondent;
        } elseif ($respondent) {
            $parties = $respondent;
        }
        
        // Format the next date (first_hearing_date)
        $nextDate = !empty($row['first_hearing_date']) ? date('d-m-Y', strtotime($row['first_hearing_date'])) : 'Not set';
        
        // Format the status based on case_stage
        $status = '';
        if ($row['case_stage'] === 'Case Open') {
            $status = '<span class="badge bg-info">Open</span>';
        } else if ($row['case_stage'] === 'Case Ongoing') {
            $status = '<span class="badge bg-warning">Ongoing</span>';
        } else if ($row['case_stage'] === 'Case Close') {
            $status = '<span class="badge bg-success">Closed</span>';
        } else {
            $status = '<span class="badge bg-secondary">' . htmlspecialchars($row['case_stage']) . '</span>';
        }
        
        $output .= "<tr>
            <td>" . htmlspecialchars($row['case_no']) . "</td>
            <td>" . htmlspecialchars($row['case_type']) . "</td> 
            <td>" . htmlspecialchars($row['court_detail']) . "</td>
            <td>" . $parties . "</td>
            <td>" . $nextDate . "</td>
            <td>" . $status . "</td>
            <td>
                <a href='case_detail.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'>View</a>
                <a href='edit_case.php?id=" . $row['id'] . "' class='btn btn-sm btn-warning'>Edit</a>
            </td>
        </tr>";
    }
} else {
    $output = "<tr><td colspan='7' class='text-center'>No cases found assigned to you</td></tr>";
}

echo $output;
?>