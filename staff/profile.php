<?php
// Add these two lines at the very top of your file
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../auth/login.php');
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
$sql = "SELECT * FROM users WHERE id=? AND role='staff'";
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
    <?php include_once('../includes/staff/sidebar.php'); ?>
    
    <div class="body-wrapper">
      <!-- Include navbar -->
      <?php include_once('../includes/staff/navbar.php'); ?>
      
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
            
            <form method="post" action="../includes/staff/edit_profile.php" enctype="multipart/form-data" class="mt-4">
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
    
  <script>
  // Dynamic profile picture preview
  document.getElementById('profile_picture').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
      // Check if file is an image
      if (!file.type.match('image.*')) {
        alert('Please select an image file');
        return;
      }
      
      // Create URL for the selected file
      const reader = new FileReader();
      reader.onload = function(e) {
        // Update the profile image preview
        const profileImage = document.querySelector('.col-md-4 img.rounded-circle');
        if (profileImage) {
          profileImage.src = e.target.result;
        }
      };
      reader.readAsDataURL(file);
    }
  });
  </script>
</body>
</html>