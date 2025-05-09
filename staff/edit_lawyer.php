<?php 
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "You don't have permission to access this page";
  header('Location: dashboard.php');
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['error'] = "Invalid lawyer ID";
  header("Location: manage_lawyers.php");
  exit();
}

$lawyer_id = intval($_GET['id']);

$query = "SELECT * FROM users WHERE id = ? AND role = 'staff'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $lawyer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['error'] = "Lawyer not found";
  header("Location: manage_lawyers.php");
  exit();
}

$lawyer = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $required_fields = ['name', 'username', 'email'];
  $errors = [];
  
  foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
      $errors[] = ucfirst($field) . ' is required';
    }
  }
  
  if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
  }
  
  if (!empty($_POST['username']) && !preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username'])) {
    $errors[] = 'Username can only contain letters, numbers and underscores';
  }
  
  if (!empty($_POST['username'])) {
    $username_check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $username_check->bind_param("si", $_POST['username'], $lawyer_id);
    $username_check->execute();
    $username_result = $username_check->get_result();
    
    if ($username_result->num_rows > 0) {
      $errors[] = 'Username already exists';
    }
  }
  
  if (!empty($_POST['email'])) {
    $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check->bind_param("si", $_POST['email'], $lawyer_id);
    $email_check->execute();
    $email_result = $email_check->get_result();
    
    if ($email_result->num_rows > 0) {
      $errors[] = 'Email already exists';
    }
  }
  
  if (!empty($_POST['password'])) {
    if (strlen($_POST['password']) < 6) {
      $errors[] = 'Password must be at least 6 characters long';
    }
  }
  
  if (empty($errors)) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
    $address = !empty($_POST['address']) ? $_POST['address'] : null;
    
    if (!empty($_POST['password'])) {
      $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
      $update_query = "UPDATE users SET name = ?, username = ?, email = ?, password = ?, phone = ?, address = ? WHERE id = ?";
      $update_stmt = $conn->prepare($update_query);
      $update_stmt->bind_param("ssssssi", $name, $username, $email, $password, $phone, $address, $lawyer_id);
    } else {
      $update_query = "UPDATE users SET name = ?, username = ?, email = ?, phone = ?, address = ? WHERE id = ?";
      $update_stmt = $conn->prepare($update_query);
      $update_stmt->bind_param("sssssi", $name, $username, $email, $phone, $address, $lawyer_id);
    }
    
    if ($update_stmt->execute()) {
      $_SESSION['success'] = "Lawyer updated successfully";
      header('Location: manage_lawyers.php');
      exit();
    } else {
      $errors[] = "Error updating lawyer: " . $conn->error;
    }
  }
} else {
  $_POST = $lawyer;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Edit Lawyer</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title">Edit Lawyer</h3>
                
                <?php if (!empty($errors)): ?>
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="mt-4">
                  <div class="row g-3">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                      <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                      <small class="text-muted">Only letters, numbers, and underscores allowed</small>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="phone" class="form-label">Phone Number</label>
                      <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    
                    <!-- Password Fields -->
                    <div class="col-md-6">
                      <label for="password" class="form-label">New Password</label>
                      <input type="password" class="form-control" id="password" name="password">
                      <small class="text-muted">Leave blank to keep current password</small>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="confirm_password" class="form-label">Confirm Password</label>
                      <input type="password" class="form-control" id="confirm_password">
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="col-md-12">
                      <label for="address" class="form-label">Address</label>
                      <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="col-12 mt-4">
                      <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i> Update Lawyer
                      </button>
                      <a href="manage_lawyers.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                  </div>
                </form>
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
  
  <script>
    $(document).ready(function() {
      $('#password, #confirm_password').on('keyup', function() {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (password === '' && confirmPassword === '') {
          $('#confirm_password').removeClass('is-invalid').removeClass('is-valid');
          return;
        }
        
        if (password !== confirmPassword) {
          $('#confirm_password').addClass('is-invalid').removeClass('is-valid');
        } else {
          $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
        }
      });
      
      $('form').on('submit', function(e) {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (password !== '' && password !== confirmPassword) {
          e.preventDefault();
          alert('Passwords do not match');
          $('#confirm_password').focus();
        }
      });
    });
  </script>
</body>

</html>