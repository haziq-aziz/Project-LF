<?php
session_start();
require_once('../includes/db_connection.php');
require_once('../includes/notifications_helper.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] === 'admin' ? 'staff' : $_SESSION['role'];

// Process form submission for sending a test notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'test_notification') {
        $recipient_id = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : $user_id;
        $recipient_type = isset($_POST['recipient_type']) ? $_POST['recipient_type'] : $user_type;
        $title = isset($_POST['title']) ? $_POST['title'] : 'Test Notification';
        $message = isset($_POST['message']) ? $_POST['message'] : 'This is a test notification sent at ' . date('Y-m-d H:i:s');
        $link = isset($_POST['link']) ? $_POST['link'] : '/staff/dashboard.php';
        
        $result = add_notification($recipient_id, $recipient_type, $title, $message, $link);
        $status = $result ? 'success' : 'error';
        $message = $result ? 'Test notification sent successfully' : 'Failed to send test notification';
        
        // Store for display
        $_SESSION['notification_test_status'] = $status;
        $_SESSION['notification_test_message'] = $message;
        
        // Redirect to avoid form resubmission
        header('Location: notification_debug.php');
        exit();
    }
    
    if ($_POST['action'] === 'mark_read') {
        $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
        if ($notification_id > 0) {
            $result = mark_notification_read($notification_id, $user_id, $user_type);
            $status = $result ? 'success' : 'error';
            $message = $result ? "Notification {$notification_id} marked as read" : "Failed to mark notification {$notification_id} as read";
            
            // Store for display
            $_SESSION['notification_test_status'] = $status;
            $_SESSION['notification_test_message'] = $message;
        }
        
        // Redirect to avoid form resubmission
        header('Location: notification_debug.php');
        exit();
    }
}

// Get notification data
$notifications = get_all_notifications($user_id, $user_type);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Debug Tool</title>
    <link rel="stylesheet" href="../assets/css/dashboard.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php 
            if ($user_type === 'staff' || $user_type === 'admin') {
                require_once('../includes/staff/sidebar.php');
            } else {
                require_once('../includes/client/sidebar.php');
            }
            ?>
            
            <div class="body-wrapper">
                <!-- Navbar -->
                <?php 
                if ($user_type === 'staff' || $user_type === 'admin') {
                    require_once('../includes/staff/navbar.php');
                } else {
                    require_once('../includes/client/navbar.php');
                }
                ?>
                
                <!-- Main Content -->
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title fw-semibold mb-4">Notification Debug Tool</h4>
                            
                            <?php if (isset($_SESSION['notification_test_status'])): ?>
                            <div class="alert alert-<?php echo $_SESSION['notification_test_status'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['notification_test_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php 
                                unset($_SESSION['notification_test_status']);
                                unset($_SESSION['notification_test_message']);
                            endif; 
                            ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5>Send Test Notification</h5>
                                        </div>
                                        <div class="card-body">
                                            <form method="post" action="notification_debug.php">
                                                <input type="hidden" name="action" value="test_notification">
                                                
                                                <div class="mb-3">
                                                    <label for="recipient_id" class="form-label">Recipient ID</label>
                                                    <input type="number" class="form-control" id="recipient_id" name="recipient_id" value="<?php echo $user_id; ?>">
                                                    <div class="form-text">Default: your user ID</div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="recipient_type" class="form-label">Recipient Type</label>
                                                    <select class="form-select" id="recipient_type" name="recipient_type">
                                                        <option value="staff" <?php echo $user_type === 'staff' || $user_type === 'admin' ? 'selected' : ''; ?>>Staff</option>
                                                        <option value="client" <?php echo $user_type === 'client' ? 'selected' : ''; ?>>Client</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="title" class="form-label">Notification Title</label>
                                                    <input type="text" class="form-control" id="title" name="title" value="Test Notification">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="message" class="form-label">Notification Message</label>
                                                    <textarea class="form-control" id="message" name="message" rows="3">This is a test notification sent at <?php echo date('Y-m-d H:i:s'); ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="link" class="form-label">Notification Link</label>
                                                    <input type="text" class="form-control" id="link" name="link" value="/<?php echo $user_type; ?>/dashboard.php">
                                                </div>
                                                
                                                <button type="submit" class="btn btn-primary">Send Test Notification</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5>System Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th>PHP Timezone</th>
                                                        <td><?php echo date_default_timezone_get(); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Server Time</th>
                                                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>UTC Time</th>
                                                        <td><?php echo gmdate('Y-m-d H:i:s'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Time Difference</th>
                                                        <td><?php echo (strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'))) / 3600; ?> hours</td>
                                                    </tr>
                                                    <tr>
                                                        <th>MySQL Time</th>
                                                        <td><?php 
                                                            $result = $conn->query("SELECT NOW() as now");
                                                            echo $result->fetch_assoc()['now']; 
                                                        ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>MySQL Timezone</th>
                                                        <td><?php 
                                                            $result = $conn->query("SELECT @@time_zone as tz");
                                                            echo $result->fetch_assoc()['tz']; 
                                                        ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Your Notifications</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($notifications)): ?>
                                    <div class="alert alert-info">You have no notifications.</div>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Title</th>
                                                    <th>Message</th>
                                                    <th>Created</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($notifications as $notification): ?>
                                                <tr>
                                                    <td><?php echo $notification['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($notification['title']); ?></td>
                                                    <td><?php echo htmlspecialchars(substr($notification['message'], 0, 50)) . (strlen($notification['message']) > 50 ? '...' : ''); ?></td>
                                                    <td>
                                                        <?php echo $notification['created_at']; ?><br>
                                                        <small class="text-muted"><?php echo format_notification_date($notification['created_at']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($notification['is_read']): ?>
                                                        <span class="badge bg-success">Read</span>
                                                        <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Unread</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!$notification['is_read']): ?>
                                                        <form method="post" action="notification_debug.php" style="display: inline;">
                                                            <input type="hidden" name="action" value="mark_read">
                                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-primary">Mark as Read</button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
