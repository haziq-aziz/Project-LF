<?php
/**
 * Timezone Configuration
 * 
 * This file handles the system timezone settings.
 * Modify the timezone according to your location.
 */

// Set the default timezone for the entire application
// For Malaysia (UTC+8), use 'Asia/Kuala_Lumpur' or 'Asia/Singapore'
// Windows servers may not have all timezone data, so we use a fallback approach
try {
    date_default_timezone_set('Asia/Kuala_Lumpur');
} catch (Exception $e) {
    // Fallback to Asia/Singapore (same timezone)
    try {
        date_default_timezone_set('Asia/Singapore');
    } catch (Exception $e) {
        // Last resort fallback to explicit UTC+8
        date_default_timezone_set('Etc/GMT-8');
    }
}

// Verify timezone was set correctly
$timezone = date_default_timezone_get();
if ($timezone != 'Asia/Kuala_Lumpur' && $timezone != 'Asia/Singapore' && $timezone != 'Etc/GMT-8') {
    error_log("Warning: Could not set desired timezone. Using: " . $timezone);
}

/**
 * Get formatted datetime with current timezone
 * 
 * @param string $datetime MySQL datetime string
 * @param string $format PHP date format
 * @return string Formatted datetime
 */
function get_formatted_datetime($datetime, $format = 'Y-m-d H:i:s') {
    $date = new DateTime($datetime);
    return $date->format($format);
}

/**
 * Get current datetime in MySQL format
 * 
 * @return string Current datetime in Y-m-d H:i:s format
 */
function get_current_datetime() {
    return date('Y-m-d H:i:s');
}
