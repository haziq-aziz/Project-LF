<?php
session_start();
require '../db_connection.php';

$entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : "";
$offset = ($page - 1) * $entriesPerPage;

// Query to fetch client details along with case number and case stage
$query = "SELECT clients.id, clients.name, clients.email, clients.phone, 
                 cases.case_no, cases.case_stage 
          FROM clients 
          LEFT JOIN cases ON clients.id = cases.client_id";

if (!empty($search)) {
    $search = "%$search%";
    $query .= " WHERE clients.name LIKE ?";
}

$query .= " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $stmt->bind_param("sii", $search, $entriesPerPage, $offset);
} else {
    $stmt->bind_param("ii", $entriesPerPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$output = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determine case number or "Assign Case"
        $caseNo = !empty($row['case_no']) ? $row['case_no'] : "<span class='text-danger'>Assign Case</span>";
        
        // Assign badge based on case stage
        if ($row['case_stage'] === "Case Open") {
            $caseStage = "<span class='badge bg-info'>Case Open</span>";
        } elseif ($row['case_stage'] === "Case Ongoing") {
            $caseStage = "<span class='badge bg-warning'>Case Ongoing</span>";
        } elseif ($row['case_stage'] === "Case Close") {
            $caseStage = "<span class='badge bg-success'>Case Close</span>";
        } else {
            $caseStage = "<span class='badge bg-secondary'>Pending</span>";
        }

        $output .= "<tr>
            <td>" . htmlspecialchars($row['id']) . "</td>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td>" . htmlspecialchars($row['phone']) . "</td>
            <td>{$caseNo}</td>
            <td>{$caseStage}</td>
            <td>
                <button class='btn btn-sm btn-primary'>Edit</button>
                <button class='btn btn-sm btn-danger'>Delete</button>
            </td>
        </tr>";
    }
} else {
    $output = "<tr><td colspan='7' class='text-center'>No clients found</td></tr>";
}

echo $output;
?>
