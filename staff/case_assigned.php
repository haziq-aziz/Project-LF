<?php 
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

// Get current user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - My Cases</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary mb-0">Assigned Cases</h3>
                <div class="d-flex">
                    <a href="case_add.php" class="btn btn-primary">
                        <i class="fa fa-plus me-2"></i> Add New Case
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <!-- Top Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <!-- Show Entries -->
                        <div>
                            <label>
                                Show 
                                <select id="entriesPerPage" class="form-select d-inline-block w-auto">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select> 
                                entries
                            </label>
                        </div>
                        <!-- Search Field -->
                        <div>
                            <input type="text" id="searchField" class="form-control" placeholder="Search by Case No, Client, or Court">
                        </div>
                    </div>

                    <!-- Cases Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-light">
                                    <th>Case No</th>
                                    <th>Case Type</th>
                                    <th>Court Detail</th>
                                    <th>Parties</th>
                                    <th>Next Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="caseTableBody">
                                <!-- Data will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <!-- Showing entries text -->
                        <div id="showingEntriesText">Showing 0 entries</div>

                        <!-- Pagination controls -->
                        <div>
                            <button class="btn btn-outline-primary" id="prevPage">Previous</button>
                            <button class="btn btn-outline-primary" id="nextPage">Next</button>
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
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  
  <!-- AJAX Script for Pagination -->
  <script>
    $(document).ready(function () {
        let currentPage = 1;
        let entriesPerPage = $("#entriesPerPage").val();
        let searchQuery = "";

        function loadCases() {
            $.ajax({
                url: "../includes/staff/fetch_case_assigned.php",
                type: "GET",
                data: { entries: entriesPerPage, page: currentPage, search: searchQuery },
                success: function (data) {
                    $("#caseTableBody").html(data);
                    $("#showingEntriesText").text(`Showing ${entriesPerPage} entries`);
                }
            });
        }

        // Initial load
        loadCases();

        // Change entries per page
        $("#entriesPerPage").change(function () {
            entriesPerPage = $(this).val();
            currentPage = 1;
            loadCases();
        });

        // Search functionality
        $("#searchField").on("keyup", function () {
            searchQuery = $(this).val();
            currentPage = 1;
            loadCases();
        });

        // Pagination controls
        $("#prevPage").click(function () {
            if (currentPage > 1) {
                currentPage--;
                loadCases();
            }
        });

        $("#nextPage").click(function () {
            currentPage++;
            loadCases();
        });
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>