<?php
require_once("../includes/db_connection.php");
require_once("../includes/notifications_helper.php");

$user_id = $_SESSION['user_id'];
$sql = "SELECT name, profile_picture FROM clients WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$name = $user['name'] ?? 'Name';
$profile_picture = $user['profile_picture'] ?? 'default.jpg';

// Get notifications
$notification_count = get_unread_notification_count($user_id, 'client');
$notifications = get_notifications($user_id, 'client', 5);
?>

<header class="app-header">
  <nav class="navbar navbar-expand-lg navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item d-block d-xl-none">
        <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
          <i class="ti ti-menu-2"></i>
        </a>
      </li>
      <li class="nav-item dropdown notification-dropdown">        <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="bell-icon-container">
            <i class="ti ti-bell-ringing"></i>
            <?php if ($notification_count > 0): ?>
            <div class="notification bg-primary rounded-circle"></div>
            <?php endif; ?>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-animate-up position-absolute" aria-labelledby="notificationDropdown" style="z-index: 1050; left: 0; right: auto; min-width: 300px;">
          <div class="message-header d-flex align-items-center justify-content-between p-3 border-bottom">
            <h5 class="mb-0">Notifications</h5>
            <?php if ($notification_count > 0): ?>
            <span class="badge bg-primary rounded-pill"><?php echo $notification_count; ?></span>
            <?php endif; ?>
          </div>
          <div class="message-body position-relative" style="max-height: 300px; overflow-y: auto;">            <?php if (!empty($notifications)): ?>
              <?php foreach ($notifications as $notification): ?>                <a href="<?php echo $notification['link'] ?: 'javascript:void(0)'; ?>" 
                   class="d-flex align-items-center gap-2 dropdown-item<?php echo $notification['is_read'] ? '' : ' fw-bold bg-light'; ?>"
                   data-notification-id="<?php echo $notification['id']; ?>"
                   <?php if ($notification['link']): ?>
                     onclick="markNotificationRead(<?php echo $notification['id']; ?>); return true;"
                   <?php else: ?>
                     onclick="markNotificationRead(<?php echo $notification['id']; ?>); return false;"
                   <?php endif; ?>>
                  <div class="position-relative">
                    <i class="ti ti-message-dots fs-6"></i>
                  </div>
                  <div>
                    <p class="mb-0"><?php echo htmlspecialchars($notification['title']); ?></p>
                    <small class="mb-0"><?php echo format_notification_date($notification['created_at']); ?></small>
                    <p class="mb-0 text-muted small"><?php echo htmlspecialchars(substr($notification['message'], 0, 50) . (strlen($notification['message']) > 50 ? '...' : '')); ?></p>
                  </div>
                </a>
              <?php endforeach; ?>
              <div class="d-grid p-2">
                <button class="btn btn-sm btn-outline-primary" onclick="markAllNotificationsRead()">
                  Mark all as read
                </button>
              </div>
            <?php else: ?>
              <div class="d-flex align-items-center gap-2 dropdown-item">
                <p class="mb-0 text-center w-100 py-2">No notifications</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </li>
    </ul>
    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
      <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
        <li class="nav-item me-3 d-flex align-items-center">
          <p class="mb-0">Welcome back, <span class="text-primary"><?php echo htmlspecialchars($name); ?></span></p>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="../../uploads/profile_picture/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" width="35" height="35" class="rounded-circle">
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
            <div class="message-body">
              <a href="../../client/profile.php" class="d-flex align-items-center gap-2 dropdown-item">
                <i class="ti ti-settings fs-6"></i>
                <p class="mb-0 fs-3">Edit Profile</p>
              </a>
              <a href="../../auth/logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </nav>
</header>

<script>
// Function to mark a notification as read
function markNotificationRead(notificationId) {
  console.log('Marking notification as read: ' + notificationId);
  
  fetch('../../includes/notification_ajax.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=mark_read&notification_id=' + notificationId
  })
  .then(response => {
    console.log('Server response status:', response.status);
    // First get the response as text
    return response.text().then(text => {
      try {
        // Try to parse as JSON
        return JSON.parse(text);
      } catch(e) {
        // If not valid JSON, log the raw response and throw an error
        console.error('Invalid JSON response:', text);
        console.error('Parse error:', e);
        throw new Error('Invalid JSON response from server');
      }
    });
  })
  .then(data => {
    console.log('Mark read response:', data);
    if (data && data.success) {
      // Update UI to show notification as read
      const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
      if (notificationElement) {
        notificationElement.classList.remove('fw-bold', 'bg-light');
      }
      // Refresh notification count
      updateNotificationCount();
    } else {
      console.error('Failed to mark notification as read:', data ? data.message : 'Unknown error');
    }
  })
  .catch(error => {
    console.error('Error marking notification as read:', error);
  });
}

// Function to mark all notifications as read
function markAllNotificationsRead() {
  console.log('Marking all notifications as read');
  
  fetch('../../includes/notification_ajax.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=mark_all_read'
  })
  .then(response => {
    console.log('Server response status:', response.status);
    return response.text().then(text => {
      // Try to parse as JSON, but handle non-JSON responses
      try {
        return JSON.parse(text);
      } catch(e) {
        console.error('Error parsing JSON response:', e);
        console.error('Raw response:', text);
        throw new Error('Invalid JSON response');
      }
    });
  })
  .then(data => {
    console.log('Mark all read response:', data);
    if (data.success) {
      // Update UI without reloading the page
      const notifications = document.querySelectorAll('.dropdown-item[data-notification-id]');
      notifications.forEach(n => {
        n.classList.remove('fw-bold', 'bg-light');
      });
      
      // Hide notification indicator
      const indicator = document.querySelector('.notification');
      if (indicator) indicator.style.display = 'none';
      
      // Update badge count
      const badge = document.querySelector('.badge.rounded-pill');
      if (badge) badge.style.display = 'none';
    }
  })
  .catch(error => {
    console.error('Error marking all notifications as read:', error);
  });
}

// Function to update notification count
function updateNotificationCount() {
  console.log('Updating notification count');
  
  fetch('../../includes/notification_ajax.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=get_unread_count'
  })
  .then(response => {
    console.log('Server response status for count:', response.status);
    return response.text().then(text => {
      try {
        return JSON.parse(text);
      } catch(e) {
        console.error('Invalid JSON response in count update:', text);
        console.error('Parse error:', e);
        throw new Error('Invalid JSON response');
      }
    });
  })
  .then(data => {
    console.log('Notification count update response:', data);
    if (data && data.success) {
      // Update UI with new count
      const indicator = document.querySelector('.notification');
      const countBadge = document.querySelector('.badge.bg-primary.rounded-pill');
      
      if (data.count > 0) {
        // Update the badge count
        if (countBadge) {
          countBadge.textContent = data.count;
          countBadge.style.display = 'inline-block';
        }
        
        // Update the notification dot
        if (indicator) {
          indicator.style.display = 'block';
        } else {
          const bellIcon = document.querySelector('.ti-bell-ringing');
          if (bellIcon) {
            const newIndicator = document.createElement('div');
            newIndicator.className = 'notification bg-primary rounded-circle';
            bellIcon.parentNode.appendChild(newIndicator);
          }
        }
      } else {
        // Hide the badge count
        if (countBadge) {
          countBadge.style.display = 'none';
        }
        
        // Hide the notification dot
        if (indicator) {
          indicator.style.display = 'none';
        }
      }
    }
  })
  .catch(error => {
    console.error('Error updating notification count:', error);
  });
}

// Check for new notifications periodically (every 60 seconds)
setInterval(updateNotificationCount, 60000);
</script>