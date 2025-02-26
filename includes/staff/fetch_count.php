<?php
require '../db_connection.php';

// Count total clients
$sqlClients = "SELECT COUNT(*) AS total_clients FROM clients";
$resultClients = $conn->query($sqlClients);
$rowClients = $resultClients->fetch_assoc();
$totalClients = $rowClients['total_clients'];

// Count cases by case_stage
$sqlCases = "SELECT 
    SUM(CASE WHEN case_stage = 'Case Open' THEN 1 ELSE 0 END) AS open_cases,
    SUM(CASE WHEN case_stage = 'Case Ongoing' THEN 1 ELSE 0 END) AS ongoing_cases,
    SUM(CASE WHEN case_stage = 'Case Close' THEN 1 ELSE 0 END) AS closed_cases
    FROM cases";

$resultCases = $conn->query($sqlCases);
$rowCases = $resultCases->fetch_assoc();

echo json_encode([
    'total_clients' => $totalClients,
    'open_cases' => $rowCases['open_cases'],
    'ongoing_cases' => $rowCases['ongoing_cases'],
    'closed_cases' => $rowCases['closed_cases']
]);
?>
