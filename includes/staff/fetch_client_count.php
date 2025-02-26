<?php
require '../db_connection.php';

$sql = "SELECT COUNT(*) AS total FROM clients";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo json_encode(['total' => $row['total']]);
?>
