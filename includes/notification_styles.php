<?php

?>
<link rel="stylesheet" href="<?php echo isset($notification_css_path) ? $notification_css_path : '../assets/css/notification.css'; ?>" />
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize notification system
  updateNotificationCount();
  
  // Check for new notifications every minute
  setInterval(updateNotificationCount, 60000);
  
  // Ensure notification dropdown appears aligned with the notification icon
  document.getElementById('notificationDropdown')?.addEventListener('show.bs.dropdown', function () {
    setTimeout(function() {
      const dropdown = document.querySelector('.dropdown-menu[aria-labelledby="notificationDropdown"]');
      if (dropdown) {
        dropdown.style.right = 'auto';
        dropdown.style.left = '0';
        dropdown.style.transform = 'none';
        dropdown.style.position = 'absolute';
      }
    }, 0);
  });
});
</script>
