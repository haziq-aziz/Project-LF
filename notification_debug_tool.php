<?php
/**
 * Notification System Advanced Debug Tool
 * 
 * This tool provides comprehensive testing for the notification system, including:
 * - Database operations testing
 * - JavaScript functionality testing
 * - Time/Timezone diagnostics
 */

session_start();
require_once('includes/db_connection.php');
require_once('includes/notifications_helper.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] === 'admin' ? 'staff' : $_SESSION['role'];

// Handle different actions
$action_result = null;
$notifications = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Create test notification
            $title = "Test Notification " . date('Y-m-d H:i:s');
            $message = "This is a test notification created at " . date('Y-m-d H:i:s');
            $link = "";
            $result = add_notification($user_id, $user_type, $title, $message, $link);
            $action_result = [
                'action' => 'Create Notification',
                'success' => $result,
                'notification_id' => $result ? $conn->insert_id : 0
            ];
            break;
            
        case 'mark_read':
            // Mark notification as read
            $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
            if ($notification_id > 0) {
                $result = mark_notification_read($notification_id, $user_id, $user_type);
                $action_result = [
                    'action' => 'Mark as Read',
                    'notification_id' => $notification_id,
                    'success' => $result
                ];
            }
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read
            $result = mark_all_notifications_read($user_id, $user_type);
            $action_result = [
                'action' => 'Mark All as Read',
                'success' => $result
            ];
            break;
            
        case 'delete':
            // Delete notification
            $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
            if ($notification_id > 0) {
                $result = delete_notification($notification_id, $user_id, $user_type);
                $action_result = [
                    'action' => 'Delete Notification',
                    'notification_id' => $notification_id,
                    'success' => $result
                ];
            }
            break;
            
        case 'check_db':
            // Direct DB query to verify notification status
            $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
            if ($notification_id > 0) {
                $query = "SELECT * FROM notifications WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $notification_id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if ($result) {
                    $action_result = [
                        'action' => 'Check DB Record',
                        'notification_id' => $notification_id,
                        'record' => $result
                    ];
                } else {
                    $action_result = [
                        'action' => 'Check DB Record',
                        'notification_id' => $notification_id,
                        'error' => 'Record not found'
                    ];
                }
            }
            break;
    }
}

// Get all notifications for the user
$notifications = get_all_notifications($user_id, $user_type);

// System information
$timezone_info = [
    'php_version' => PHP_VERSION,
    'system_timezone' => date_default_timezone_get(),
    'php_date' => date('Y-m-d H:i:s'),
    'timestamp' => time()
];

