<?php 
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

$admin_query = "SELECT is_admin FROM users WHERE id = ? LIMIT 1";
$admin_stmt = $conn->prepare($admin_query);
$admin_stmt->bind_param("i", $_SESSION['user_id']);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$is_admin = false;

if ($admin_result->num_rows > 0) {
  $admin_data = $admin_result->fetch_assoc();
  $is_admin = $admin_data['is_admin'] == 1;
}

if (!$is_admin) {
  $_SESSION['error'] = "You don't have permission to access this page";
  header("Location: dashboard.php");
  exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['name', 'username', 'email', 'password'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email format is invalid";
    }
    
    if (!empty($_POST['username'])) {
        $check_query = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $_POST['username']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Username already exists";
        }
    }
    
    if (!empty($_POST['email'])) {
        $check_query = "SELECT id FROM users WHERE email = ?";
        $check_stmt->bind_param("s", $_POST['email']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    if (empty($errors)) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? null;
        $address = $_POST['address'] ?? null;
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $is_admin_new = isset($_POST['is_admin']) ? 1 : 0;
        
        $insert_query = "INSERT INTO users (name, username, email, phone, address, password, role, is_admin) 
                         VALUES (?, ?, ?, ?, ?, ?, 'staff', ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssssssi", $name, $username, $email, $phone, $address, $password, $is_admin_new);
        
        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "Lawyer added successfully";
            header("Location: manage_lawyers.php");
            exit();
        } else {
            $errors[] = "Error adding lawyer: " . $conn->error;
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Add New Lawyer</title>
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
      
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title">Add New Lawyer</h3>
                
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
                    <!-- Name -->
                    <div class="col-md-6">
                      <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="name" name="name" value="<?= $_POST['name'] ?? '' ?>" required>
                    </div>
                    
                    <!-- Username -->
                    <div class="col-md-6">
                      <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="username" name="username" value="<?= $_POST['username'] ?? '' ?>" required>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                      <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
                    </div>
                    
                    <!-- Phone -->
                    <div class="col-md-6">
                      <label for="phone" class="form-label">Phone</label>
                      <input type="text" class="form-control" id="phone" name="phone" value="<?= $_POST['phone'] ?? '' ?>">
                    </div>
                    
                    <!-- Address -->
                    <div class="col-md-12">
                      <label for="address" class="form-label">Address</label>
                      <textarea class="form-control" id="address" name="address" rows="3"><?= $_POST['address'] ?? '' ?></textarea>
                    </div>
                    
                    <!-- Password -->
                    <div class="col-md-6">
                      <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                      <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="col-md-6">
                      <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <!-- Admin Privileges -->
                    <div class="col-md-12">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" <?= isset($_POST['is_admin']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_admin">
                          Grant administrator privileges
                        </label>
                      </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="col-12 mt-4">
                      <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i> Add Lawyer
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
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  
  <script>
    $(document).ready(function() {
      $("form").on("submit", function(e) {
        let isValid = true;
        
        const password = $("#password").val();
        const confirmPassword = $("#confirm_password").val();
        
        if (password !== confirmPassword) {
          alert("Passwords do not match");
          isValid = false;
        }
        
        if (!isValid) {
          e.preventDefault();
        }
      });
    });
  </script>
</body>

</html>