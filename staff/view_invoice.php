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

// Check if invoice ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['error'] = "Invalid invoice ID";
  header('Location: payment_invoices.php');
  exit();
}

$invoice_id = intval($_GET['id']);

// Get invoice details with client and case information
$invoice_query = "SELECT i.*, 
                  c.name AS client_name, c.email AS client_email, c.phone AS client_phone, c.address AS client_address,
                  cs.case_no, cs.case_type, cs.description AS case_description,
                  u.name AS created_by_name
                  FROM invoices i
                  LEFT JOIN clients c ON i.client_id = c.id
                  LEFT JOIN cases cs ON i.case_id = cs.id
                  LEFT JOIN users u ON i.created_by = u.id
                  WHERE i.id = ?";

$invoice_stmt = $conn->prepare($invoice_query);
$invoice_stmt->bind_param("i", $invoice_id);
$invoice_stmt->execute();
$invoice_result = $invoice_stmt->get_result();

if ($invoice_result->num_rows === 0) {
  $_SESSION['error'] = "Invoice not found";
  header('Location: payment_invoices.php');
  exit();
}

$invoice = $invoice_result->fetch_assoc();

// Fix the query to remove the non-existent item_order column
$items_query = "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $invoice_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$invoice_items = [];

while ($item = $items_result->fetch_assoc()) {
  $invoice_items[] = $item;
}

// Check if we're in print mode
$print_mode = isset($_GET['print']) && $_GET['print'] === 'true';

// Set a specific page title for print view
$page_title = $print_mode ? 'Invoice #' . $invoice['invoice_number'] : 'Nabihah Ishak & CO. - Invoice #' . $invoice['invoice_number'];
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>
  
  <?php if (!$print_mode): ?>
  <!-- Regular view mode stylesheets -->
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <?php else: ?>
  <!-- Print mode: only include the minimal styling needed -->
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <?php endif; ?>
  
  <?php if ($print_mode): ?>
  <style>
    /* Additional print-specific styles */
    body {
      background-color: white;
      margin: 0;
      padding: 20px;
      font-size: 12pt;
    }
    
    /* Hide anything with no-print class */
    .no-print {
      display: none !important;
    }
    
    /* Override any dark themes or complex backgrounds */
    .card, .container, body, html {
      background-color: white;
      color: black;
    }
    
    /* Remove unnecessary spacing */
    .container {
      max-width: 100%;
      padding: 0;
      margin: 0;
    }
    
    /* Print optimizations */
    @page {
      size: auto;
      margin: 10mm; /* Adjust margins for printing */
    }
  </style>
  
  <script>
    window.onload = function() {
      window.print();
      // Optional: Add a delay before closing to ensure print dialog appears
      // setTimeout(function() { window.close(); }, 500);
    }
  </script>
  <?php endif; ?>
</head>