// Get MySQL timezone
$sql = "SELECT @@global.time_zone AS global_tz, @@session.time_zone AS session_tz, NOW() AS mysql_time";
$result = $conn->query($sql);
$timezone_info['mysql'] = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Notification Debug</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .code-block {
            background-color: #f8f9fa;
            border: 1px solid #eaecef;
            border-radius: 3px;
            padding: 10px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        
        .notification-item {
            transition: background-color 0.3s;
        }
        
        .notification-item.highlight {
            background-color: #ffc107 !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h1>Advanced Notification System Debugging</h1>
        
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h4>User Info:</h4>
                    <p>ID: <?= $user_id ?>, Type: <?= $user_type ?></p>
                </div>
            </div>
        </div>
        
        <?php if ($action_result): ?>
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-success">
                        <h4>Action Results:</h4>
                        <div class="code-block"><?php print_r($action_result); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Left Column: Tools -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Database Operations</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" class="mb-3">
                            <input type="hidden" name="action" value="create">
                            <button type="submit" class="btn btn-success w-100">Create Test Notification</button>
                        </form>
                        
                        <form method="post" class="mb-3">
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="btn btn-warning w-100">Mark All as Read</button>
                        </form>
                        
                        <form method="post" class="mb-3">
                            <div class="input-group">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="number" name="notification_id" class="form-control" placeholder="Notification ID" required>
                                <button type="submit" class="btn btn-info">Mark as Read</button>
                            </div>
                        </form>
                        
                        <form method="post" class="mb-3">
                            <div class="input-group">
                                <input type="hidden" name="action" value="delete">
                                <input type="number" name="notification_id" class="form-control" placeholder="Notification ID" required>
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </div>
                        </form>
                        
                        <form method="post">
                            <div class="input-group">
                                <input type="hidden" name="action" value="check_db">
                                <input type="number" name="notification_id" class="form-control" placeholder="Notification ID" required>
                                <button type="submit" class="btn btn-secondary">Check in DB</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">JavaScript Testing</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="jsNotificationId" class="form-label">Notification ID:</label>
                            <input type="number" id="jsNotificationId" class="form-control">
                        </div>
                        <button id="jsMarkReadBtn" class="btn btn-primary mb-2 w-100">Mark as Read via AJAX</button>
                        <button id="jsMarkAllReadBtn" class="btn btn-warning mb-2 w-100">Mark All as Read via AJAX</button>
                        <button id="jsRefreshCountBtn" class="btn btn-info w-100">Update Notification Count</button>
                        
                        <hr>
                        <h6>AJAX Response:</h6>
                        <div id="ajaxResponse" class="code-block" style="max-height: 150px; overflow-y: auto;">No AJAX calls made yet</div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">System Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>PHP Version</th>
                                <td><?= $timezone_info['php_version'] ?></td>
                            </tr>
                            <tr>
                                <th>PHP Timezone</th>
                                <td><?= $timezone_info['system_timezone'] ?></td>
                            </tr>
                            <tr>
                                <th>PHP Date</th>
                                <td><?= $timezone_info['php_date'] ?></td>
                            </tr>
                            <tr>
                                <th>PHP Timestamp</th>
                                <td><?= $timezone_info['timestamp'] ?></td>
                            </tr>
                            <tr>
                                <th>MySQL Global TZ</th>
                                <td><?= $timezone_info['mysql']['global_tz'] ?></td>
                            </tr>
                            <tr>
                                <th>MySQL Session TZ</th>
                                <td><?= $timezone_info['mysql']['session_tz'] ?></td>
                            </tr>
                            <tr>
                                <th>MySQL DateTime</th>
                                <td><?= $timezone_info['mysql']['mysql_time'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Notifications -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Your Notifications (<?= count($notifications) ?>)</h5>
                        <a href="?" class="btn btn-sm btn-light">Refresh</a>
                    </div>
                    <div class="card-body p-0" style="max-height: 700px; overflow-y: auto;">
                        <?php if (!empty($notifications)): ?>
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Created</th>
                                        <th>Formatted</th>
                                        <th>Status</th>
                                        <th>Read At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notifications as $notification): ?>
                                        <tr class="notification-item <?= $notification['is_read'] ? '' : 'table-light fw-bold' ?>" 
                                            data-notification-id="<?= $notification['id'] ?>">
                                            <td><?= $notification['id'] ?></td>
                                            <td><?= htmlspecialchars($notification['title']) ?></td>
                                            <td><?= $notification['created_at'] ?></td>
                                            <td><?= format_notification_date($notification['created_at']) ?></td>
                                            <td><?= $notification['is_read'] ? 'Read' : 'Unread' ?></td>
                                            <td><?= $notification['read_at'] ?: '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="p-4 text-center">
                                <p>No notifications found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxResponse = document.getElementById('ajaxResponse');
            
            // Mark notification as read via AJAX
            document.getElementById('jsMarkReadBtn').addEventListener('click', function() {
                const notificationId = document.getElementById('jsNotificationId').value;
                if (!notificationId) {
                    ajaxResponse.textContent = 'Please enter a notification ID';
                    return;
                }
                
                ajaxResponse.textContent = 'Processing...';
                
                fetch('includes/notification_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_read&notification_id=' + notificationId
                })
                .then(response => {
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch(e) {
                            throw {
                                error: 'JSON Parse Error',
                                message: e.message,
                                rawResponse: text
                            };
                        }
                    });
                })
                .then(data => {
                    ajaxResponse.textContent = 'Response: ' + JSON.stringify(data, null, 2);
                    
                    if (data.success) {
                        // Highlight the row
                        const row = document.querySelector(`tr[data-notification-id="${notificationId}"]`);
                        if (row) {
                            row.classList.add('highlight');
                            row.classList.remove('fw-bold', 'table-light');
                            
                            // Update status cell
                            row.cells[4].textContent = 'Read';
                            
                            setTimeout(() => {
                                row.classList.remove('highlight');
                            }, 3000);
                        }
                    }
                })
                .catch(error => {
                    ajaxResponse.textContent = 'Error: ' + JSON.stringify(error, null, 2);
                });
            });
            
            // Mark all as read via AJAX
            document.getElementById('jsMarkAllReadBtn').addEventListener('click', function() {
                ajaxResponse.textContent = 'Processing...';
                
                fetch('includes/notification_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_all_read'
                })
                .then(response => {
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch(e) {
                            throw {
                                error: 'JSON Parse Error',
                                message: e.message,
                                rawResponse: text
                            };
                        }
                    });
                })
                .then(data => {
                    ajaxResponse.textContent = 'Response: ' + JSON.stringify(data, null, 2);
                    
                    if (data.success) {
                        // Update all unread rows
                        document.querySelectorAll('tr.fw-bold.table-light').forEach(row => {
                            row.classList.add('highlight');
                            row.classList.remove('fw-bold', 'table-light');
                            
                            // Update status cell
                            row.cells[4].textContent = 'Read';
                            
                            setTimeout(() => {
                                row.classList.remove('highlight');
                            }, 3000);
                        });
                    }
                })
                .catch(error => {
                    ajaxResponse.textContent = 'Error: ' + JSON.stringify(error, null, 2);
                });
            });
            
            // Update notification count
            document.getElementById('jsRefreshCountBtn').addEventListener('click', function() {
                ajaxResponse.textContent = 'Processing...';
                
                fetch('includes/notification_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_unread_count'
                })
                .then(response => {
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch(e) {
                            throw {
                                error: 'JSON Parse Error',
                                message: e.message,
                                rawResponse: text
                            };
                        }
                    });
                })
                .then(data => {
                    ajaxResponse.textContent = 'Response: ' + JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    ajaxResponse.textContent = 'Error: ' + JSON.stringify(error, null, 2);
                });
            });
        });
    </script>
</body>
</html>
