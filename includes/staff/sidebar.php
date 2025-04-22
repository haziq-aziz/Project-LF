<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../db_connection.php';
}

?>
<aside class="left-sidebar">
  <div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="dashboard.php" class="text-nowrap logo-img">
        <img src="../assets/images/logos/1.png" height="120" alt="" />
      </a>
      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-8"></i>
      </div>
    </div>
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
          <span class="hide-menu">Home</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="dashboard.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:home-2-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>
      </ul>
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
          <span class="hide-menu">Cases</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="case_assigned.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:case-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">My Cases</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="case_view.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:documents-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">All Cases</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="case_add.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:document-add-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">Add New Case</span>
          </a>
        </li>
      </ul>
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
          <span class="hide-menu">Client</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="client_view.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">List of Client</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="client_add.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:user-plus-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">Add Client</span>
          </a>
        </li>
      </ul>
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
          <span class="hide-menu">Appointments</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="my_appointments.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:calendar-mark-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">My Appointments</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="set_appointment.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:calendar-add-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">Set Appointment</span>
          </a>
        </li>
      </ul>

      <?php
      $admin_query = "SELECT role FROM users WHERE id = ? AND role = 'staff'";
      $admin_stmt = $conn->prepare($admin_query);
      $admin_stmt->bind_param("i", $_SESSION['user_id']);
      $admin_stmt->execute();
      $admin_result = $admin_stmt->get_result();
      $is_admin = ($admin_result->num_rows > 0);

      if ($is_admin): 
      ?>
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
          <span class="hide-menu">Administration</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="manage_lawyers.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">Manage Lawyers</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="payment_invoices.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:bill-list-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">Payments & Invoices</span>
          </a>
        </li>
      </ul>
      <?php endif; ?>
    </nav>
  </div>
</aside>