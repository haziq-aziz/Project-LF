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

// Base query
$query = "SELECT c.*, 
         (SELECT GROUP_CONCAT(case_no) FROM cases WHERE client_id = c.id) as case_numbers
         FROM clients c";

// Add search conditions if search term is provided
if (!empty($search)) {
    $search = "%$search%";
    $query .= " WHERE c.name LIKE ? OR c.email LIKE ? OR 
              EXISTS (SELECT 1 FROM cases WHERE client_id = c.id AND case_no LIKE ?)";
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
        // Format case numbers for display
        $caseNumbers = $row['case_numbers'] ? htmlspecialchars($row['case_numbers']) : 'No cases';
        
        // Determine status based on if client has cases
        $status = $row['case_numbers'] ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-secondary">No Cases</span>';
        
        $output .= "<tr>
            <td>" . $row['id'] . "</td>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td>" . htmlspecialchars($row['phone']) . "</td>
            <td>" . $caseNumbers . "</td>
            <td>" . $status . "</td>
            <td>
                <a href='client_detail.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'>View</a>
                <a href='client_edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-warning'>Edit</a>
                <a href='javascript:void(0);' onclick='confirmDelete(" . $row['id'] . ", \"" . htmlspecialchars($row['name'], ENT_QUOTES) . "\")' 
                   class='btn btn-sm btn-danger'>Delete</a>
            </td>
        </tr>";
    }
} else {
    $output = "<tr><td colspan='7' class='text-center'>No clients found</td></tr>";
}

echo $output;
?>