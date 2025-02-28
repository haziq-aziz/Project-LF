<?php
require '../includes/db_connection.php';

if (!isset($_GET['case_id']) || empty($_GET['case_id'])) {
    header('Location: ../../staff/case_view.php');
    exit();
}

$case_id = intval($_GET['case_id']);

$query = "SELECT cases.*, clients.name AS client_name, clients.email AS client_email, clients.phone AS client_phone,
                 clients.address AS client_address, clients.country AS client_country, clients.state AS client_state, clients.city AS client_city,
                 users.name AS lawyer_name
          FROM cases
          JOIN clients ON cases.client_id = clients.id
          JOIN users ON cases.lawyer_id = users.id
          WHERE cases.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $case_id);
$stmt->execute();
$result = $stmt->get_result();
$case = $result->fetch_assoc();

if (!$case) {
    header('Location: case_view.php');
    exit();
}

?>