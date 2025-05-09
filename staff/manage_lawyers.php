<?php 
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

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

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  $lawyer_id = intval($_GET['id']);
  
  if ($lawyer_id == $user_id) {
    $_SESSION['error'] = "You cannot delete your own account";
  } else {
    $delete_query = "DELETE FROM users WHERE id = ? AND role = 'staff'";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $lawyer_id);
    
    if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
      $_SESSION['success'] = "Lawyer account successfully deleted";
    } else {
      $_SESSION['error'] = "Failed to delete lawyer account. They may have associated data.";
    }
  }
  
  header('Location: manage_lawyers.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $errors = [];
  
  $required_fields = ['name', 'username', 'email'];
  foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
      $errors[] = ucfirst($field) . ' is required';
    }
  }
  
  if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address";
  }
  
  if (!empty($_POST['username'])) {
    $username_check = "SELECT id FROM users WHERE username = ? AND id != ?";
    $username_stmt = $conn->prepare($username_check);
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    $username_stmt->bind_param("si", $_POST['username'], $edit_id);
    $username_stmt->execute();
    if ($username_stmt->get_result()->num_rows > 0) {
      $errors[] = "Username already exists";
    }
  }
  
  if (!empty($_POST['email'])) {
    $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
    $email_stmt = $conn->prepare($email_check);
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    $email_stmt->bind_param("si", $_POST['email'], $edit_id);
    $email_stmt->execute();
    if ($email_stmt->get_result()->num_rows > 0) {
      $errors[] = "Email already exists";
    }
  }
  
  if (empty($errors)) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
      $edit_id = intval($_POST['edit_id']);
      
      if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET name = ?, username = ?, email = ?, 
                        phone = ?, address = ?, password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssssi", $name, $username, $email, $phone, $address, $password, $edit_id);
      } else {
        $update_query = "UPDATE users SET name = ?, username = ?, email = ?, 
                        phone = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssssi", $name, $username, $email, $phone, $address, $edit_id);
      }
      
      if ($update_stmt->execute()) {
        $_SESSION['success'] = "Lawyer information updated successfully";
      } else {
        $_SESSION['error'] = "Error updating lawyer information: " . $conn->error;
      }
      
    } else {
      if (empty($_POST['password'])) {
        $errors[] = "Password is required for new lawyers";
      } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'staff';
        
        $insert_query = "INSERT INTO users (name, username, email, phone, address, password, role) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("sssssss", $name, $username, $email, $phone, $address, $password, $role);
        
        if ($insert_stmt->execute()) {
          $_SESSION['success'] = "New lawyer added successfully";
          $_POST = [];
        } else {
          $_SESSION['error'] = "Error adding new lawyer: " . $conn->error;
        }
      }
    }
  }
}

$lawyer_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
  $edit_id = intval($_GET['id']);
  $edit_query = "SELECT id, name, username, email, phone, address FROM users 
                WHERE id = ? AND role = 'staff'";
  $edit_stmt = $conn->prepare($edit_query);
  $edit_stmt->bind_param("i", $edit_id);
  $edit_stmt->execute();
  $lawyer_to_edit = $edit_stmt->get_result()->fetch_assoc();
  
  if (!$lawyer_to_edit) {
    $_SESSION['error'] = "Lawyer not found";
    header('Location: manage_lawyers.php');
    exit();
  }
  
  $_POST = $lawyer_to_edit;
}

$lawyers_query = "SELECT id, name, username, email, phone, created_at FROM users 
                  WHERE role = 'staff' ORDER BY name";
