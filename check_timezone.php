<?php
require_once('includes/db_connection.php');

echo "PHP Timezone: " . date_default_timezone_get() . "\n";
echo "PHP Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP UTC Time: " . gmdate('Y-m-d H:i:s') . "\n\n";

$result = $conn->query("SELECT @@global.time_zone AS global_tz, @@session.time_zone AS session_tz, NOW() AS current_time");
if ($row = $result->fetch_assoc()) {
    echo "MySQL Global Timezone: " . $row['global_tz'] . "\n";
    echo "MySQL Session Timezone: " . $row['session_tz'] . "\n";
    echo "MySQL Current Time: " . $row['current_time'] . "\n\n";
}

echo "Sample notification from database:\n";
$result = $conn->query("SELECT created_at, NOW() as now FROM notifications ORDER BY id DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "Notification created_at: " . $row['created_at'] . "\n";
    echo "Current MySQL time: " . $row['now'] . "\n";
    
    $then = new DateTime($row['created_at']);
    $now = new DateTime($row['now']);
    $interval = $now->diff($then);
    
    echo "Time difference: ";
    if ($interval->h > 0) {
        echo $interval->h . " hours ";
    }
    if ($interval->i > 0) {
        echo $interval->i . " minutes ";
    }
    if ($interval->s > 0) {
        echo $interval->s . " seconds";
    }
    echo ($interval->invert ? " ago" : " in future") . "\n";
}
?>
