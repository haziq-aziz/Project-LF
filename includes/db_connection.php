<?php
// Include timezone configuration if it exists
$timezone_file = __DIR__ . '/timezone_config.php';
if (file_exists($timezone_file)) {
    require_once($timezone_file);
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "lawyer_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set MySQL session timezone using offset instead of name
// For Malaysia (UTC+8), use +08:00
try {
    // Use numeric offset for MySQL timezone instead of named timezone
    $conn->query("SET time_zone = '+08:00'");
} catch (Exception $e) {
    error_log("Failed to set MySQL timezone: " . $e->getMessage());
    // Continue execution even if timezone setting fails
}
?>