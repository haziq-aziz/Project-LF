<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

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
          <a class="sidebar-link" href="client_case.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:case-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">My Cases</span>
          </a>
        </li>
      </ul>
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
          <span class="hide-menu">Payment</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="invoice.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:bill-list-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">My Bills</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="payment_history.php" aria-expanded="false">
            <span>
              <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-6"></iconify-icon>
            </span>
            <span class="hide-menu">Payment History</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>