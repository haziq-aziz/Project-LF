<?php
/**
 * Notification System Debug Tool
 * 
 * This tool helps test and debug the notification system.
 */

session_start();
require_once('includes/db_connection.php');
require_once('includes/notifications_helper.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You need to be logged in to use this tool.");
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] === 'admin' ? 'staff' : $_SESSION['role'];

// Function to create test notification
function create_test_notification($recipient_id, $recipient_type) {
    global $conn;
    $title = "Test Notification " . date('Y-m-d H:i:s');
    $message = "This is a test notification created at " . date('Y-m-d H:i:s');
    $link = "";
    
    $sql = "INSERT INTO notifications (title, message, recipient_id, recipient_type, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssis', $title, $message, $recipient_id, $recipient_type);
    $success = $stmt->execute();
    $notification_id = $conn->insert_id;
    $stmt->close();
    
    return ['success' => $success, 'notification_id' => $notification_id];
}

// Handle actions
$action = isset($_POST['action']) ? $_POST['action'] : '';
$result = null;

if ($action === 'create_notification') {
    $result = create_test_notification($user_id, $user_type);
} elseif ($action === 'mark_read') {
    $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
    if ($notification_id > 0) {
        $result = ['success' => mark_notification_read($notification_id, $user_id, $user_type)];
    }
} elseif ($action === 'mark_all_read') {
    $result = ['success' => mark_all_notifications_read($user_id, $user_type)];
} elseif ($action === 'check_notifications') {
    $notifications = get_notifications($user_id, $user_type, 100);
    $result = ['notifications' => $notifications];
}

// Get current notifications for display
$notifications = get_notifications($user_id, $user_type, 100);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification System Debug</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Notification System Debug Tool</h1>
        <p>User ID: <?php echo $user_id; ?>, Type: <?php echo $user_type; ?></p>
        
        <?php if ($result): ?>
            <div class="alert alert-info">
                <h4>Last Action Result:</h4>
                <pre><?php print_r($result); ?></pre>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Actions</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" class="mb-3">
                            <input type="hidden" name="action" value="create_notification">
                            <button type="submit" class="btn btn-primary">Create Test Notification</button>
                        </form>
                        
                        <form method="post" class="mb-3">
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="btn btn-warning">Mark All as Read</button>
                        </form>
                        
                        <form method="post" class="mb-3">
                            <div class="input-group">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="number" name="notification_id" class="form-control" placeholder="Notification ID">
                                <button type="submit" class="btn btn-info">Mark as Read</button>
                            </div>
                        </form>
                        
                        <form method="post">
                            <input type="hidden" name="action" value="check_notifications">
                            <button type="submit" class="btn btn-secondary">Refresh Notifications</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h3>PHP Info</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                        <p><strong>System Timezone:</strong> <?php echo date_default_timezone_get(); ?></p>
                        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                        <p><strong>MySQL Time:</strong> 
                        <?php
                        $sql = "SELECT NOW() as mysql_time";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['mysql_time'];
                        ?>
                        </p>
                        <p><strong>MySQL Timezone Setting:</strong> 
                        <?php
                        $sql = "SELECT @@time_zone as tz";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['tz'];
                        ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Your Notifications (<?php echo count($notifications); ?>)</h3>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        <?php if (!empty($notifications)): ?>
                            <table class="table table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Created</th>
                                        <th>Read</th>
                                        <th>Read At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notifications as $notification): ?>
                                        <tr class="<?php echo $notification['is_read'] ? '' : 'table-light fw-bold'; ?>">
                                            <td><?php echo $notification['id']; ?></td>
                                            <td><?php echo htmlspecialchars($notification['title']); ?></td>
                                            <td><?php echo $notification['created_at']; ?></td>
                                            <td><?php echo $notification['is_read'] ? 'Yes' : 'No'; ?></td>
                                            <td><?php echo $notification['read_at'] ?: '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center">No notifications found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
