<?php 
session_start();
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
          <div class="col-lg-3">
            <div class="card">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="text-primary mb-2 text-uppercase">Clients</h5>
                        <h2 class="fw-bold mb-2">0</h2>
                        <p class="text-muted">total clients</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-users fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="card">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="text-primary mb-2 text-uppercase">Case Open</h5>
                        <h2 class="fw-bold mb-2">0</h2>
                        <p class="text-muted">total case open</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-envelope-open fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="card">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="text-primary mb-2 text-uppercase">Case in Progress</h5>
                        <h2 class="fw-bold mb-2">0</h2>
                        <p class="text-muted">total case in progress</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-envelope-open-text fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="card">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="text-primary mb-2 text-uppercase">Case Closed</h5>
                        <h2 class="fw-bold mb-2">0</h2>
                        <p class="text-muted">total case closed</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-envelope fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align0items-center">
                        <div>
                            <h5 class="card-title fw-semibold mb-2 text-primary">Case Board</h5>
                            <p class="mb-2">Manage your cases</p>
                        </div>
                        <div>
                            <button class="btn btn-primary">View All Cases</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card overflow-hidden hover-img">
                                <div class="position-relative">
                                    <!-- Smaller Case Image -->
                                    <img src="../assets/images/staff_dashboard/img_1.jpg" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    
                                    <!-- Case Type Badge -->
                                    <span class="badge text-bg-light text-dark fs-2 lh-sm py-1 px-2 fw-semibold position-absolute bottom-0 end-0 m-2">
                                        Personal Injury
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Case Title -->
                                    <h5 class="text-primary fw-bold mb-1">Case: Ahmad Haziq vs. XYZ</h5>

                                    <!-- Case Description -->
                                    <p class="text-muted mb-2">
                                        "Dia pergi meninggalkan aku keseorangan di sini. Sakitnya tu di sini, di dalam hatiku."
                                    </p>

                                    <!-- Case Details -->
                                    <p class="mb-1"><strong>Case ID:</strong> #123456</p>
                                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p class="mb-1"><strong>Hearing Date:</strong> 25 March 2024</p>

                                    <!-- View More Button -->
                                    <div class="d-flex justify-content-center">
                                        <a href="#" class="btn btn-outline-primary mt-3">View Case Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card overflow-hidden hover-img">
                                <div class="position-relative">
                                    <img src="../assets/images/staff_dashboard/img_2.jpg" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <span class="badge text-bg-light text-dark fs-2 lh-sm py-1 px-2 fw-semibold position-absolute bottom-0 end-0 m-2">
                                        Conveyancing
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <h5 class="text-primary fw-bold mb-1">Case: Ahmad Haziq vs. XYZ</h5>
                                    <p class="text-muted mb-2">"Dia pergi meninggalkan aku keseorangan di sini. Sakitnya tu di sini, di dalam hatiku."</p>
                                    <p class="mb-1"><strong>Case ID:</strong> #123456</p>
                                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p class="mb-1"><strong>Hearing Date:</strong> 25 March 2024</p>
                                    <div class="d-flex justify-content-center">
                                        <a href="#" class="btn btn-outline-primary mt-3">View Case Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card overflow-hidden hover-img">
                                <div class="position-relative">
                                    <img src="../assets/images/staff_dashboard/img_3.jpg" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <span class="badge text-bg-light text-dark fs-2 lh-sm py-1 px-2 fw-semibold position-absolute bottom-0 end-0 m-2">
                                        Criminal Law
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <h5 class="text-primary fw-bold mb-1">Case: Ahmad Haziq vs. XYZ</h5>
                                    <p class="text-muted mb-2">"Dia pergi meninggalkan aku keseorangan di sini. Sakitnya tu di sini, di dalam hatiku."</p>
                                    <p class="mb-1"><strong>Case ID:</strong> #123456</p>
                                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p class="mb-1"><strong>Hearing Date:</strong> 25 March 2024</p>
                                    <div class="d-flex justify-content-center">
                                        <a href="#" class="btn btn-outline-primary mt-3">View Case Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End Row -->
                </div>
            </div>
           </div>
           <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fw-semibold mb-2 text-primary">Appointments</h5>
                            <p class="mb-2">Manage your appointments</p>
                        </div>
                        <div>
                            <button class="btn btn-primary">Schedule Appointment</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ahmad Haziq</td>
                                    <td>26 March 2024</td>
                                    <td>10:00 AM</td>
                                    <td><span class="badge bg-success">Confirmed</span></td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm">View</button>
                                        <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>John Doe</td>
                                    <td>28 March 2024</td>
                                    <td>3:00 PM</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm">View</button>
                                        <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                    </td>
                                </tr>
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
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>