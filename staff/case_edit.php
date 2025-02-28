<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$caseId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$caseData = [];

// Fetch existing case data
if ($caseId > 0) {
    $caseQuery = "SELECT * FROM cases WHERE id = ?";
    $stmt = $conn->prepare($caseQuery);
    $stmt->bind_param("i", $caseId);
    $stmt->execute();
    $caseResult = $stmt->get_result();

    if ($caseResult->num_rows > 0) {
        $caseData = $caseResult->fetch_assoc();
    } else {
        $_SESSION['error'] = "Case not found.";
        header("Location: ../../staff/case_view.php");
        exit();
    }
    $stmt->close();
}

// Fetch clients and lawyers for dropdowns
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
  <title>Nabihah Ishak & CO. - Edit Case</title>
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
            <h3 class="text-primary mb-4 text-uppercase">Edit Case</h3>
            <form action="../includes/staff/edit_case_process.php" method="POST">
                <input type="hidden" name="case_id" value="<?= $caseId; ?>">
                <!-- Client Details -->
                <div class="card">
                    <div class="card-body">
                        <legend>Client Details</legend>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="respondentName" class="form-label text-primary">Respondent's Name</label>
                                    <input type="text" name="respondentName" class="form-control" id="respondentName" value="<?= htmlspecialchars($caseData['respondent_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="role" id="petitioner" value="Petitioner" <?= ($caseData['role'] ?? '') === 'Petitioner' ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="petitioner">Petitioner</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="role" id="respondent" value="Respondent" <?= ($caseData['role'] ?? '') === 'Respondent' ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="respondent">Respondent</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="respondentAdvocate" class="form-label text-primary">Respondent's Advocate</label>
                                    <input type="text" name="respondentAdvocate" class="form-control" id="respondentAdvocate" value="<?= htmlspecialchars($caseData['respondent_advocate'] ?? ''); ?>" required>
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
                                    <input type="text" name="caseNo" class="form-control" id="caseNo" value="<?= htmlspecialchars($caseData['case_no'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="filingNo" class="form-label text-primary">Filing Number</label>
                                    <input type="text" name="filingNo" class="form-control" id="filingNo" value="<?= htmlspecialchars($caseData['filing_no'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="registerNo" class="form-label text-primary">Registration Number</label>
                                    <input type="text" name="registerNo" class="form-control" id="registerNo" value="<?= htmlspecialchars($caseData['register_no'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="caseNoReport" class="form-label text-primary">Case Number Report</label>
                                    <input type="text" name="caseNoReport" class="form-control" id="caseNoReport" value="<?= htmlspecialchars($caseData['case_no_report'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <!-- Middle Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="caseType" class="form-label text-primary">Case Type</label>
                                    <select class="form-select" id="caseType" name="caseType" required>
                                        <option value="Personal Injury" <?= ($caseData['case_type'] ?? '') === 'Personal Injury' ? 'selected' : ''; ?>>Personal Injury</option>
                                        <option value="Criminal Law" <?= ($caseData['case_type'] ?? '') === 'Criminal Law' ? 'selected' : ''; ?>>Criminal Law</option>
                                        <option value="Conveyencing" <?= ($caseData['case_type'] ?? '') === 'Conveyencing' ? 'selected' : ''; ?>>Conveyencing</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="filingDate" class="form-label text-primary">Filing Date</label>
                                    <input type="date" name="filingDate" class="form-control" id="filingDate" value="<?= htmlspecialchars($caseData['filing_date'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="registerDate" class="form-label text-primary">Registration Date</label>
                                    <input type="date" name="registerDate" class="form-control" id="registerDate" value="<?= htmlspecialchars($caseData['register_date'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="description" class="form-label text-primary">Description</label>
                                    <textarea name="description" class="form-control" id="description" rows="4" required><?= htmlspecialchars($caseData['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="caseStage" class="form-label text-primary">Stage of Case</label>
                                    <select class="form-select" id="caseStage" name="caseStage" required>
                                        <option value="Case Open" <?= ($caseData['case_stage'] ?? '') === 'Case Open' ? 'selected' : ''; ?>>Case Open</option>
                                        <option value="Case Ongoing" <?= ($caseData['case_stage'] ?? '') === 'Case Ongoing' ? 'selected' : ''; ?>>Case Ongoing</option>
                                        <option value="Case Close" <?= ($caseData['case_stage'] ?? '') === 'Case Close' ? 'selected' : ''; ?>>Case Close</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="fileCategory" class="form-label text-primary">File Category</label>
                                    <select class="form-select" id="fileCategory" name="fileCategory" required>
                                        <option value="Blue" <?= ($caseData['file_category'] ?? '') === 'Blue' ? 'selected' : ''; ?>>Blue</option>
                                        <option value="Green" <?= ($caseData['file_category'] ?? '') === 'Green' ? 'selected' : ''; ?>>Green</option>
                                        <option value="Red" <?= ($caseData['file_category'] ?? '') === 'Red' ? 'selected' : ''; ?>>Red</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="firstHearingDate" class="form-label text-primary">First Hearing Date</label>
                                    <input type="date" name="firstHearingDate" class="form-control" id="firstHearingDate" value="<?= htmlspecialchars($caseData['first_hearing_date'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-primary">Case Priority</label>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="casePriority" id="high" value="High" <?= ($caseData['case_priority'] ?? '') === 'High' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="high">High</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="casePriority" id="medium" value="Medium" <?= ($caseData['case_priority'] ?? '') === 'Medium' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="medium">Medium</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="casePriority" id="low" value="Low" <?= ($caseData['case_priority'] ?? '') === 'Low' ? 'checked' : ''; ?> required>
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
                                    <input type="text" name="courtDetail" class="form-control" id="courtDetail" value="<?= htmlspecialchars($caseData['court_detail'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <!-- Middle Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="courtType" class="form-label text-primary">Court Type</label>
                                    <input type="text" name="courtType" class="form-control" id="courtType" value="<?= htmlspecialchars($caseData['court_type'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="court" class="form-label text-primary">Court</label>
                                    <input type="text" name="court" class="form-control" id="court" value="<?= htmlspecialchars($caseData['court'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <label for="judgeName" class="form-label text-primary">Judge Name</label>
                                    <input type="text" name="judgeName" class="form-control" id="judgeName" value="<?= htmlspecialchars($caseData['judge_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="remarks" class="form-label text-primary">Remarks</label>
                                <textarea name="remarks" class="form-control" id="remarks" rows="3" required><?= htmlspecialchars($caseData['remarks'] ?? ''); ?></textarea>
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
                                    <?php while ($lawyer = $lawyerResult->fetch_assoc()): ?>
                                        <option value="<?= $lawyer['id']; ?>" <?= ($caseData['lawyer_id'] ?? '') === $lawyer['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($lawyer['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">‎</label>
                                <a href="case_view.php" class="btn btn-outline-danger m-1">Cancel</a>
                                <button type="submit" class="btn btn-primary m-1">Update Case</button>
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