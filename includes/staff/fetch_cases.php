<?php
session_start();
require '../db_connection.php';

$entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : "";
$offset = ($page - 1) * $entriesPerPage;

// Query to fetch case details
$query = "SELECT cases.id, cases.client_id, cases.case_no, cases.case_type, cases.court_detail, 
                 cases.respondent_name, cases.respondent_advocate, 
                 cases.first_hearing_date, cases.case_stage,
                 clients.name AS client_name
          FROM cases 
          LEFT JOIN clients ON cases.client_id = clients.id";

if (!empty($search)) {
    $search = "%$search%";
    $query .= " WHERE cases.case_no LIKE ?";
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
        $output .= "<tr>
            <td>" . htmlspecialchars($row['case_no']) . "</td>
            <td>" . htmlspecialchars($row['case_type']) . "</td> 
            <td>" . htmlspecialchars($row['court_detail']) . "</td>
            <td>" . htmlspecialchars($row['client_name']) . " vs " . htmlspecialchars($row['respondent_name']) . "</td>
            <td>" . htmlspecialchars($row['first_hearing_date']) . "</td>
            <td>" . htmlspecialchars($row['case_stage']) . "</td>
            <td>
                <a href='../staff/case_detail.php?case_id=" . $row['id'] . "' class='btn btn-sm btn-primary'>View Details</a>
                <button class='btn btn-sm btn-danger'>Delete</button>
            </td>
        </tr>";
    }
} else {
    $output = "<tr><td colspan='7' class='text-center'>No cases found</td></tr>";
}

echo $output;
?>