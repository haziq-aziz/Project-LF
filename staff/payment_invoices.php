<?php 
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

// Check if user is staff (has admin privileges)
$admin_query = "SELECT role FROM users WHERE id = ? AND role = 'staff'";
$admin_stmt = $conn->prepare($admin_query);
$admin_stmt->bind_param("i", $_SESSION['user_id']);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();

if ($admin_result->num_rows === 0) {
  $_SESSION['error'] = "You don't have permission to access this page";
  header('Location: dashboard.php');
  exit();
}

// Get current user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';

// Handle invoice status updates
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
  $invoice_id = intval($_GET['id']);
  $new_status = $_GET['status'];
  
  // Validate status
  $allowed_statuses = ['Paid', 'Unpaid', 'Partial', 'Cancelled'];
  if (in_array($new_status, $allowed_statuses)) {
    // Update payment date if marked as paid
    if ($new_status == 'Paid') {
      $update_query = "UPDATE invoices SET status = ?, payment_date = CURDATE() WHERE id = ?";
    } else {
      $update_query = "UPDATE invoices SET status = ? WHERE id = ?";
    }
    
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_status, $invoice_id);
    
    if ($update_stmt->execute()) {
      $_SESSION['success'] = "Invoice #" . $invoice_id . " status updated to " . $new_status;
    } else {
      $_SESSION['error'] = "Failed to update invoice status";
    }
  } else {
    $_SESSION['error'] = "Invalid status value";
  }
  
  header('Location: payment_invoices.php');
  exit();
}

// Handle invoice deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  $invoice_id = intval($_GET['id']);
  
  // Delete invoice
  $delete_query = "DELETE FROM invoices WHERE id = ?";
  $delete_stmt = $conn->prepare($delete_query);
  $delete_stmt->bind_param("i", $invoice_id);
  
  if ($delete_stmt->execute()) {
    $_SESSION['success'] = "Invoice #" . $invoice_id . " has been deleted";
  } else {
    $_SESSION['error'] = "Failed to delete invoice";
  }
  
  header('Location: payment_invoices.php');
  exit();
}

// Get all invoices with client and case details
$invoices_query = "SELECT i.*, 
                  c.name AS client_name,
                  cs.case_no,
                  u.name AS created_by_name
                  FROM invoices i 
                  LEFT JOIN clients c ON i.client_id = c.id
                  LEFT JOIN cases cs ON i.case_id = cs.id
                  LEFT JOIN users u ON i.created_by = u.id
                  ORDER BY i.created_at DESC";
