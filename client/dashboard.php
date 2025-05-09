<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../auth/login.php');
    exit();
}
require_once('../includes/db_connection.php');

// Fetch cases for this client
$client_id = $_SESSION['user_id'];
$cases = [];
$stmt = $conn->prepare("SELECT case_no, case_type, case_stage, description, filing_date, court, judge_name, remarks FROM cases WHERE client_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cases[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Client Dashboard | Nabihah Ishak & CO.</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <style>
    .case-card {
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .case-card .card-body {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    .case-description {
      flex-grow: 1;
      min-height: 80px;
      overflow: hidden;
    }
    .case-details {
      margin-top: auto;
    }
    .case-action {
      margin-top: 1rem;
    }
    .img-container {
      height: 150px;
      overflow: hidden;
    }
  </style>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php include_once('../includes/client/sidebar.php'); ?>
    <div class="body-wrapper">
      <?php include_once('../includes/client/navbar.php'); ?>
      <div class="container-fluid">
        <div class="card mt-4">
          <div class="card-body">
            <h3 class="card-title fw-semibold mb-4">Welcome to Your Dashboard</h3>
            <p class="lead">Here you can view your cases, appointments, and manage your profile.</p>
            <h5 class="mt-4 mb-3">Your Case Status</h5>
            <?php if (count($cases) > 0): ?>
              <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($cases as $case): ?>
                  <div class="col">
                    <div class="card overflow-hidden hover-img case-card h-100 w-100">
                      <div class="position-relative img-container">
                        <img src="../assets/images/staff_dashboard/img_1.jpg" class="card-img-top" style="height: 100%; object-fit: cover;">
                        <span class="badge text-bg-light text-dark fs-2 lh-sm py-1 px-2 fw-semibold position-absolute bottom-0 end-0 m-2">
                          <?= htmlspecialchars($case['case_type']) ?>
                        </span>
                      </div>
                      <div class="card-body p-4">
                        <h5 class="text-primary fw-bold mb-1">
                          <?= htmlspecialchars($case['case_no']) ?>
                        </h5>
                        <div class="case-description">
                          <p class="text-muted mb-2">
                            <?= htmlspecialchars(substr($case['description'] ?? '', 0, 100)) . (strlen($case['description'] ?? '') > 100 ? '...' : '') ?>
                          </p>
                        </div>
                        <div class="case-details">
                          <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-info text-white">
                              <?= htmlspecialchars($case['case_stage'] ?? $case['case_stage']) ?>
                            </span>
                          </p>
                          <p class="mb-1"><strong>Filing Date:</strong> <?= htmlspecialchars($case['filing_date']) ?></p>
                          <p class="mb-1"><strong>Court:</strong> <?= htmlspecialchars($case['court']) ?></p>
                          <p class="mb-1"><strong>Judge:</strong> <?= htmlspecialchars($case['judge_name']) ?></p>
                        </div>
                        <div class="mt-auto">
                          <p class="mb-0"><strong>Remarks:</strong> <?= htmlspecialchars($case['remarks']) ?></p>
                          <a href="case_detail.php?case_no=<?= urlencode($case['case_no']) ?>" class="btn btn-outline-primary btn-sm mt-3 w-100">View Case Detail</a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="alert alert-info">You have no cases assigned yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php include_once('../includes/footer.php'); ?>
    </div>
  </div>
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
</body>
</html>
