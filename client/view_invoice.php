<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$invoice_id = isset($_GET['invoice']) ? intval($_GET['invoice']) : 0;

// Fetch invoice header
$sql = "SELECT * FROM invoices WHERE id = ? AND client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $invoice_id, $user_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    echo '<div class="alert alert-danger m-5">Invoice not found or access denied.</div>';
    exit();
}

// Handle payment for the whole invoice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    // Normalize payment method value
    if ($payment_method === 'card') {
        $payment_method_db = 'card';
    } elseif ($payment_method === 'online_banking') {
        $payment_method_db = 'online';
    } else {
        $payment_method_db = $payment_method;
    }
    // Mark invoice as paid and set payment_method
    $sql = "UPDATE invoices SET status = 'Paid', payment_date = CURDATE(), payment_method = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $payment_method_db, $invoice_id);
    $stmt->execute();
    $stmt->close();
    // Add to payment history
    $sql = "INSERT INTO payment_history (invoice_id, client_id, amount, payment_method, payment_date) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $invoice_id, $user_id, $invoice['total_amount'], $payment_method_db);
    $stmt->execute();
    $stmt->close();
    // Redirect to invoice.php with a success message
    header("Location: invoice.php?msg=Payment successful");
    exit();
}

// Fetch invoice items with status
$sql = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?> - Nabihah Ishak & CO.</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
.square-pay-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 150px;
  height: 90px;
  border: 2px solid;
  border-radius: 12px;
  background: #fff;
  cursor: pointer;
  padding: 0px 5px;
  text-align: center;
  transition: border-color 0.2s, box-shadow 0.2s;
  font-size: 0.95rem;
}
.square-pay-btn.selected,
.square-pay-btn:focus,
.square-pay-btn:hover {
  border-color: #0d6efd;
  box-shadow: 0 0 0 2px #0d6efd33;
  background: #0d6efd;
  color: #fff;
}
.square-pay-btn .fa {
  font-size: 1.7rem;
  margin-bottom: 0.3rem;
}
.square-pay-btn-success.selected,
.square-pay-btn-success:focus,
.square-pay-btn-success:hover {
  border-color: #198754;
  box-shadow: 0 0 0 2px #19875433;
  background: #198754;
  color: #fff;
}
  </style>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
  <?php include '../includes/client/sidebar.php'; ?>
  <div class="body-wrapper">
    <?php include '../includes/client/navbar.php'; ?>
    <div class="container-fluid py-4">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
              <h4 class="mb-0">Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></h4>
              <span class="badge <?= $invoice['status']==='Paid' ? 'bg-success' : ($invoice['status']==='Unpaid' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                <?= htmlspecialchars($invoice['status']) ?>
              </span>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <strong>Issued On:</strong> <?= htmlspecialchars($invoice['issue_date']) ?><br>
                <strong>Due Date:</strong> <?= htmlspecialchars($invoice['due_date']) ?><br>
                <?php if ($invoice['case_id']): ?>
                  <strong>Related Case:</strong> <?= htmlspecialchars($invoice['case_id']) ?><br>
                <?php endif; ?>
                <?php if (!empty($invoice['notes'])): ?>
                  <strong>Notes:</strong> <?= nl2br(htmlspecialchars($invoice['notes'])) ?><br>
                <?php endif; ?>
              </div>
              <div class="table-responsive mb-4">
                <table class="table table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>Description</th>
                      <th class="text-end">Qty</th>
                      <th class="text-end">Unit Price (RM)</th>
                      <th class="text-end">Total (RM)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($items)): ?>
                      <tr><td colspan="4" class="text-center">No items found.</td></tr>
                    <?php else: foreach ($items as $item): ?>
                      <tr>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td class="text-end"><?= number_format($item['quantity'], 2) ?></td>
                        <td class="text-end"><?= number_format($item['unit_price'], 2) ?></td>
                        <td class="text-end"><?= number_format($item['total'], 2) ?></td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="3" class="text-end">Subtotal</th>
                      <th class="text-end">RM<?= number_format($invoice['amount'], 2) ?></th>
                      <th></th>
                    </tr>
                    <tr>
                      <th colspan="3" class="text-end">Tax</th>
                      <th class="text-end">RM<?= number_format($invoice['tax_amount'], 2) ?></th>
                      <th></th>
                    </tr>
                    <tr>
                      <th colspan="3" class="text-end">Discount</th>
                      <th class="text-end">RM<?= number_format($invoice['discount_amount'], 2) ?></th>
                      <th></th>
                    </tr>
                    <tr class="table-primary">
                      <th colspan="3" class="text-end">Total</th>
                      <th class="text-end">RM<?= number_format($invoice['total_amount'], 2) ?></th>
                      <th></th>
                    </tr>
                  </tfoot>
                </table>
                <?php if ($invoice['status'] === 'Paid'): ?>
                  <div class="alert alert-success text-center fw-bold mb-4">This invoice is already paid.</div>
                  <div class="text-center mb-4">
                    <strong>Payment Date:</strong> <?= htmlspecialchars($invoice['payment_date']) ?>
                  </div>
                <?php else: ?>
                  <form method="post" class="mt-4">
                    <div class="mb-3 text-center">
                      <label class="form-label fw-bold w-100">Select Payment Method:</label>
                      <div class="d-flex flex-wrap gap-3 justify-content-center mb-3">
                        <div>
                          <input type="radio" class="d-none" name="payment_method" id="card" value="card" required>
                          <label class="square-pay-btn" for="card" tabindex="0">
                            <i class="fa fa-credit-card"></i>
                            <span style="font-size:0.95rem;">Credit/Debit Card</span>
                          </label>
                        </div>
                        <div>
                          <input type="radio" class="d-none" name="payment_method" id="banking" value="online_banking" required>
                          <label class="square-pay-btn square-pay-btn-success" for="banking" tabindex="0">
                            <i class="fa fa-university"></i>
                            <span style="font-size:0.95rem;">Online Banking</span>
                          </label>
                        </div>
                      </div>
                      <div class="row mt-3" style="margin-left: 0px; margin-right: 0px;">
                        <div class="col-6 text-start">
                          <a href="invoice.php" class="btn btn-secondary">Back to Bills</a>
                        </div>
                        <div class="col-6 text-end">
                          <button type="submit" class="btn btn-primary">Pay Now</button>
                        </div>
                      </div>
                    </div>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include '../includes/footer.php'; ?>
  </div>
</div>
<script src="../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sidebarmenu.js"></script>
<script src="../assets/js/app.min.js"></script>
<script>
// Toggle selected style for square buttons
const radios = document.querySelectorAll('input[name="payment_method"]');
radios.forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.square-pay-btn').forEach(btn => btn.classList.remove('selected'));
    if (this.checked) {
      document.querySelector('label[for="' + this.id + '"]').classList.add('selected');
    }
  });
});
</script>
</body>
</html>