$lawyers_result = $conn->query($lawyers_query);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Manage Lawyers</title>
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
        
        <!-- Add/Edit Lawyer Form Card -->
        <div class="row">
          <div class="col-12 mb-4">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title"><?= isset($lawyer_to_edit) ? 'Edit Lawyer' : 'Add New Lawyer' ?></h3>
                
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
                  <?php if (isset($lawyer_to_edit)): ?>
                    <input type="hidden" name="edit_id" value="<?= $lawyer_to_edit['id'] ?>">
                  <?php endif; ?>
                  
                  <div class="row g-3">
                    <!-- Name -->
                    <div class="col-md-6">
                      <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    
                    <!-- Username -->
                    <div class="col-md-6">
                      <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                      <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    
                    <!-- Phone -->
                    <div class="col-md-6">
                      <label for="phone" class="form-label">Phone Number</label>
                      <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    
                    <!-- Password -->
                    <div class="col-md-6">
                      <label for="password" class="form-label">
                        <?= isset($lawyer_to_edit) ? 'Password (leave blank to keep current)' : 'Password <span class="text-danger">*</span>' ?>
                      </label>
                      <input type="password" class="form-control" id="password" name="password" 
                             <?= isset($lawyer_to_edit) ? '' : 'required' ?>>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="col-md-6">
                      <label for="confirm_password" class="form-label">
                        <?= isset($lawyer_to_edit) ? 'Confirm Password (if changing)' : 'Confirm Password <span class="text-danger">*</span>' ?>
                      </label>
                      <input type="password" class="form-control" id="confirm_password" 
                             <?= isset($lawyer_to_edit) ? '' : 'required' ?>>
                    </div>
                    
                    <!-- Address -->
                    <div class="col-md-12">
                      <label for="address" class="form-label">Address</label>
                      <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="col-12 mt-4">
                      <button type="submit" class="btn btn-primary">
                        <i class="fa fa-<?= isset($lawyer_to_edit) ? 'save' : 'plus' ?> me-2"></i>
                        <?= isset($lawyer_to_edit) ? 'Update Lawyer' : 'Add Lawyer' ?>
                      </button>
                      
                      <?php if (isset($lawyer_to_edit)): ?>
                        <a href="manage_lawyers.php" class="btn btn-secondary ms-2">Cancel Edit</a>
                      <?php endif; ?>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          
          <!-- Lawyers List -->
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title">Lawyers</h3>
                
                <div class="table-responsive mt-4">
                  <table id="lawyers-table" class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Created</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($lawyer = $lawyers_result->fetch_assoc()): ?>
                      <tr>
                        <td><?= $lawyer['id'] ?></td>
                        <td><?= htmlspecialchars($lawyer['name']) ?></td>
                        <td><?= htmlspecialchars($lawyer['username']) ?></td>
                        <td><?= htmlspecialchars($lawyer['email']) ?></td>
                        <td><?= htmlspecialchars($lawyer['phone'] ?? 'N/A') ?></td>
                        <td><?= date('d M Y', strtotime($lawyer['created_at'])) ?></td>
                        <td>
                          <a href="manage_lawyers.php?action=edit&id=<?= $lawyer['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fa fa-edit"></i>
                          </a>
                          
                          <?php if ($lawyer['id'] != $user_id): // Prevent deleting self ?>
                          <a href="#" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $lawyer['id'] ?>, '<?= htmlspecialchars($lawyer['name']) ?>')">
                            <i class="fa fa-trash"></i>
                          </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
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
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  
  <script>
    $(document).ready(function() {
      // Initialize DataTable
      $('#lawyers-table').DataTable({
        ordering: true,
        paging: true,
        searching: true,
        responsive: true
      });
      
      // Password confirmation validation
      $('#password, #confirm_password').on('keyup', function() {
        if ($('#password').val() && $('#confirm_password').val()) {
          if ($('#password').val() != $('#confirm_password').val()) {
            $('#confirm_password').addClass('is-invalid');
          } else {
            $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
          }
        }
      });
      
      // Form validation
      $('form').on('submit', function(e) {
        let isValid = true;
        
        // Password match validation 
        if ($('#password').val() && $('#password').val() != $('#confirm_password').val()) {
          alert('Passwords do not match');
          $('#confirm_password').focus();
          isValid = false;
        }
        
        if (!isValid) {
          e.preventDefault();
        }
      });
    });
    
    // Confirmation for lawyer deletion
    function confirmDelete(id, name) {
      if (confirm(`Are you sure you want to delete the lawyer account for ${name}?`)) {
        window.location.href = `manage_lawyers.php?action=delete&id=${id}`;
      }
    }
  </script>
</body>
</html>