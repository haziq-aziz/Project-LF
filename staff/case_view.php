<?php 
session_start();
require '../includes/db_connection.php';

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
  <title>Nabihah Ishak & CO. - Case Management</title>
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
            <h3 class="text-primary mb-4">Case Management</h3>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <!-- Top Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
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
                            <input type="text" id="searchField" class="form-control" placeholder="Search by Name or Case ID">
                        </div>
                    </div>

                    <!-- Clients Table -->
                    <table class="table table-bordered table-striped">
                      <thead>
                          <tr>
                              <th>Case No.</th>
                              <th>Case Type</th>
                              <th>Court Detail</th>
                              <th>Petitioner vs Respondent</th>
                              <th>Next Date</th>
                              <th>Status</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody id="caseTableBody">
                        <!-- Data will be loaded here dynamically -->
                      </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Showing entries text -->
                        <div id="showingEntriesText">Showing 1 to 10 entries</div>

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
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <!-- AJAX Script for Pagination -->
  <script>
    $(document).ready(function () {
        let currentPage = 1;
        let entriesPerPage = $("#entriesPerPage").val();
        let searchQuery ="";

        function loadClients() {
            $.ajax({
                url: "../includes/staff/fetch_cases.php",
                type: "GET",
                data: { entries: entriesPerPage, page: currentPage, search: searchQuery },
                success: function (data) {
                    $("#caseTableBody").html(data);
                    $("#showingEntriesText").text(`Showing ${entriesPerPage} entries`);
                }
            });
        }

        // Initial load
        loadClients();

        // Change entries per page
        $("#entriesPerPage").change(function () {
            entriesPerPage = $(this).val();
            currentPage = 1;
            loadClients();
        });

        $("#searchField").on("keyup", function () {
            searchQuery = $(this).val();
            currentPage = 1;
            loadClients();
        })

        // Pagination controls
        $("#prevPage").click(function () {
            if (currentPage > 1) {
                currentPage--;
                loadClients();
            }
        });

        $("#nextPage").click(function () {
            currentPage++;
            loadClients();
        });
    });
  </script>

</body>

</html>
