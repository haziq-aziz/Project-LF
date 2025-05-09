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

// Get next invoice number
$invoice_query = "SELECT MAX(SUBSTRING_INDEX(invoice_number, '-', -1)) as last_num FROM invoices WHERE invoice_number LIKE 'INV-%'";
$invoice_result = $conn->query($invoice_query);
$last_num = 0;

if ($invoice_result && $invoice_result->num_rows > 0) {
  $row = $invoice_result->fetch_assoc();
  $last_num = intval($row['last_num']);
}

$next_num = $last_num + 1;
$next_invoice_number = 'INV-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $errors = [];
  
  // Validate required fields
  $required_fields = ['invoice_number', 'issue_date', 'due_date', 'total_amount'];
  foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
      $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
    }
  }
  
  // Validate amounts
  if (!empty($_POST['total_amount']) && !is_numeric($_POST['total_amount'])) {
    $errors[] = "Total amount must be a number";
  }
  
  // Process if no errors
  if (empty($errors)) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
      // Prepare invoice data
      $invoice_number = mysqli_real_escape_string($conn, $_POST['invoice_number']);
      $client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;
      $case_id = !empty($_POST['case_id']) ? intval($_POST['case_id']) : null;
      $issue_date = mysqli_real_escape_string($conn, $_POST['issue_date']);
      $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
      $amount = floatval($_POST['amount'] ?? 0);
      $tax_amount = floatval($_POST['tax_amount'] ?? 0);
      $discount_amount = floatval($_POST['discount_amount'] ?? 0);
      $total_amount = floatval($_POST['total_amount']);
      $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
      $status = 'Unpaid';
      $created_by = $user_id;
      
      // Insert invoice
      $query = "INSERT INTO invoices (invoice_number, client_id, case_id, amount, tax_amount, 
                discount_amount, total_amount, status, issue_date, due_date, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      
      $stmt = $conn->prepare($query);
      
      // Handle NULL values properly
      if ($client_id === null) {
        $client_id = NULL;
      }
      if ($case_id === null) {
        $case_id = NULL;
      }
      
      $stmt->bind_param("ssiidddssssi", $invoice_number, $client_id, $case_id, $amount, $tax_amount, 
                      $discount_amount, $total_amount, $status, $issue_date, $due_date, $notes, $created_by);
      
      if (!$stmt->execute()) {
        throw new Exception("Error creating invoice: " . $conn->error);
      }
      
      $invoice_id = $conn->insert_id;
      
      // Insert invoice items if provided
      if (isset($_POST['item_description']) && is_array($_POST['item_description'])) {
        $item_count = count($_POST['item_description']);
        
        $insert_item_query = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)";
        $item_stmt = $conn->prepare($insert_item_query);
        
        for ($i = 0; $i < $item_count; $i++) {
          if (!empty($_POST['item_description'][$i])) {
            $item_description = mysqli_real_escape_string($conn, $_POST['item_description'][$i]);
            $quantity = floatval($_POST['item_quantity'][$i] ?? 1);
            $unit_price = floatval($_POST['item_price'][$i] ?? 0);
            $item_total = floatval($_POST['item_total'][$i] ?? ($quantity * $unit_price));
            
            $item_stmt->bind_param("isddd", $invoice_id, $item_description, $quantity, $unit_price, $item_total);
            
            if (!$item_stmt->execute()) {
              throw new Exception("Error adding invoice item: " . $conn->error);
            }
          }
        }
      }
      
      // Commit transaction
      $conn->commit();
      
      $_SESSION['success'] = "Invoice created successfully";
      header("Location: payment_invoices.php");
      exit();
      
    } catch (Exception $e) {
      // Rollback transaction on error
      $conn->rollback();
      $errors[] = $e->getMessage();
    }
  }
}

// Get clients for dropdown
$clients_query = "SELECT id, name, email FROM clients ORDER BY name";
$clients_result = $conn->query($clients_query);

// Get cases for dropdown
$cases_query = "SELECT c.id, c.case_no, c.case_type, cl.name as client_name, c.client_id 
                FROM cases c 
                LEFT JOIN clients cl ON c.client_id = cl.id
                ORDER BY c.case_no";
