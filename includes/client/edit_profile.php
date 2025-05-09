<?php
// DEBUG: Enable error reporting for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('../db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check for messages from process-profile.php
$errors = $_SESSION['profile_errors'] ?? [];
$success_message = $_SESSION['success_message'] ?? '';

// Clear session messages after retrieving them
unset($_SESSION['profile_errors']);
unset($_SESSION['success_message']);

// Retrieve user data
$sql = "SELECT * FROM clients WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

echo "<!-- QUERY: " . str_replace('?', $user_id, $sql) . " -->"; 

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$user = $result->fetch_assoc();

$form_data = $user;
if (isset($_SESSION['profile_form_data']) && is_array($_SESSION['profile_form_data'])) {
    
    foreach ($_SESSION['profile_form_data'] as $key => $value) {
        if ($value !== null && $value !== '') {
            $form_data[$key] = $value;
        }
    }
    unset($_SESSION['profile_form_data']);
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    $errors = [];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_picture = $user['profile_picture'] ?? 'default.jpg';

    // Validate required fields
    if ($name === '' || $email === '' || $phone === '' || $address === '') {
        $errors[] = 'All fields are required.';
    }

    // Email uniqueness check
    $check = $conn->prepare('SELECT id FROM clients WHERE email = ? AND id != ?');
    $check->bind_param('si', $email, $user_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = 'Email already exists.';
    }
    $check->close();

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['name'] !== '') {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024;
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading file.';
        } elseif (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, GIF allowed.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'File size too large. Max 2MB.';
        } else {
            $upload_dir = '../../uploads/profile_picture/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = uniqid() . '_' . basename($file['name']);
            $upload_path = $upload_dir . $file_name;
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old picture if not default
                if ($profile_picture && $profile_picture !== 'default.jpg' && file_exists($upload_dir . $profile_picture)) {
                    unlink($upload_dir . $profile_picture);
                }
                $profile_picture = $file_name;
            } else {
                $errors[] = 'Failed to upload file.';
            }
        }
    }

    // Handle password change
    $password_sql = '';
    $password_param = '';
    if ($new_password || $confirm_password) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to change password.';
        } else {
            $pwd_stmt = $conn->prepare('SELECT password FROM clients WHERE id=?');
            $pwd_stmt->bind_param('i', $user_id);
            $pwd_stmt->execute();
            $pwd_result = $pwd_stmt->get_result();
            $user_pwd = $pwd_result->fetch_assoc();
            if (!password_verify($current_password, $user_pwd['password'])) {
                $errors[] = 'Current password is incorrect.';
            }
            $pwd_stmt->close();
        }
        if (empty($new_password)) {
            $errors[] = 'New password cannot be empty.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } else {
            $password_sql = ', password=?';
            $password_param = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    if (empty($errors)) {
        $sql = 'UPDATE clients SET name=?, email=?, phone=?, address=?, profile_picture=?' . ($password_sql ? $password_sql : '') . ' WHERE id=?';
        $types = 'sssssi' . ($password_sql ? 's' : '');
        $params = [$name, $email, $phone, $address, $profile_picture];
        if ($password_sql) $params[] = $password_param;
        $params[] = $user_id;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Profile updated successfully!';
            $_SESSION['name'] = $name;
            header('Location: ../../client/profile.php');
            exit();
        } else {
            $errors[] = 'Error updating profile: ' . $conn->error;
        }
    }
    $_SESSION['profile_errors'] = $errors;
    $_SESSION['profile_form_data'] = $_POST;
    header('Location: ../../client/profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Edit Profile</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="../assets/libs/apexcharts/dist/apexcharts.css" />
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Include sidebar -->
    <?php include_once('../includes/client/sidebar.php'); ?>
    
    <div class="body-wrapper">
      <!-- Include navbar -->
      <?php include_once('../includes/client/navbar.php'); ?>
      
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">My Profile</h5>
            
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
              <div class="alert alert-success">
                <?= htmlspecialchars($success_message) ?>
              </div>
            <?php endif; ?>
            
            <!-- Debug Information Block -->
            <?php if (defined('DEBUG') && DEBUG): ?>
            <div class="alert alert-info">
              <h4>Debug Information</h4>
              <pre><?php print_r($form_data); ?></pre>
            </div>
            <?php endif; ?>
            
            <form method="post" action="../includes/client/edit_profile.php" enctype="multipart/form-data" class="mt-4">
              <div class="row">
                <!-- Profile Picture Column -->
                <div class="col-md-4 text-center mb-4">
                  <div class="mb-3">
                    <img src="../uploads/profile_picture/<?= $form_data['profile_picture'] ?? 'default.jpg' ?>" 
                         class="img-fluid rounded-circle mb-3" 
                         style="width: 180px; height: 180px; object-fit: cover;" 
                         alt="Profile Picture">
                    <div class="mt-2">
                      <label for="profile_picture" class="form-label">Change Profile Picture</label>
                      <input class="form-control" type="file" id="profile_picture" name="profile_picture" accept="image/*">
                      <div class="form-text">Max size: 2MB. Supported formats: JPG, PNG, GIF</div>
                    </div>
                  </div>
                </div>
                
                <!-- Profile Info Column -->
                <div class="col-md-8">
                  <div class="row">
                    <!-- Personal Information -->
                    <div class="col-12 mb-4">
                      <h6 class="fw-semibold mb-3">Personal Information</h6>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label for="name" class="form-label">Full Name</label>
                          <input type="text" class="form-control" id="name" name="name" 
                                 value="<?php echo htmlspecialchars($form_data['name']); ?>">
                        </div>
                        <div class="col-md-6">
                          <label for="email" class="form-label">Email Address</label>
                          <input type="text" class="form-control" id="email" name="email" 
                                value="<?php echo htmlspecialchars($form_data['email']); ?>">
                        </div>
                        <div class="col-md-6">
                          <label for="phone" class="form-label">Phone Number</label>
                          <input type="text" class="form-control" id="phone" name="phone" 
                                value="<?php echo htmlspecialchars($form_data['phone']); ?>">
                        </div>
                        <div class="col-md-6">
                          <!-- Placeholder for layout balance -->
                        </div>
                        <div class="col-12">
                          <label for="address" class="form-label">Address</label>
                          <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="col-12 mt-2">
                      <h6 class="fw-semibold mb-3">Change Password</h6>
                      <div class="row g-3">
                        <div class="col-md-12">
                          <label for="current_password" class="form-label">Current Password</label>
                          <input type="password" class="form-control" id="current_password" name="current_password">
                          <div class="form-text">Leave blank if you don't want to change your password</div>
                        </div>
                        <div class="col-md-6">
                          <label for="new_password" class="form-label">New Password</label>
                          <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="col-md-6">
                          <label for="confirm_password" class="form-label">Confirm New Password</label>
                          <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="dashboard.php" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Profile</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Include footer -->
      <?php include_once('../includes/footer.php'); ?>
    </div>
  </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
</body>
</html>