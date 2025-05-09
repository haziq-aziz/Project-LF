<?php 
session_start();
require '../includes/db_connection.php';
require '../includes/staff/fetch_case_detail.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Case Details</title>
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
          <div class="col-lg-4">
            <a href="case_edit.php?id=<?= $case['id']; ?>" class="btn btn-sm btn-primary p-2 mb-2">
              <i class="fa fa-edit me-2"></i>
              Edit Case
            </a>
          </div>
          <div class="row">
            <div class="col-lg">
              <?php if (isset($_SESSION['success'])): ?>
                  <div class="alert alert-success" role="alert">
                      <?php echo $_SESSION['success']; ?>
                  </div>
                  <?php unset($_SESSION['success']); ?>
              <?php endif; ?>
              
              <?php if (isset($_SESSION['error'])): ?>
                  <div class="alert alert-danger" role="alert">
                      <?php echo $_SESSION['error']; ?>
                  </div>
                  <?php unset($_SESSION['error']); ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="card overflow-hidden hover-img">
            <div class="position-relative">
                <img src="../assets/images/staff_dashboard/img_1.jpg" class="card-img-top" style="height: 150px; object-fit: cover;">
              <span class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">
                <?= htmlspecialchars($case['case_type']) ?>
              </span>
            </div>
            <div class="card-body p-4">
                <!-- Case Details -->
                <div class="mb-4">
                    <h1 class="fs-5 text-primary fw-semibold">Case Details</h1>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Client's Name:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['client_name']) ?></p>
                            
                            <strong>Client's Role:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['client_role'] ?? 'Not specified') ?></p>
                            
                            <strong>Case No:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['case_no']) ?></p>
                            
                            <?php if ($case['client_role'] === 'Petitioner'): ?>
                                <strong>Respondent Name:</strong>
                                <p class="mb-2"><?= htmlspecialchars($case['respondent_name']) ?></p>
                            <?php else: ?>
                                <strong>Petitioner Name:</strong>
                                <p class="mb-2"><?= htmlspecialchars($case['petitioner_name']) ?></p>
                            <?php endif; ?>
                            
                            <strong>Opposing Party's Advocate:</strong> 
                            <p class="mb-2"><?= htmlspecialchars($case['advocate_name'] ?? 'Not specified') ?></p>
                            
                            <strong>Assigned Lawyer:</strong> 
                            <p class="mb-2"><?= htmlspecialchars($case['lawyer_name']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Filing No:</strong> 
                            <p class="mb-2"><?= htmlspecialchars($case['filing_no']) ?></p>
                            
                            <strong>Register No:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['register_no']) ?></p>
                            
                            <strong>Case No Report:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['case_no_report']) ?></p>
                            
                            <strong>Filing Date:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['filing_date']) ?></p>
                            
                            <strong>Register Date:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['register_date']) ?></p>
                            
                            <strong>Case Priority:</strong> 
                            <p class="mb-2"><span class="badge 
                            <?php if ($case['case_priority'] == 'High') {
                                echo 'bg-danger';
                            } elseif ($case['case_priority'] == 'Medium') {
                                echo 'bg-warning';
                            } else {
                                echo 'bg-success';
                            } ?>">
                            <?= htmlspecialchars($case['case_priority']) ?></span></p>
                            
                            <strong>Date Created:</strong>
                            <p class="mb-2"> <?= htmlspecialchars($case['created_at']) ?></p>
                        </div>
                    </div>
                </div>
                <hr>
                <h1 class="d-block my-4 fs-5 text-primary fw-semibold">Case Description</h1>
                <p>
                    <?= nl2br(htmlspecialchars($case['description'])) ?>
                </p>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
    <div class="card-body p-4">
        <!-- Client Details -->
        <h1 class="fs-5 text-primary fw-semibold mb-3">Client Details</h1>
        <?php if (!empty($case['client_name'])): ?>
        <div class="row">
            <div class="col-md-6">
                <strong>Client's Name:</strong> 
                <p><?= htmlspecialchars($case['client_name']) ?></p>
                <strong>Email:</strong> 
                <p><?= htmlspecialchars($case['client_email']) ?></p>
                <strong>Phone:</strong> 
                <p><?= htmlspecialchars($case['client_phone']) ?></p>
            </div>
            <div class="col-md-6">
                <strong>Country:</strong> 
                <p><?= htmlspecialchars($case['client_country']) ?></p>
                <strong>State:</strong> 
                <p><?= htmlspecialchars($case['client_state']) ?></p>
                <strong>City:</strong> 
                <p><?= htmlspecialchars($case['client_city']) ?></p>
            </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="timeline-content">
              <strong>Client Address</strong>
              <p><?= htmlspecialchars($case['client_address']) ?></p>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i> No client associated with this case
        </div>
        <?php endif; ?>

        <hr>

        <!-- Court Details -->
        <h1 class="fs-5 text-primary fw-semibold mb-3">Court Details</h1>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Court Detail:</strong> <?= htmlspecialchars($case['court_detail']) ?></p>
                <p><strong>Court Type:</strong> <?= htmlspecialchars($case['court_type']) ?></p>
                <p><strong>Court:</strong> <?= htmlspecialchars($case['court']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Judge Name:</strong> <?= htmlspecialchars($case['judge_name']) ?></p>
                <p><strong>Hearing Date:</strong> <?= htmlspecialchars($case['first_hearing_date']) ?></p>
                <p><strong>Case Stage:</strong> 
                   <span class="badge <?= ($case['case_stage'] == 'Case Open') ? 'bg-info' : 
                                        (($case['case_stage'] == 'Case Ongoing') ? 'bg-warning' : 'bg-success') ?>">
                      <?= htmlspecialchars($case['case_stage']) ?>
                   </span>
                </p>
            </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="timeline-content">
              <strong>Remarks</strong>
              <p><?= htmlspecialchars($case['remarks']) ?></p>
            </div>
          </div>
        </div>
    </div>
</div>

        </div>
        <div class="col-lg-8">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">List of Files</h5>
                <a href="case_add_file.php?case_id=<?= $case['id']; ?>" class="btn btn-primary btn-sm">
                  <i class="fa fa-plus me-1"></i> Add Files
                </a>
              </div>
              <div class="table-responsive">
                <table class="table text-nowrap align-middle mb-0">
                  <thead>
                    <tr class="border-2 border-bottom border-primary border-0"> 
                      <th scope="col" >File Name</th>
                      <th scope="col" >Case No.</th>
                      <th scope="col" class="text-center">File Category</th>
                      <th scope="col" class="text-center">Case Type</th>
                      <th scope="col" class="text-center">Data Created</th>
                      <th scope="col" class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody class="table-group-divider">
                    <?php
                    // Fetch files for this case
                    $files_query = "SELECT cf.*, c.case_no, c.case_type 
                                   FROM case_files cf
                                   JOIN cases c ON cf.case_id = c.id
                                   WHERE cf.case_id = ?
                                   ORDER BY cf.created_at DESC";
                    $stmt = $conn->prepare($files_query);
                    $stmt->bind_param("i", $case['id']);
                    $stmt->execute();
                    $files_result = $stmt->get_result();
                    
                    if ($files_result->num_rows > 0) {
                        while ($file = $files_result->fetch_assoc()) {
                    ?>
                    <tr>
                      <td>
                        <a href="../uploads/case_files/<?= htmlspecialchars($file['file_path']) ?>" target="_blank" 
                           class="link-primary text-dark fw-medium d-block">
                           <?= htmlspecialchars($file['file_name']) ?>
                        </a>
                      </td>
                      <td class="text-center fw-medium"><?= htmlspecialchars($file['case_no']) ?></td>
                      <td class="text-center fw-medium"><?= htmlspecialchars($file['file_category']) ?></td>
                      <td class="text-center fw-medium"><?= htmlspecialchars($file['case_type']) ?></td>
                      <td class="text-center fw-medium"><?= date('d-m-Y', strtotime($file['created_at'])) ?></td>
                      <td class="text-center fw-medium">
                        <a href="../uploads/case_files/<?= htmlspecialchars($file['file_path']) ?>" target="_blank" class="btn btn-sm btn-info">
                          <i class="fa fa-eye"></i> View
                        </a>
                        <a href="edit_case_file.php?id=<?= $file['id'] ?>&case_id=<?= $case['id'] ?>" class="btn btn-sm btn-warning">
                          <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="../includes/staff/delete_case_file.php?id=<?= $file['id'] ?>&case_id=<?= $case['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this file?')" 
                           class="btn btn-sm btn-danger">
                          <i class="fa fa-trash"></i> Delete
                        </a>
                      </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                      <td colspan="6" class="text-center py-3">
                        <i class="fa fa-folder-open text-muted me-2"></i> No files uploaded yet
                      </td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Case Progress</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProgressModal">
                  <i class="fa fa-plus me-1"></i> Add Progress
                </button>
              </div>
              <ul class="timeline">
                <?php
                // Fetch case progress entries
                $progress_query = "SELECT cp.*, u.name as staff_name 
                                  FROM case_progress cp
                                  LEFT JOIN users u ON cp.created_by = u.id
                                  WHERE cp.case_id = ?
                                  ORDER BY cp.progress_date DESC, cp.created_at DESC";
                $stmt = $conn->prepare($progress_query);
                $stmt->bind_param("i", $case['id']);
                $stmt->execute();
                $progress_result = $stmt->get_result();
                
                if ($progress_result->num_rows > 0) {
                    while ($progress = $progress_result->fetch_assoc()) {
                ?>
                <li>
                  <span class="timeline-date"><?= date('d M Y', strtotime($progress['progress_date'])) ?></span>
                  <div class="timeline-content">
                    <div class="d-flex justify-content-between">
                      <strong><?= htmlspecialchars($progress['title']) ?></strong>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-link dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
                          <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li><a class="dropdown-item" href="javascript:void(0);" onclick="editProgress(<?= $progress['id'] ?>, '<?= htmlspecialchars(addslashes($progress['title'])) ?>', '<?= htmlspecialchars(addslashes($progress['description'])) ?>', '<?= $progress['progress_date'] ?>')"><i class="fa fa-edit me-2"></i>Edit</a></li>
                          <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDeleteProgress(<?= $progress['id'] ?>)"><i class="fa fa-trash me-2"></i>Delete</a></li>
                        </ul>
                      </div>
                    </div>
                    <p><?= nl2br(htmlspecialchars($progress['description'])) ?></p>
                    <small class="text-muted">Added by: <?= htmlspecialchars($progress['staff_name']) ?></small>
                  </div>
                </li>
                <?php
                    }
                } else {
                ?>
                <li class="text-center">
                  <div class="timeline-content bg-light">
                    <i class="fa fa-info-circle me-2"></i> No progress entries yet
                  </div>
                </li>
                <?php } ?>
              </ul>
            </div>
          </div>
        </div>
  </div>
      
      <!-- Include Footer -->
      <?php include '../includes/footer.php'; ?>
    </div>
  </div>
  
  <!-- Case Progress Modal -->
  <div class="modal fade" id="addProgressModal" tabindex="-1" aria-labelledby="addProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addProgressModalLabel">Add Case Progress</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="../includes/staff/add_case_progress.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
            
            <div class="mb-3">
              <label for="progress_date" class="form-label">Date</label>
              <input type="date" class="form-control" id="progress_date" name="progress_date" required>
            </div>
            
            <div class="mb-3">
              <label for="title" class="form-label">Title</label>
              <input type="text" class="form-control" id="title" name="title" placeholder="Progress Title" required>
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3" placeholder="Progress Description" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Progress</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <script>
  // Edit progress function - populate the edit modal with existing data
  function editProgress(id, title, description, date) {
    // Create a modal on the fly if it doesn't exist
    if (!$('#editProgressModal').length) {
      const modalHTML = `
      <div class="modal fade" id="editProgressModal" tabindex="-1" aria-labelledby="editProgressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editProgressModalLabel">Edit Case Progress</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../includes/staff/edit_case_progress.php" method="POST">
              <div class="modal-body">
                <input type="hidden" name="progress_id" id="edit_progress_id">
                <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
                
                <div class="mb-3">
                  <label for="edit_progress_date" class="form-label">Date</label>
                  <input type="date" class="form-control" id="edit_progress_date" name="progress_date" required>
                </div>
                
                <div class="mb-3">
                  <label for="edit_title" class="form-label">Title</label>
                  <input type="text" class="form-control" id="edit_title" name="title" placeholder="Progress Title" required>
                </div>
                
                <div class="mb-3">
                  <label for="edit_description" class="form-label">Description</label>
                  <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Progress Description" required></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>`;
      
      $('body').append(modalHTML);
    }
    
    // Populate the form fields
    $('#edit_progress_id').val(id);
    $('#edit_title').val(title);
    $('#edit_description').val(description);
    $('#edit_progress_date').val(date);
    
    // Show the modal
    const editModal = new bootstrap.Modal(document.getElementById('editProgressModal'));
    editModal.show();
  }
  
  // Delete confirmation
  function confirmDeleteProgress(id) {
    if (confirm('Are you sure you want to delete this progress entry? This action cannot be undone.')) {
      window.location.href = `../includes/staff/delete_case_progress.php?id=${id}&case_id=<?= $case['id'] ?>`;
    }
  }

  // Set the default date in the add progress modal to today
  document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('progress_date').value = today;
  });
</script>
</body>

</html>