$cases_result = $conn->query($cases_query);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Create Invoice</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    .invoice-item-row {
      margin-bottom: 10px;
      padding: 10px;
      border-bottom: 1px solid #eee;
    }
    .remove-item-btn {
      margin-top: 32px;
    }
    #invoice-items-container {
      margin-bottom: 20px;
    }
    .total-section {
      background-color: #f9f9f9;
      padding: 15px;
      border-radius: 5px;
    }
  </style>
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
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <!-- Invoice Form Card -->
        <div class="card">
          <div class="card-body">
            <h3 class="card-title">Create New Invoice</h3>
            
            <form method="POST" action="" id="invoice-form" class="mt-4">
              <div class="row g-3">
                <!-- Invoice Header Section -->
                <div class="col-md-4">
                  <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="<?= htmlspecialchars($_POST['invoice_number'] ?? $next_invoice_number) ?>" required>
                </div>
                
                <div class="col-md-4">
                  <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control datepicker" id="issue_date" name="issue_date" value="<?= $_POST['issue_date'] ?? date('Y-m-d') ?>" required>
                </div>
                
                <div class="col-md-4">
                  <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control datepicker" id="due_date" name="due_date" value="<?= $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>" required>
                </div>
                
                <!-- Client and Case Selection -->
                <div class="col-md-6">
                  <label for="client_id" class="form-label">Client</label>
                  <select class="form-select" id="client_id" name="client_id">
                    <option value="">-- Select Client (Optional) --</option>
                    <?php if ($clients_result && $clients_result->num_rows > 0): ?>
                      <?php while ($client = $clients_result->fetch_assoc()): ?>
                        <option value="<?= $client['id'] ?>" <?= (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($client['name']) ?> (<?= htmlspecialchars($client['email']) ?>)
                        </option>
                      <?php endwhile; ?>
                    <?php endif; ?>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="case_id" class="form-label">Related Case</label>
                  <select class="form-select" id="case_id" name="case_id">
                    <option value="">-- Select Case (Optional) --</option>
                    <?php if ($cases_result && $cases_result->num_rows > 0): ?>
                      <?php 
                      // Reset pointer to beginning
                      $cases_result->data_seek(0);
                      while ($case = $cases_result->fetch_assoc()): 
                      ?>
                        <option value="<?= $case['id'] ?>" data-client-id="<?= $case['client_id'] ?>" <?= (isset($_POST['case_id']) && $_POST['case_id'] == $case['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($case['case_no']) ?> - <?= htmlspecialchars($case['case_type']) ?>
                          <?= !empty($case['client_name']) ? ' (' . htmlspecialchars($case['client_name']) . ')' : '' ?>
                        </option>
                      <?php endwhile; ?>
                    <?php endif; ?>
                  </select>
                </div>
                
                <!-- Notes -->
                <div class="col-md-12">
                  <label for="notes" class="form-label">Notes</label>
                  <textarea class="form-control" id="notes" name="notes" rows="2"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                </div>
                
                <!-- Invoice Items Section -->
                <div class="col-12 mt-4">
                  <h4 class="mb-3">Invoice Items</h4>
                  
                  <div id="invoice-items-container">
                    <!-- Invoice items will be added here -->
                    <div class="row invoice-item-row">
                      <div class="col-md-5">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control item-description" name="item_description[]" required>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control item-quantity" name="item_quantity[]" value="1" min="0.01" step="0.01" required>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label">Unit Price (RM)</label>
                        <input type="number" class="form-control item-price" name="item_price[]" value="0.00" min="0" step="0.01" required>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label">Total (RM)</label>
                        <input type="number" class="form-control item-total" name="item_total[]" value="0.00" readonly>
                      </div>
                      <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-item-btn">
                          <i class="fa fa-times"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="button" class="btn btn-secondary" id="add-item-btn">
                      <i class="fa fa-plus me-2"></i>Add Item
                    </button>
                  </div>
                </div>
                
                <!-- Total Calculation Section -->
                <div class="col-md-6">
                  <!-- Empty space for alignment -->
                </div>
                <div class="col-md-6 total-section">
                  <div class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label">Subtotal Amount (RM)</label>
                    </div>
                    <div class="col-md-6">
                      <input type="number" class="form-control text-end" id="amount" name="amount" value="0.00" readonly>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Tax Amount (RM)</label>
                    </div>
                    <div class="col-md-6">
                      <input type="number" class="form-control text-end" id="tax_amount" name="tax_amount" value="0.00" min="0" step="0.01">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Discount Amount (RM)</label>
                    </div>
                    <div class="col-md-6">
                      <input type="number" class="form-control text-end" id="discount_amount" name="discount_amount" value="0.00" min="0" step="0.01">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Total Amount (RM)</label>
                    </div>
                    <div class="col-md-6">
                      <input type="number" class="form-control text-end fw-bold" id="total_amount" name="total_amount" value="0.00" min="0.01" step="0.01" required>
                    </div>
                  </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="col-12 mt-4">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-2"></i>Create Invoice
                  </button>
                  <a href="payment_invoices.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
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
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  
  <script>
    $(document).ready(function() {
      // Initialize date pickers
      $(".datepicker").flatpickr({
        dateFormat: "Y-m-d"
      });
      
      // Auto-select client when case is selected
      $("#case_id").change(function() {
        const clientId = $(this).find(':selected').data('client-id');
        if (clientId) {
          $("#client_id").val(clientId);
        }
      });
      
      // Add invoice item
      $("#add-item-btn").click(function() {
        const newItem = `
          <div class="row invoice-item-row">
            <div class="col-md-5">
              <label class="form-label">Description</label>
              <input type="text" class="form-control item-description" name="item_description[]" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Quantity</label>
              <input type="number" class="form-control item-quantity" name="item_quantity[]" value="1" min="0.01" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Unit Price (RM)</label>
              <input type="number" class="form-control item-price" name="item_price[]" value="0.00" min="0" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Total (RM)</label>
              <input type="number" class="form-control item-total" name="item_total[]" value="0.00" readonly>
            </div>
            <div class="col-md-1">
              <button type="button" class="btn btn-danger remove-item-btn">
                <i class="fa fa-times"></i>
              </button>
            </div>
          </div>
        `;
        
        $("#invoice-items-container").append(newItem);
        
        // Add event listeners to the new row
        addItemEventListeners($("#invoice-items-container .invoice-item-row:last-child"));
      });
      
      // Add event listeners to initial row
      addItemEventListeners($("#invoice-items-container .invoice-item-row:first-child"));
      
      // Remove invoice item
      $(document).on("click", ".remove-item-btn", function() {
        // Don't remove if it's the only item
        if ($("#invoice-items-container .invoice-item-row").length > 1) {
          $(this).closest(".invoice-item-row").remove();
          calculateTotals();
        } else {
          alert("You need at least one invoice item");
        }
      });
      
      // Calculate item total when quantity or price changes
      function addItemEventListeners(row) {
        // Calculate item total when quantity or price changes
        row.find(".item-quantity, .item-price").on("input", function() {
          const row = $(this).closest(".invoice-item-row");
          calculateItemTotal(row);
          calculateTotals();
        });
      }
      
      // Calculate single item total
      function calculateItemTotal(row) {
        const quantity = parseFloat(row.find(".item-quantity").val()) || 0;
        const price = parseFloat(row.find(".item-price").val()) || 0;
        const total = quantity * price;
        row.find(".item-total").val(total.toFixed(2));
      }
      
      // Calculate all totals
      function calculateTotals() {
        let subtotal = 0;
        
        // Sum up all item totals
        $(".item-total").each(function() {
          subtotal += parseFloat($(this).val()) || 0;
        });
        
        const taxAmount = parseFloat($("#tax_amount").val()) || 0;
        const discountAmount = parseFloat($("#discount_amount").val()) || 0;
        
        // Calculate total
        const total = subtotal + taxAmount - discountAmount;
        
        // Update the fields
        $("#amount").val(subtotal.toFixed(2));
        $("#total_amount").val(total.toFixed(2));
      }
      
      // When tax or discount changes, recalculate totals
      $("#tax_amount, #discount_amount").on("input", calculateTotals);
      
      // Initialize calculations
      calculateTotals();
    });
  </script>
</body>

</html>