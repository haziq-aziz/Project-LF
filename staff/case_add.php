<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

$clientQuery = "SELECT id, name FROM clients";
$clientResult = $conn->query($clientQuery);

$lawyerQuery = "SELECT id, name FROM users WHERE role = 'staff'";
$lawyerResult = $conn->query($lawyerQuery);

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Add a case</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
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
            <h3 class="text-primary mb-4 text-uppercase">Add Case</h3>
            <form action="../includes/staff/add_case_process.php" method="POST">
                <!-- Client Details -->
                <div class="card">
                    <div class="card-body">
                        <legend>Client Details</legend>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="clientName" class="form-label text-primary">Respondent's Name</label>
                                    <input type="text" name="respondentName" class="form-control" id="clientName" placeholder="Enter full name" required>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="role" id="petitioner" value="Petitioner" required>
                                            <label class="form-check-label" for="petitioner">Petitioner</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="role" id="respondent" value="Respondent" required>
                                            <label class="form-check-label" for="respondent">Respondent</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="advocateName" class="form-label text-primary">Respondent's Advocate</label>
                                    <input type="text" name="respondentAdvocate" class="form-control" id="advocateName" placeholder="Enter Respondent's Advocate" required>
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
                                    <input type="text" name="caseNo" class="form-control" id="caseNo" required>
                                </div>
                                <div class="mb-4">
                                    <label for="filingNo" class="form-label text-primary">Filing Number</label>
                                    <input type="text" name="filingNo" class="form-control" id="filingNo" required>
                                </div>
                                <div class="mb-4">
                                    <label for="registerNo" class="form-label text-primary">Registration Number</label>
                                    <input type="text" name="registerNo" class="form-control" id="registerNo" required>
                                </div>
                                <div class="mb-4">
                                    <label for="caseNoReport" class="form-label text-primary">Case Number Report</label>
                                    <input type="text" name="caseNoReport" class="form-control" id="caseNoReport" required>
                                </div>
                            </div>
                            <!-- Middle Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="caseType" class="form-label text-primary">Case Type</label>
                                    <select class="form-select" id="caseType" name="caseType">
                                        <option selected disabled>Select Case Type</option>
                                        <option value="Personal Injury">Personal Injury</option>
                                        <option value="Criminal Law">Criminal Law</option>
                                        <option value="Conveyencing">Conveyencing</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="filingDate" class="form-label text-primary">Filing Date</label>
                                    <input type="date" name="filingDate" class="form-control" id="filingDate" required>
                                </div>
                                <div class="mb-4">
                                    <label for="registerDate" class="form-label text-primary">Registration Date</label>
                                    <input type="date" name="registerDate" class="form-control" id="registerDate" required>
                                </div>
                                <div class="mb-4">
                                          <label for="description" class="form-label text-primary">Descrition</label>
                                          <textarea name="description" class="form-control" id="description" rows="4" required></textarea>
                                      </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-4">
                                 <div class="mb-4">
                                    <label for="caseStage" class="form-label text-primary">Stage of Case</label>
                                    <select class="form-select" id="caseStage" name="caseStage">
                                        <option selected disabled>Select Stage</option>
                                        <option value="Case Open">Case Open</option>
                                        <option value="Case Ongoing">Case Ongoing</option>
                                        <option value="Case Close">Case Close</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="fileCategory" class="form-label text-primary">File Category</label>
                                    <select class="form-select" id="fileCategory" name="fileCategory">
                                        <option selected disabled>Select Category</option>
                                        <option value="Personal Injury">Blue</option>
                                        <option value="Criminal Law">Green</option>
                                        <option value="Conveyencing">Red</option>
                                    </select>
                                </div>
                                 <div class="mb-4">
                                    <label for="firstHearingDate" class="form-label text-primary">First Hearing Date</label>
                                    <input type="date" name="firstHearingDate" class="form-control" id="firstHearingDate" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-primary">Case Priority</label>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="casePriority" id="high" value="High" required>
                                            <label class="form-check-label" for="high">High</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="casePriority" id="medium" value="Medium" required>
                                            <label class="form-check-label" for="medium">Medium</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="casePriority" id="low" value="Low" required>
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
                                        <input type="text" name="courtDetail" class="form-control" id="courtDetail" placeholder="Court Detail" required>
                                    </div>
                                </div>
                                <!-- Middle Column -->
                                <div class="col-md-4">
                                    <div class="mb-4">
                                        <label for="courtType" class="form-label text-primary">Court Type</label>
                                        <input type="text" name="courtType" class="form-control" id="courtType" placeholder="Court Type" required>
                                    </div>
                                </div>
                                <!-- Right Column -->
                                 <div class="col-md-4">
                                    <div class="mb-4">
                                        <label for="court" class="form-label text-primary">Court</label>
                                        <input type="text" name="court" class="form-control" id="court" placeholder="Court" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label for="judgeName" class="form-label text-primary">Judge Name</label>
                                        <input type="text" name="judgeName" class="form-control" id="judgeName" placeholder="Judge Name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="remarks" class="form-label text-primary">Remarks</label>
                                    <textarea name="remarks" class="form-control" id="remarks" rows="3" required></textarea>
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
                                        <option selected disabled>Select Lawyer</option>
                                        <?php while ($lawyer = $lawyerResult->fetch_assoc()): ?>
                                        <option value="<?php echo $lawyer['id']; ?>"><?php echo $lawyer['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">‎</label>
                                    <button type="reset" class="btn btn-outline-danger m-1">Reset</button>
                                    <button type="submit" class="btn btn-primary m-1">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
      
        <!-- Include Footer -->
        <?php include '../includes/footer.php'; ?>
        </div>
    </div>
  
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
    <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>