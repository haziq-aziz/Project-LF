<?php 
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

// Get current user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';

// Fetch bills for this client
$bills = [];
$sql = "SELECT id, invoice_number, notes, total_amount, amount, tax_amount, discount_amount, status, due_date, issue_date, created_at FROM invoices WHERE client_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bills[] = $row;
}
$stmt->close();

// Fetch payment history for this client
$payment_history = [];
$sql = "SELECT ph.*, i.invoice_number FROM payment_history ph JOIN invoices i ON ph.invoice_id = i.id WHERE ph.client_id = ? ORDER BY ph.payment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $payment_history[] = $row;
}
$stmt->close();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - My Bills</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Include Sidebar -->
    <?php include '../includes/client/sidebar.php'; ?>
    
    <!--  Main wrapper -->
    <div class="body-wrapper">
      
      <!-- Include Navbar -->
      <?php include '../includes/client/navbar.php'; ?>
      
      <!-- Main Content -->
      <div class="container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary mb-0">My Bills</h3>
            </div>
            
            <?php if (isset($_GET['msg']) && !empty($_GET['msg'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>
            
            <!-- Payment History Section -->
            <div class="card">
              <div class="card-body">
                <h5 class="mb-3 text-primary">Payment History</h5>
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr class="bg-light">
                        <th>Invoice No</th>
                        <th>Amount (RM)</th>
                        <th>Payment Method</th>
                        <th>Payment Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($payment_history)): ?>
                        <tr><td colspan="4" class="text-center">No payment history found.</td></tr>
                      <?php else: foreach ($payment_history as $ph): ?>
                        <tr>
                          <td><?= htmlspecialchars($ph['invoice_number']) ?></td>
                          <td><?= number_format($ph['amount'], 2) ?></td>
                          <td><?= htmlspecialchars(ucfirst($ph['payment_method'])) ?></td>
                          <td><?= htmlspecialchars($ph['payment_date']) ?></td>
                        </tr>
                      <?php endforeach; endif; ?>
                    </tbody>
                  </table>
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