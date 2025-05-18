<?php 
session_start();
require '../includes/db_connection.php';
require '../includes/notifications_helper.php'; // Add notification helper

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Check if case_id is provided
if (!isset($_GET['case_id']) || empty($_GET['case_id'])) {
    $_SESSION['error'] = "No case selected.";
    header('Location: cases.php');
    exit();
}

$case_id = $_GET['case_id'];

// Fetch case details to display in the form
$stmt = $conn->prepare("SELECT id, case_no, case_type FROM cases WHERE id = ?");
$stmt->bind_param("i", $case_id);
$stmt->execute();
$case_result = $stmt->get_result();

if ($case_result->num_rows === 0) {
    $_SESSION['error'] = "Case not found.";
    header('Location: cases.php');
    exit();
}

$case = $case_result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $file_name = isset($_POST['file_name']) ? trim($_POST['file_name']) : '';
    $file_category = isset($_POST['file_category']) ? trim($_POST['file_category']) : '';
    $file_description = isset($_POST['file_description']) ? trim($_POST['file_description']) : '';
    
    $errors = [];
    
    // Validate inputs
    if (empty($file_name)) {
        $errors[] = "File name is required";
    }
    
    if (empty($file_category)) {
        $errors[] = "File category is required";
    }
    
    // Handle file upload
    if (!isset($_FILES['case_file']) || $_FILES['case_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please select a file to upload";
    } else {
        $file = $_FILES['case_file'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $file['error'];
        }
        
        // Validate file size (max 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB in bytes
        if ($file['size'] > $max_size) {
            $errors[] = "File size exceeds the maximum limit of 10MB";
        }
        
        // Validate file type
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Allowed types: PDF, DOC, DOCX, JPEG, PNG";
        }
    }
    
    // If no errors, proceed with database insertion and file upload
    if (empty($errors)) {
        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/case_files/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename to prevent overwriting
        $file_extension = pathinfo($_FILES['case_file']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid('case_file_') . '.' . $file_extension;
        $file_path = $upload_dir . $unique_filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['case_file']['tmp_name'], $file_path)) {
            // Insert file information into the database
            $stmt = $conn->prepare("INSERT INTO case_files (case_id, file_name, file_category, file_path, file_description, original_filename, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $original_filename = $_FILES['case_file']['name'];
            $stmt->bind_param("isssss", $case_id, $file_name, $file_category, $unique_filename, $file_description, $original_filename);
              if ($stmt->execute()) {
                // Get case and client information for notification
                $case_query = "SELECT c.case_type, c.client_id, cl.name 
                              FROM cases c 
                              LEFT JOIN clients cl ON c.client_id = cl.id 
                              WHERE c.id = ?";
                $case_stmt = $conn->prepare($case_query);
                $case_stmt->bind_param("i", $case_id);                $case_stmt->execute();
                $case_info = $case_stmt->get_result()->fetch_assoc();
                
                // If client exists, send notification about uploaded file
                if ($case_info && $case_info['client_id']) {
                    // Fetch the case_no from the cases table
                    $case_no_query = "SELECT case_no FROM cases WHERE id = ?";
                    $case_no_stmt = $conn->prepare($case_no_query);
                    $case_no_stmt->bind_param("i", $case_id);
                    $case_no_stmt->execute();
                    $case_no_result = $case_no_stmt->get_result();
                      if ($case_no_result && $case_no_data = $case_no_result->fetch_assoc()) {
                        $notification_result = notify_client_file_upload(
                            $case_info['client_id'],
                            $case_no_data['case_no'],
                            $case_info['case_type'],
                            $original_filename
                        );
                          // Debug the notification
                        if (!$notification_result) {
                            $_SESSION['debug'] = "Notification failed: Client ID: " . $case_info['client_id'] . 
                                                ", Case #: " . $case_no_data['case_no'] . 
                                                ", Type: " . $case_info['case_type'];
                                                
                            // Add SQL error if available
                            if (isset($_SESSION['notification_error'])) {
                                $_SESSION['debug'] .= " | Error: " . $_SESSION['notification_error'];
                                unset($_SESSION['notification_error']);
                            }
                        } else {
                            $_SESSION['debug'] = "Notification sent successfully!";
                        }
                    }
                    
                    if (isset($case_no_stmt)) {
                        $case_no_stmt->close();
                    }
                }
                  $_SESSION['success'] = "File uploaded successfully";
                
                // Add debug info to success message if available
                if (isset($_SESSION['debug'])) {
                    $_SESSION['success'] .= " | " . $_SESSION['debug'];
                    unset($_SESSION['debug']);
                }
                
                header("Location: case_detail.php?case_id=$case_id");
                exit();
            } else {
                $errors[] = "Database error: " . $conn->error;
                // Delete uploaded file if database insertion fails
                unlink($file_path);
            }
        } else {
            $errors[] = "Failed to move uploaded file";
        }
    }
}

// Get available file categories
$file_categories = [
    'Green', 
    'Blue', 
    'Red',
];
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Add Case File</title>
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
          <div class="col-lg-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Add Case File</h4>
                <p class="mb-0">
                  Case: <strong><?= htmlspecialchars($case['case_no']) ?></strong> | 
                  Type: <strong><?= htmlspecialchars($case['case_type']) ?></strong>
                </p>
              </div>
              <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                  <strong>Errors:</strong>
                  <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                      <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>
                
                <form action="case_add_file.php?case_id=<?= $case_id ?>" method="post" enctype="multipart/form-data">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="file_name" class="form-label">File Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="file_name" name="file_name" required value="<?= isset($file_name) ? htmlspecialchars($file_name) : '' ?>">
                    </div>
                    <div class="col-md-6">
                      <label for="file_category" class="form-label">File Category <span class="text-danger">*</span></label>
                      <select class="form-select" id="file_category" name="file_category" required>
                        <option value="">Select Category</option>
                        <?php foreach ($file_categories as $category): ?>
                          <option value="<?= htmlspecialchars($category) ?>" <?= (isset($file_category) && $file_category === $category) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label for="file_description" class="form-label">Description</label>
                      <textarea class="form-control" id="file_description" name="file_description" rows="3"><?= isset($file_description) ? htmlspecialchars($file_description) : '' ?></textarea>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label for="case_file" class="form-label">Upload File <span class="text-danger">*</span></label>
                      <input type="file" class="form-control" id="case_file" name="case_file" required>
                      <small class="text-muted">
                        Max file size: 10MB. Allowed file types: PDF, DOC, DOCX, JPG, PNG
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-upload me-1"></i> Upload File
                    </button>
                    <a href="case_detail.php?id=<?= $case_id ?>" class="btn btn-secondary">
                      <i class="fa fa-arrow-left me-1"></i> Back
                    </a>
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
</body>

</html>