<body class="<?= $print_mode ? 'print-body' : '' ?>">
  <?php if (!$print_mode): ?>
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
        <!-- Action buttons -->
        <div class="d-flex align-items-center justify-content-between mb-4 no-print">
          <h3 class="fs-4 fw-semibold">Invoice Details</h3>
          <div class="btn-group">
            <a href="payment_invoices.php" class="btn btn-outline-secondary">
              <i class="fa fa-arrow-left me-2"></i>Back to Invoices
            </a>
            <a href="view_invoice.php?id=<?= $invoice_id ?>&print=true" target="_blank" class="btn btn-outline-primary">
              <i class="fa fa-print me-2"></i>Print Invoice
            </a>
            
            <!-- Additional action buttons -->
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-cog me-2"></i>Actions
              </button>
              <ul class="dropdown-menu">
                <?php if ($invoice['status'] != 'Paid' && $invoice['status'] != 'Cancelled'): ?>
                <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateStatus(<?= $invoice['id'] ?>, 'Paid')">
                  <i class="fa fa-check text-success me-2"></i>Mark as Paid
                </a></li>
                <?php endif; ?>
                
                <?php if ($invoice['status'] == 'Unpaid'): ?>
                <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateStatus(<?= $invoice['id'] ?>, 'Partial')">
                  <i class="fa fa-percentage text-warning me-2"></i>Mark as Partially Paid
                </a></li>
                <?php endif; ?>
                
                <?php if ($invoice['status'] != 'Cancelled'): ?>
                <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateStatus(<?= $invoice['id'] ?>, 'Cancelled')">
                  <i class="fa fa-ban text-secondary me-2"></i>Cancel Invoice
                </a></li>
                <?php endif; ?>
                
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="javascript:void(0)" onclick="confirmDelete(<?= $invoice['id'] ?>, '<?= $invoice['invoice_number'] ?>')">
                  <i class="fa fa-trash text-danger me-2"></i>Delete Invoice
                </a></li>
              </ul>
            </div>
          </div>
        </div>
  <?php endif; ?>
      
  <!-- Invoice Content -->
  <div class="<?= $print_mode ? 'container my-4' : 'card' ?>">
    <div class="<?= $print_mode ? '' : 'card-body' ?>">
      <!-- Invoice Header -->
      <div class="invoice-header row">
        <div class="col-md-6">
          <div class="d-flex align-items-center">
            <img src="../assets/images/logos/1.png" alt="Company Logo" class="company-logo me-3">
            <div>
              <h4 class="mb-0">Nabihah Ishak & CO.</h4>
              <p class="mb-0">123 Jalan Ampang, 50450 Kuala Lumpur</p>
              <p class="mb-0">Tel: +603-2142-5555</p>
              <p class="mb-0">Email: info@nabihahishak.com</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
          <span class="invoice-title">INVOICE</span>
          <br>
          <span class="invoice-id">#<?= htmlspecialchars($invoice['invoice_number']) ?></span>
          <div class="mt-2">
            <span class="invoice-status status-<?= $invoice['status'] ?>"><?= $invoice['status'] ?></span>
            <?php if ($invoice['status'] == 'Paid' && $invoice['payment_date']): ?>
              <br><small>Payment received on <?= date('d M Y', strtotime($invoice['payment_date'])) ?></small>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- Invoice and Client Details -->
      <div class="row mb-4">
        <div class="col-md-6">
          <h5>Bill To:</h5>
          <div class="client-details">
            <h6><?= htmlspecialchars($invoice['client_name']) ?></h6>
            <p class="mb-0"><?= nl2br(htmlspecialchars($invoice['client_address'] ?? '')) ?></p>
            <p class="mb-0">Email: <?= htmlspecialchars($invoice['client_email'] ?? '') ?></p>
            <p class="mb-0">Phone: <?= htmlspecialchars($invoice['client_phone'] ?? '') ?></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="invoice-details">
            <div class="row">
              <div class="col-6">Issue Date:</div>
              <div class="col-6 text-end"><?= date('d M Y', strtotime($invoice['issue_date'])) ?></div>
            </div>
            <div class="row">
              <div class="col-6">Due Date:</div>
              <div class="col-6 text-end">
                <?= date('d M Y', strtotime($invoice['due_date'])) ?>
                <?php if ($invoice['status'] != 'Paid' && $invoice['status'] != 'Cancelled' && strtotime($invoice['due_date']) < time()): ?>
                  <span class="badge bg-danger ms-1">Overdue</span>
                <?php endif; ?>
              </div>
            </div>
            
            <?php if ($invoice['case_no']): ?>
            <div class="row mt-2">
              <div class="col-6">Case Reference:</div>
              <div class="col-6 text-end"><?= htmlspecialchars($invoice['case_no']) ?></div>
            </div>
            <div class="row">
              <div class="col-6">Case Type:</div>
              <div class="col-6 text-end"><?= htmlspecialchars($invoice['case_type']) ?></div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- Invoice Items Table -->
      <div class="table-responsive mb-4">
        <table class="table table-bordered table-items">
          <thead>
            <tr>
              <th width="5%">#</th>
              <th width="45%">Description</th>
              <th width="15%" class="text-end">Quantity</th>
              <th width="15%" class="text-end">Unit Price (RM)</th>
              <th width="20%" class="text-end">Amount (RM)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($invoice_items) > 0): ?>
              <?php $counter = 1; foreach ($invoice_items as $item): ?>
                <tr>
                  <td><?= $counter++ ?></td>
                  <td><?= htmlspecialchars($item['description']) ?></td>
                  <td class="text-end"><?= number_format($item['quantity'], 2) ?></td>
                  <td class="text-end"><?= number_format($item['unit_price'], 2) ?></td>
                  <td class="text-end"><?= number_format($item['total'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center">No items found for this invoice</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Invoice Summary -->
      <div class="row">
        <div class="col-md-6">
          <?php if (!empty($invoice['notes'])): ?>
          <div class="invoice-notes">
            <h6>Notes:</h6>
            <p><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
          </div>
          <?php endif; ?>
          
          <div class="payment-instructions">
            <h6>Payment Instructions:</h6>
            <p>Please make payment to:</p>
            <p>
              <strong>Bank Name:</strong> Maybank<br>
              <strong>Account Name:</strong> Nabihah Ishak & CO.<br>
              <strong>Account Number:</strong> 1234 5678 9012<br>
              <strong>Reference:</strong> Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?>
            </p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="invoice-total">
            <div class="row mb-2">
              <div class="col-7">Subtotal:</div>
              <div class="col-5">RM <?= number_format($invoice['amount'], 2) ?></div>
            </div>
            
            <?php if ($invoice['tax_amount'] > 0): ?>
            <div class="row mb-2">
              <div class="col-7">Tax:</div>
              <div class="col-5">RM <?= number_format($invoice['tax_amount'], 2) ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($invoice['discount_amount'] > 0): ?>
            <div class="row mb-2">
              <div class="col-7">Discount:</div>
              <div class="col-5">- RM <?= number_format($invoice['discount_amount'], 2) ?></div>
            </div>
            <?php endif; ?>
            
            <div class="row mb-2">
              <div class="col-7">
                <h5>Total:</h5>
              </div>
              <div class="col-5">
                <h5>RM <?= number_format($invoice['total_amount'], 2) ?></h5>
              </div>
            </div>
            
            <?php if ($invoice['status'] == 'Partial' && $invoice['amount_paid'] > 0): ?>
            <div class="row mb-2">
              <div class="col-7">Amount Paid:</div>
              <div class="col-5">RM <?= number_format($invoice['amount_paid'], 2) ?></div>
            </div>
            
            <div class="row">
              <div class="col-7">
                <h6>Balance Due:</h6>
              </div>
              <div class="col-5">
                <h6>RM <?= number_format($invoice['total_amount'] - $invoice['amount_paid'], 2) ?></h6>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <?php if ($print_mode): ?>
      <div class="print-footer">
        <p>This invoice was generated by Nabihah Ishak & CO. system on <?= date('d M Y') ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
      
  <?php if (!$print_mode): ?>
      </div>
      <!-- Include Footer -->
      <?php include '../includes/footer.php'; ?>
    </div>
  </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  
  <script>
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
  </script>
  <?php endif; ?>
</body>

</html>