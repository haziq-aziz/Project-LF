<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

// Check if case ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No case specified for editing.";
    header('Location: case_view.php');
    exit();
}

$case_id = intval($_GET['id']);

// Fetch case details
$caseQuery = "SELECT * FROM cases WHERE id = ?";
$stmt = $conn->prepare($caseQuery);
$stmt->bind_param("i", $case_id);
$stmt->execute();
$case = $stmt->get_result()->fetch_assoc();

if (!$case) {
    $_SESSION['error'] = "Case not found.";
    header('Location: case_view.php');
    exit();
}

// Fetch all clients for the dropdown
$clientQuery = "SELECT id, name FROM clients";
$clientResult = $conn->query($clientQuery);

// Fetch all lawyers for the dropdown
$lawyerQuery = "SELECT id, name FROM users WHERE role = 'staff'";
$lawyerResult = $conn->query($lawyerQuery);

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Edit Case</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
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
            <h3 class="text-primary mb-4 text-uppercase">Edit Case</h3>
            
            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <!-- Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form action="../includes/staff/edit_case_process.php" method="POST">
                <input type="hidden" name="case_id" value="<?= $case_id ?>">
                
                <!-- Client Details -->
                <div class="card">
                    <div class="card-body">
                        <legend>Client Details</legend>
                        <div class="row">
                            <!-- Left Column - Client Selection -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="client_id" class="form-label text-primary">Select Client</label>
                                    <select name="client_id" class="form-select" id="client_id">
                                        <option value="">Select Existing Client</option>
                                        <?php 
                                        $clientResult->data_seek(0);
                                        while ($client = $clientResult->fetch_assoc()): 
                                            $selected = ($client['id'] == $case['client_id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $client['id'] ?>" <?= $selected ?>><?= htmlspecialchars($client['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label text-primary">Client's Role in Case</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="client_role" 
                                                id="client_petitioner" value="Petitioner" 
                                                <?= ($case['client_role'] === 'Petitioner') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="client_petitioner">Petitioner</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="client_role" 
                                                id="client_respondent" value="Respondent" 
                                                <?= ($case['client_role'] === 'Respondent') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="client_respondent">Respondent</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column - Dynamic fields -->
                            <div class="col-md-6" id="opposingPartyFields">
                                <!-- These fields will change based on the client's role -->
                                <div class="mb-4" id="respondent_field" style="display:none;">
                                    <label for="respondentName" class="form-label text-primary">Respondent's Name</label>
                                    <input type="text" name="respondentName" class="form-control" id="respondentName" 
                                           placeholder="Enter Respondent's Name" value="<?= htmlspecialchars($case['respondent_name']) ?>">
                                </div>
                                
                                <div class="mb-4" id="petitioner_field" style="display:none;">
                                    <label for="petitionerName" class="form-label text-primary">Petitioner's Name</label>
                                    <input type="text" name="petitionerName" class="form-control" id="petitionerName" 
                                           placeholder="Enter Petitioner's Name" value="<?= htmlspecialchars($case['petitioner_name']) ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="advocateName" class="form-label text-primary">Opposing Party's Advocate</label>
                                    <input type="text" name="advocateName" class="form-control" id="advocateName" 
                                           placeholder="Enter Advocate's Name" value="<?= htmlspecialchars($case['advocate_name'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Case Details -->
                <div class="card">
                    <div class="card-body">
                        <legend>Case Details</legend>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="caseNo" class="form-label text-primary">Case No.</label>
                                    <input type="text" name="caseNo" class="form-control" id="caseNo" 
                                          value="<?= htmlspecialchars($case['case_no']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="filingNo" class="form-label text-primary">Filing Number</label>
                                    <input type="text" name="filingNo" class="form-control" id="filingNo" 
                                          value="<?= htmlspecialchars($case['filing_no']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="registerNo" class="form-label text-primary">Registration Number</label>
                                    <input type="text" name="registerNo" class="form-control" id="registerNo" 
                                          value="<?= htmlspecialchars($case['register_no']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="caseNoReport" class="form-label text-primary">Case Number Report</label>
                                    <input type="text" name="caseNoReport" class="form-control" id="caseNoReport" 
                                          value="<?= htmlspecialchars($case['case_no_report']) ?>" required>
                                </div>
                            </div>
                            <!-- Middle Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="caseType" class="form-label text-primary">Case Type</label>
                                    <select class="form-select" id="caseType" name="caseType">
                                        <option disabled>Select Case Type</option>
                                        <option value="Personal Injury" <?= ($case['case_type'] === 'Personal Injury') ? 'selected' : '' ?>>Personal Injury</option>
                                        <option value="Criminal Law" <?= ($case['case_type'] === 'Criminal Law') ? 'selected' : '' ?>>Criminal Law</option>
                                        <option value="Conveyencing" <?= ($case['case_type'] === 'Conveyencing') ? 'selected' : '' ?>>Conveyencing</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="filingDate" class="form-label text-primary">Filing Date</label>
                                    <input type="date" name="filingDate" class="form-control" id="filingDate" 
                                          value="<?= htmlspecialchars($case['filing_date']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="registerDate" class="form-label text-primary">Registration Date</label>
                                    <input type="date" name="registerDate" class="form-control" id="registerDate" 
                                          value="<?= htmlspecialchars($case['register_date']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="description" class="form-label text-primary">Description</label>
                                    <textarea name="description" class="form-control" id="description" rows="4" required><?= htmlspecialchars($case['description']) ?></textarea>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="caseStage" class="form-label text-primary">Stage of Case</label>
                                    <select class="form-select" id="caseStage" name="caseStage">
                                        <option disabled>Select Stage</option>
                                        <option value="Case Open" <?= ($case['case_stage'] === 'Case Open') ? 'selected' : '' ?>>Case Open</option>
                                        <option value="Case Ongoing" <?= ($case['case_stage'] === 'Case Ongoing') ? 'selected' : '' ?>>Case Ongoing</option>
                                        <option value="Case Close" <?= ($case['case_stage'] === 'Case Close') ? 'selected' : '' ?>>Case Close</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="fileCategory" class="form-label text-primary">File Category</label>
                                    <select class="form-select" id="fileCategory" name="fileCategory">
                                        <option disabled>Select Category</option>
                                        <option value="Blue" <?= ($case['file_category'] === 'Blue') ? 'selected' : '' ?>>Blue</option>
                                        <option value="Green" <?= ($case['file_category'] === 'Green') ? 'selected' : '' ?>>Green</option>
                                        <option value="Red" <?= ($case['file_category'] === 'Red') ? 'selected' : '' ?>>Red</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="firstHearingDate" class="form-label text-primary">First Hearing Date</label>
                                    <input type="date" name="firstHearingDate" class="form-control" id="firstHearingDate" 
                                          value="<?= htmlspecialchars($case['first_hearing_date']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-primary">Case Priority</label>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="casePriority" id="high" value="High" 
                                              <?= ($case['case_priority'] === 'High') ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="high">High</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="casePriority" id="medium" value="Medium" 
                                              <?= ($case['case_priority'] === 'Medium') ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="medium">Medium</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="casePriority" id="low" value="Low" 
                                              <?= ($case['case_priority'] === 'Low') ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="low">Low</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Court Detail -->
                <div class="card">
                    <div class="card-body">
                        <legend>Court Detail</legend>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="courtDetail" class="form-label text-primary">Courts Detail</label>
                                    <input type="text" name="courtDetail" class="form-control" id="courtDetail" 
                                          value="<?= htmlspecialchars($case['court_detail']) ?>" required>
                                </div>
                            </div>
                            <!-- Middle Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="courtType" class="form-label text-primary">Court Type</label>
                                    <input type="text" name="courtType" class="form-control" id="courtType" 
                                          value="<?= htmlspecialchars($case['court_type']) ?>" required>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="court" class="form-label text-primary">Court</label>
                                    <input type="text" name="court" class="form-control" id="court" 
                                          value="<?= htmlspecialchars($case['court']) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <label for="judgeName" class="form-label text-primary">Judge Name</label>
                                    <input type="text" name="judgeName" class="form-control" id="judgeName" 
                                          value="<?= htmlspecialchars($case['judge_name']) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="remarks" class="form-label text-primary">Remarks</label>
                                <textarea name="remarks" class="form-control" id="remarks" rows="3" required><?= htmlspecialchars($case['remarks']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Assign -->
                <div class="card">
                    <div class="card-body">
                        <legend>Task Assign</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="lawyer" class="form-label text-primary">Assign Case to Lawyer</label>
                                <select class="form-select" id="lawyer" name="lawyer_id" required>
                                    <option disabled>Select Lawyer</option>
                                    <?php 
                                    $lawyerResult->data_seek(0);
                                    while ($lawyer = $lawyerResult->fetch_assoc()): 
                                        $selected = ($lawyer['id'] == $case['lawyer_id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $lawyer['id'] ?>" <?= $selected ?>><?= htmlspecialchars($lawyer['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">‎</label>
                                <a href="case_view.php" class="btn btn-outline-secondary m-1">Cancel</a>
                                <button type="submit" class="btn btn-primary m-1">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
      // Show the appropriate field on page load based on stored role
      var initialRole = $('input[name="client_role"]:checked').val();
      updateOpposingPartyFields(initialRole);
      
      // When a radio button is clicked
      $('input[name="client_role"]').change(function() {
          var selectedRole = $(this).val();
          updateOpposingPartyFields(selectedRole);
      });
      
      function updateOpposingPartyFields(role) {
          // Hide both fields first
          $('#respondent_field').hide();
          $('#petitioner_field').hide();
          
          // Show relevant field based on selection
          if (role === 'Petitioner') {
              $('#respondent_field').show();
              $('#respondentName').prop('required', true);
              $('#petitionerName').prop('required', false);
          } else if (role === 'Respondent') {
              $('#petitioner_field').show();
              $('#petitionerName').prop('required', true);
              $('#respondentName').prop('required', false);
          }
      }
  });
  </script>
</body>

</html>