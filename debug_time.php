<?php
/**
 * Timezone Check Script
 * This script helps debug timezone issues by displaying various time-related information
 */
require_once('includes/db_connection.php');

echo "<pre>";
echo "===== PHP TIMEZONE INFORMATION =====\n";
echo "PHP Timezone: " . date_default_timezone_get() . "\n";
echo "PHP Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP UTC Time: " . gmdate('Y-m-d H:i:s') . "\n";
echo "Timezone Offset: " . (date('Z') / 3600) . " hours\n\n";

echo "===== MYSQL TIMEZONE INFORMATION =====\n";
$result = $conn->query("SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz, NOW() as mysql_time, UTC_TIMESTAMP() as mysql_utc");
if ($row = $result->fetch_assoc()) {
    echo "MySQL Global Timezone: " . $row['global_tz'] . "\n";
    echo "MySQL Session Timezone: " . $row['session_tz'] . "\n";
    echo "MySQL Current Time: " . $row['mysql_time'] . "\n";
    echo "MySQL UTC Time: " . $row['mysql_utc'] . "\n\n";
}

echo "===== NOTIFICATION TIMESTAMP TEST =====\n";
// Get a sample notification
$notif_result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 1");
if ($notif = $notif_result->fetch_assoc()) {
    echo "Notification ID: " . $notif['id'] . "\n";
    echo "Database Timestamp: " . $notif['created_at'] . "\n";
    
    // PHP interpretation of the timestamp
    $timestamp = strtotime($notif['created_at']);
    echo "PHP Interpretation: " . date('Y-m-d H:i:s', $timestamp) . "\n";
    
    // Time difference calculation
    $now = time();
    $diff_seconds = $now - $timestamp;
    $diff_hours = floor($diff_seconds / 3600);
    $diff_minutes = floor(($diff_seconds % 3600) / 60);
    
    echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
    echo "Time Difference: " . $diff_hours . " hours, " . $diff_minutes . " minutes\n\n";
    
    // Format using format_notification_date function
    if (function_exists('format_notification_date')) {
        echo "format_notification_date() output: " . format_notification_date($notif['created_at']) . "\n";
    }
}

echo "===== NOTIFICATION TABLE INFO =====\n";
$count_result = $conn->query("SELECT COUNT(*) as total FROM notifications");
$count = $count_result->fetch_assoc()['total'];
echo "Total notifications: $count\n";

$range = $conn->query("SELECT MIN(created_at) as oldest, MAX(created_at) as newest FROM notifications");
if ($range_data = $range->fetch_assoc()) {
    echo "Oldest notification: " . $range_data['oldest'] . "\n";
    echo "Newest notification: " . $range_data['newest'] . "\n";
}

echo "</pre>";
?>
