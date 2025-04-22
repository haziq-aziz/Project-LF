<?php 
session_start();
require '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Check if file ID and case ID are provided
if (!isset($_GET['id']) || !isset($_GET['case_id']) || empty($_GET['id']) || empty($_GET['case_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: cases.php');
    exit();
}

$file_id = $_GET['id'];
$case_id = $_GET['case_id'];

// Fetch file details
$stmt = $conn->prepare("SELECT cf.*, c.case_no, c.case_type 
                       FROM case_files cf
                       JOIN cases c ON cf.case_id = c.id
                       WHERE cf.id = ? AND cf.case_id = ?");
$stmt->bind_param("ii", $file_id, $case_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "File not found.";
    header("Location: case_detail.php?id=$case_id");
    exit();
}

$file = $result->fetch_assoc();

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
    
    // Handle new file upload if provided
    $file_path = $file['file_path']; // Default to current file path
    $original_filename = $file['original_filename']; // Default to current original filename
    
    if (isset($_FILES['case_file']) && $_FILES['case_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploaded_file = $_FILES['case_file'];
        
        // Check for upload errors
        if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $uploaded_file['error'];
        } else {
            // Validate file size (max 10MB)
            $max_size = 10 * 1024 * 1024; // 10MB in bytes
            if ($uploaded_file['size'] > $max_size) {
                $errors[] = "File size exceeds the maximum limit of 10MB";
            }
            
            // Validate file type
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            $file_type = mime_content_type($uploaded_file['tmp_name']);
            
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Invalid file type. Allowed types: PDF, DOC, DOCX, JPEG, PNG";
            }
            
            // If no errors with the file, prepare for upload
            if (empty($errors)) {
                // Create uploads directory if it doesn't exist
                $upload_dir = '../uploads/case_files/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);
                $unique_filename = uniqid('case_file_') . '.' . $file_extension;
                $new_file_path = $upload_dir . $unique_filename;
                
                // Delete old file if a new one is being uploaded
                if (file_exists($upload_dir . $file['file_path'])) {
                    unlink($upload_dir . $file['file_path']);
                }
                
                // Move uploaded file
                if (move_uploaded_file($uploaded_file['tmp_name'], $new_file_path)) {
                    $file_path = $unique_filename;
                    $original_filename = $uploaded_file['name'];
                } else {
                    $errors[] = "Failed to move uploaded file";
                }
            }
        }
    }
    
    // If no errors, update database
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE case_files SET 
                               file_name = ?, 
                               file_category = ?, 
                               file_description = ?, 
                               file_path = ?, 
                               original_filename = ?,
                               updated_at = NOW() 
                               WHERE id = ? AND case_id = ?");
                               
        $stmt->bind_param("sssssii", $file_name, $file_category, $file_description, $file_path, $original_filename, $file_id, $case_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "File updated successfully";
            header("Location: case_detail.php?case_id=$case_id");
            exit();
        } else {
            $errors[] = "Database error: " . $conn->error;
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
  <title>Nabihah Ishak & CO. - Edit Case File</title>
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
                <h4 class="card-title">Edit Case File</h4>
                <p class="mb-0">
                  Case: <strong><?= htmlspecialchars($file['case_no']) ?></strong> | 
                  Type: <strong><?= htmlspecialchars($file['case_type']) ?></strong>
                </p>
              </div>
              <div class="card-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                  <strong>Errors:</strong>
                  <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                      <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>
                
                <form action="edit_case_file.php?id=<?= $file_id ?>&case_id=<?= $case_id ?>" method="post" enctype="multipart/form-data">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="file_name" class="form-label">File Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="file_name" name="file_name" required 
                             value="<?= htmlspecialchars($file['file_name']) ?>">
                    </div>
                    <div class="col-md-6">
                      <label for="file_category" class="form-label">File Category <span class="text-danger">*</span></label>
                      <select class="form-select" id="file_category" name="file_category" required>
                        <option value="">Select Category</option>
                        <?php foreach ($file_categories as $category): ?>
                          <option value="<?= htmlspecialchars($category) ?>" 
                                  <?= ($file['file_category'] === $category) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label for="file_description" class="form-label">Description</label>
                      <textarea class="form-control" id="file_description" name="file_description" rows="3"><?= htmlspecialchars($file['file_description'] ?? '') ?></textarea>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label for="case_file" class="form-label">Current File</label>
                      <div class="mb-2">
                        <a href="../uploads/case_files/<?= htmlspecialchars($file['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="fa fa-file me-1"></i> <?= htmlspecialchars($file['original_filename'] ?? $file['file_path']) ?>
                        </a>
                      </div>
                      
                      <label for="case_file" class="form-label">Upload New File (Optional)</label>
                      <input type="file" class="form-control" id="case_file" name="case_file">
                      <small class="text-muted">
                        Leave this empty to keep the current file. Max file size: 10MB. Allowed file types: PDF, DOC, DOCX, JPG, PNG
                      </small>
                    </div>
                  </div>
                  
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-save me-1"></i> Save Changes
                    </button>
                    <a href="case_detail.php?case_id=<?= $case_id ?>" class="btn btn-secondary">
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