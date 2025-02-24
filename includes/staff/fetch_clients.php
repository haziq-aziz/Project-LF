<?php
session_start();
require '../db_connection.php';

$entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : "";
$offset = ($page - 1) * $entriesPerPage;

$query = "SELECT id, name, email, phone FROM clients WHERE 1";

if(!empty($search)) {
    $search = "%$search%";
    $query .= " AND name LIKE ?";
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
            <td>" . htmlspecialchars($row['id']) . "</td>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td>" . htmlspecialchars($row['phone']) . "</td>
            <td>C001</td>
            <td><span class='badge bg-success'>Active</span></td>
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
