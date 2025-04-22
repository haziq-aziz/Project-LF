<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Get pagination parameters
$entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $entriesPerPage;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query - Now joining with clients table to get client names
$query = "SELECT c.id, c.case_no, c.case_type, c.court_detail, c.first_hearing_date, c.case_stage, 
         c.respondent_name, c.petitioner_name, c.client_id, c.client_role, cl.name as client_name 
         FROM cases c 
         LEFT JOIN clients cl ON c.client_id = cl.id";

// Add search conditions if search term is provided
if (!empty($search)) {
    $search = "%$search%";
    $query .= " WHERE c.case_no LIKE ? OR c.respondent_name LIKE ? OR c.petitioner_name LIKE ?";
}

// Add pagination
$query .= " ORDER BY c.id DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $stmt->bind_param("sssii", $search, $search, $search, $entriesPerPage, $offset);
} else {
    $stmt->bind_param("ii", $entriesPerPage, $offset);
}

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
        
        // Generate table row
        $output .= "<tr>
            <td>" . htmlspecialchars($row['case_no']) . "</td>
            <td>" . htmlspecialchars($row['case_type']) . "</td> 
            <td>" . htmlspecialchars($row['court_detail']) . "</td>
            <td>" . $parties . "</td>
            <td>" . $nextDate . "</td>
            <td>" . htmlspecialchars($row['case_stage']) . "</td>
            <td>
                <a href='case_detail.php?case_id=" . $row['id'] . "' class='btn btn-sm btn-primary'>View Details</a>
                <a href='case_edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-warning'>Edit</a>
                <a href='../includes/staff/delete_case.php?id=" . $row['id'] . "' 
                   class='btn btn-sm btn-danger'
                   onclick=\"return confirm('Are you sure you want to delete this case? This action cannot be undone.');\">Delete</a>
            </td>
        </tr>";
    }
} else {
    $output = "<tr><td colspan='7' class='text-center'>No cases found</td></tr>";
}

echo $output;
?>