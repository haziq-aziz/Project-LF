<?php

session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../auth/login.php');
  exit();
}

// Check if client ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No client specified.";
    header('Location: client_view.php');
    exit();
}

$client_id = $_GET['id'];

// Fetch client details
$sql = "SELECT * FROM clients WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$client) {
    $_SESSION['error'] = "Client not found.";
    header('Location: client_view.php');
    exit();
}

// Fetch cases related to this client
$caseQuery = "SELECT * FROM cases WHERE client_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($caseQuery);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$cases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch invoices related to this client
$invoiceQuery = "SELECT id, invoice_number, notes, total_amount, amount, tax_amount, discount_amount, status, due_date, issue_date, created_at 
                FROM invoices 
                WHERE client_id = ? 
                ORDER BY created_at DESC";
$stmt = $conn->prepare($invoiceQuery);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Client Details | Nabihah Ishak & CO.</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Include Sidebar -->
    <?php include '../includes/staff/sidebar.php'; ?>
    
    <div class="body-wrapper">
      
      <!-- Include Navbar -->
      <?php include '../includes/staff/navbar.php'; ?>
      
      <!-- Main Content -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <div class="d-sm-flex d-block align-items-center justify-content-between mb-3">
                  <h5 class="card-title fw-semibold">Client Details</h5>
                  <div>
                    <a href="client_view.php" class="btn btn-sm btn-outline-secondary me-2">
                      <i class="fas fa-arrow-left"></i> Back to Clients
                    </a>
                    <a href="client_edit.php?id=<?= $client['id'] ?>" class="btn btn-sm btn-primary">
                      <i class="fas fa-edit"></i> Edit Client
                    </a>
                  </div>
                </div>
                
                <!-- Client Details Section -->
                <div class="row">
                  <div class="col-md-8">
                    <div class="card mb-4">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="mb-3">
                              <h6 class="fw-semibold mb-1">Client Name</h6>
                              <p><?= htmlspecialchars($client['name']) ?></p>
                            </div>
                            <div class="mb-3">
                              <h6 class="fw-semibold mb-1">Email Address</h6>
                              <p><?= htmlspecialchars($client['email']) ?></p>
                            </div>
                            <div class="mb-3">
                              <h6 class="fw-semibold mb-1">Phone Number</h6>
                              <p><?= htmlspecialchars($client['phone']) ?></p>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="mb-3">
                              <h6 class="fw-semibold mb-1">Country</h6>
                              <p><?= htmlspecialchars($client['country'] ?? 'Not specified') ?></p>
                            </div>
                            <div class="mb-3">
                              <h6 class="fw-semibold mb-1">State</h6>
                              <p><?= htmlspecialchars($client['state'] ?? 'Not specified') ?></p>
                            </div>
                            <div class="mb-3">
                              <h6 class="fw-semibold mb-1">City</h6>
                              <p><?= htmlspecialchars($client['city'] ?? 'Not specified') ?></p>
                            </div>
                          </div>
                        </div>
                        <div class="mb-3">
                          <h6 class="fw-semibold mb-1">Address</h6>
                          <p><?= nl2br(htmlspecialchars($client['address'] ?? 'Not specified')) ?></p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card mb-4">
                      <div class="card-body">
                        <h6 class="fw-semibold mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                          <a href="case_add.php?client_id=<?= $client['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-folder-plus me-1"></i> Create New Case
                          </a>
                          <a href="create_invoice.php?client_id=<?= $client['id'] ?>" class="btn btn-success">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Create Invoice
                          </a>
                          <a href="set_appointment.php?client_id=<?= $client['id'] ?>" class="btn btn-info text-white">
                            <i class="fas fa-calendar-plus me-1"></i> Schedule Appointment
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Client Cases Section -->
                <div class="card mb-4">
                  <div class="card-body">
                    <h5 class="card-title fw-semibold mb-3">Cases</h5>
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped">
                        <thead class="table-light">
                          <tr>
                            <th>Case No</th>
                            <th>Case Type</th>
                            <th>Filing Date</th>
                            <th>Stage</th>
                            <th>Priority</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($cases)): ?>
                          <tr>
                            <td colspan="6" class="text-center">No cases found for this client.</td>
                          </tr>
                          <?php else: foreach ($cases as $case): ?>
                          <tr>
                            <td><?= htmlspecialchars($case['case_no']) ?></td>
                            <td><?= htmlspecialchars($case['case_type']) ?></td>
                            <td><?= htmlspecialchars($case['filing_date']) ?></td>
                            <td>
                              <span class="badge <?= ($case['case_stage'] == 'Case Open') ? 'bg-info' : 
                                                 (($case['case_stage'] == 'Case Ongoing') ? 'bg-warning' : 'bg-success') ?>">
                                <?= htmlspecialchars($case['case_stage']) ?>
                              </span>
                            </td>
                            <td>
                              <span class="badge <?= ($case['case_priority'] == 'High') ? 'bg-danger' : 
                                                 (($case['case_priority'] == 'Medium') ? 'bg-warning' : 'bg-success') ?>">
                                <?= htmlspecialchars($case['case_priority']) ?>
                              </span>
                            </td>
                            <td>
                              <a href="case_detail.php?case_no=<?= $case['case_no'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                              </a>
                            </td>
                          </tr>
                          <?php endforeach; endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                
                <!-- Client Invoices Section -->
                <div class="card mb-4">
                  <div class="card-body">
                    <h5 class="card-title fw-semibold mb-3">Invoices</h5>
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped">                        <thead class="table-light">
                          <tr>
                            <th>Invoice #</th>
                            <th>Description</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($invoices)): ?>
                          <tr>
                            <td colspan="6" class="text-center">No invoices found for this client.</td>
                          </tr>
                          <?php else: foreach ($invoices as $invoice): ?>
                          <tr>
                            <td><?= htmlspecialchars($invoice['invoice_number'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($invoice['notes'] ?? 'No description') ?></td>
                            <td><?= htmlspecialchars($invoice['due_date'] ?? 'Not set') ?></td>
                            <td>RM <?= number_format($invoice['total_amount'] ?? 0, 2) ?></td>
                            <td>
                              <span class="badge <?= ($invoice['status'] == 'Paid') ? 'bg-success' : 
                                                 (($invoice['status'] == 'Pending' || $invoice['status'] == 'pending') ? 'bg-warning' : 'bg-danger') ?>">
                                <?= htmlspecialchars($invoice['status'] ?? 'Unknown') ?>
                              </span>
                            </td>
                            <td>
                              <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                              </a>
                            </td>
                          </tr>
                          <?php endforeach; endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Include Footer -->
      <?php include '../includes/footer.php'; ?>
    </div>
  </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
</body>

</html>