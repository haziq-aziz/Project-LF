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
  <title>Nabihah Ishak & CO. - Lawyer Dashboard</title>
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
            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="card overflow-hidden hover-img">
            <div class="position-relative">
                <img src="../assets/images/staff_dashboard/img_1.jpg" class="card-img-top" style="height: 150px; object-fit: cover;">
              <span class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">
                Personal Injury
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
                            <strong>Case No:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['case_no']) ?></p>
                            <strong>Respondent Name:</strong>
                            <p class="mb-2"><?= htmlspecialchars($case['respondent_name']) ?></p>
                            <strong>Respondent Advocate:</strong> 
                            <p class="mb-2"><?= htmlspecialchars($case['respondent_advocate']) ?></p>
                            <strong>Assigned Lawyer:</strong> 
                            <p class="mb-2"><?= htmlspecialchars($case['lawyer_name']) ?></p>
                            <strong>Filing No:</strong> 
                            <p class="mb-2"><?= htmlspecialchars($case['filing_no']) ?></p>
                        </div>
                        <div class="col-md-6">
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
              <h5 class="card-title">List of Files</h5>
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
                    <tr>
                      <td>
                        <a href="javascript:void(0)" class="link-primary text-dark fw-medium d-block">Nama File Apa.pdf</a>
                      </td>
                      <td class="text-center fw-medium">C001</td>
                      <td class="text-center fw-medium">Blue</td>
                      <td class="text-center fw-medium">Personal Injury</td>
                      <td class="text-center fw-medium">17-03-2025</td>
                      <td class="text-center fw-medium">
                        <a href="">View</a> |
                        <a href="">Edit</a> |
                        <a href="">Delete</a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Case Progress</h5>
            <ul class="timeline">
                <li>
                    <span class="timeline-date">05 Mar 2025</span>
                    <div class="timeline-content">
                        <strong>Case Filed</strong>
                        <p>Case officially filed in court.</p>
                    </div>
                </li>
                <li>
                    <span class="timeline-date">10 Mar 2025</span>
                    <div class="timeline-content">
                        <strong>Case Registered</strong>
                        <p>Case registered under case no C001.</p>
                    </div>
                </li>
                <li>
                    <span class="timeline-date">12 Mar 2025</span>
                    <div class="timeline-content">
                        <strong>Preliminary Hearing</strong>
                        <p>Initial hearing with the judge.</p>
                    </div>
                </li>
                <li>
                    <span class="timeline-date">15 May 2025</span>
                    <div class="timeline-content">
                        <strong>Main Hearing</strong>
                        <p>Full hearing in progress.</p>
                    </div>
                </li>
                <li>
                    <span class="timeline-date">Pending</span>
                    <div class="timeline-content">
                        <strong>Final Verdict</strong>
                        <p>Awaiting judge’s final decision.</p>
                    </div>
                </li>
            </ul>
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
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>