$invoices_result = $conn->query($invoices_query);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Payment & Invoices</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Include Sidebar -->
    <?php include '../includes/staff/sidebar.php'; ?>
    
    <!--  Main wrapper -->
    <div class="body-wrapper">
      
      <!-- Include Navbar -->
      <?php include '../includes/staff/navbar.php'; ?>
      
      <!-- Main Content -->
      <div class="container-fluid">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Page Title and Create Button -->
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h3 class="fs-4 fw-semibold">Payments & Invoices</h3>
          <a href="create_invoice.php" class="btn btn-primary">
            <i class="fa fa-plus-circle me-2"></i>Create New Invoice
          </a>
        </div>
        
        <!-- Invoice Summary Cards -->
        <div class="row mb-4">
          <?php
          // Get summary statistics
          $total_query = "SELECT 
                          COUNT(*) as total_invoices,
                          SUM(total_amount) as total_amount,
                          SUM(CASE WHEN status = 'Paid' THEN total_amount ELSE 0 END) as paid_amount,
                          SUM(CASE WHEN status = 'Unpaid' OR status = 'Partial' THEN total_amount ELSE 0 END) as unpaid_amount,
                          COUNT(CASE WHEN status = 'Unpaid' THEN 1 END) as unpaid_count
                          FROM invoices";
          $total_result = $conn->query($total_query);
          $summary = $total_result->fetch_assoc();
          ?>
          
          <div class="col-md-3">
            <div class="card invoice-summary-card">
              <div class="card-body">
                <h5 class="card-title">Total Invoices</h5>
                <h3 class="mb-0"><?= number_format($summary['total_invoices']) ?></h3>
              </div>
            </div>
          </div>
          
          <div class="col-md-3">
            <div class="card invoice-summary-card" style="border-left-color: #198754;">
              <div class="card-body">
                <h5 class="card-title">Total Amount</h5>
                <h3 class="mb-0">RM <?= number_format($summary['total_amount'] ?? 0, 2) ?></h3>
              </div>
            </div>
          </div>
          
          <div class="col-md-3">
            <div class="card invoice-summary-card" style="border-left-color: #dc3545;">
              <div class="card-body">
                <h5 class="card-title">Unpaid</h5>
                <h3 class="mb-0">RM <?= number_format($summary['unpaid_amount'] ?? 0, 2) ?></h3>
                <small class="text-muted"><?= number_format($summary['unpaid_count'] ?? 0) ?> invoices</small>
              </div>
            </div>
          </div>
          
          <div class="col-md-3">
            <div class="card invoice-summary-card" style="border-left-color: #20c997;">
              <div class="card-body">
                <h5 class="card-title">Collected</h5>
                <h3 class="mb-0">RM <?= number_format($summary['paid_amount'] ?? 0, 2) ?></h3>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Invoices List -->
        <div class="card">
          <div class="card-body">
            <h4 class="card-title mb-4">All Invoices</h4>
            
            <div class="table-responsive">
              <table id="invoices-table" class="table table-striped table-hover align-middle">
                <thead>
                  <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Case Reference</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($invoices_result && $invoices_result->num_rows > 0): ?>
                    <?php while ($invoice = $invoices_result->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="fw-semibold text-primary">
                          <?= htmlspecialchars($invoice['invoice_number']) ?>
                        </a>
                      </td>
                      <td><?= htmlspecialchars($invoice['client_name'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($invoice['case_no'] ?? 'N/A') ?></td>
                      <td><?= date('d M Y', strtotime($invoice['issue_date'])) ?></td>
                      <td>
                        <?= date('d M Y', strtotime($invoice['due_date'])) ?>
                        <?php if ($invoice['status'] != 'Paid' && $invoice['status'] != 'Cancelled' && strtotime($invoice['due_date']) < time()): ?>
                          <span class="badge bg-danger">Overdue</span>
                        <?php endif; ?>
                      </td>
                      <td>RM <?= number_format($invoice['total_amount'], 2) ?></td>
                      <td>
                        <span class="badge status-badge bg-<?= 
                          $invoice['status'] == 'Paid' ? 'success' : 
                          ($invoice['status'] == 'Partial' ? 'warning' : 
                          ($invoice['status'] == 'Cancelled' ? 'secondary' : 'danger')) ?>">
                          <?= $invoice['status'] ?>
                        </span>
                      </td>
                      <td class="invoice-actions">
                        <div class="btn-group">
                          <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-primary" title="View">
                            <i class="fa fa-eye"></i>
                          </a>
                          
                          <button type="button" class="btn btn-sm btn-info" onclick="printInvoice(<?= $invoice['id'] ?>)" title="Print">
                            <i class="fa fa-print"></i>
                          </button>
                          
                          <?php if ($invoice['status'] != 'Paid' && $invoice['status'] != 'Cancelled'): ?>
                          <button type="button" class="btn btn-sm btn-success" onclick="updateStatus(<?= $invoice['id'] ?>, 'Paid')" title="Mark as Paid">
                            <i class="fa fa-check"></i>
                          </button>
                          <?php endif; ?>
                          
                          <?php if ($invoice['status'] == 'Unpaid'): ?>
                          <button type="button" class="btn btn-sm btn-warning" onclick="updateStatus(<?= $invoice['id'] ?>, 'Partial')" title="Mark as Partially Paid">
                            <i class="fa fa-percentage"></i>
                          </button>
                          <?php endif; ?>
                          
                          <?php if ($invoice['status'] != 'Cancelled'): ?>
                          <button type="button" class="btn btn-sm btn-secondary" onclick="updateStatus(<?= $invoice['id'] ?>, 'Cancelled')" title="Cancel Invoice">
                            <i class="fa fa-ban"></i>
                          </button>
                          <?php endif; ?>
                          
                          <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $invoice['id'] ?>, '<?= $invoice['invoice_number'] ?>')" title="Delete Invoice">
                            <i class="fa fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center">No invoices found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
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
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  
  <script>
    $(document).ready(function() {
      <?php if ($invoices_result && $invoices_result->num_rows > 0): ?>
        // Initialize DataTable only when we have data
        $('#invoices-table').DataTable({
          ordering: true,
          paging: true,
          searching: true,
          responsive: true,
          order: [[3, 'desc']], // Sort by issue date by default
          language: {
            emptyTable: "No invoices found"
          }
        });
      <?php endif; ?>
    });
    
    // Function to update invoice status
    function updateStatus(id, status) {
      if (confirm(`Are you sure you want to mark this invoice as ${status}?`)) {
        window.location.href = `payment_invoices.php?action=update_status&id=${id}&status=${status}`;
      }
    }
    
    // Function to confirm invoice deletion
    function confirmDelete(id, invoiceNumber) {
      if (confirm(`Are you sure you want to delete invoice #${invoiceNumber}? This action cannot be undone.`)) {
        window.location.href = `payment_invoices.php?action=delete&id=${id}`;
      }
    }
    
    // Function to print invoice
    function printInvoice(id) {
      window.open(`view_invoice.php?id=${id}&print=true`, '_blank');
    }
  </script>
</body>

</html>