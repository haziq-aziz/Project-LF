<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Lawyer Dashboard</title>
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
            <h3 class="text-primary mb-4">List of Clients</h3>
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
                            <input type="text" id="searchField" class="form-control" placeholder="Search...">
                        </div>
                    </div>

                    <!-- Clients Table -->
                    <table class="table table-bordered table-striped">
                      <thead>
                          <tr>
                              <th>ID</th>
                              <th>Client Name</th>
                              <th>Email</th>
                              <th>Mobile No</th>
                              <th>Case No</th>
                              <th>Status</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody id="clientTableBody">
                          <tr>
                              <td>1</td>
                              <td>John Doe</td>
                              <td>john.doe@example.com</td>
                              <td>+60123456789</td>
                              <td>C001</td>
                              <td><span class="badge bg-success">Active</span></td>
                              <td>
                                  <button class="btn btn-sm btn-primary">Edit</button>
                                  <button class="btn btn-sm btn-danger">Delete</button>
                              </td>
                          </tr>
                          <tr>
                              <td>2</td>
                              <td>Jane Smith</td>
                              <td>jane.smith@example.com</td>
                              <td>+60199887766</td>
                              <td>C002</td>
                              <td><span class="badge bg-warning">Pending</span></td>
                              <td>
                                  <button class="btn btn-sm btn-primary">Edit</button>
                                  <button class="btn btn-sm btn-danger">Delete</button>
                              </td>
                          </tr>
                          <tr>
                              <td>3</td>
                              <td>Ali bin Abu</td>
                              <td>ali.abu@example.com</td>
                              <td>+60123456788</td>
                              <td>C003</td>
                              <td><span class="badge bg-danger">Closed</span></td>
                              <td>
                                  <button class="btn btn-sm btn-primary">Edit</button>
                                  <button class="btn btn-sm btn-danger">Delete</button>
                              </td>
                          </tr>
                          <tr>
                              <td>4</td>
                              <td>Siti Aisyah</td>
                              <td>siti.aisyah@example.com</td>
                              <td>+60187654321</td>
                              <td>C004</td>
                              <td><span class="badge bg-success">Active</span></td>
                              <td>
                                  <button class="btn btn-sm btn-primary">Edit</button>
                                  <button class="btn btn-sm btn-danger">Delete</button>
                              </td>
                          </tr>
                          <tr>
                            <td>5</td>
                            <td>Michael Lee</td>
                            <td>michael.lee@example.com</td>
                            <td>+60176543210</td>
                            <td>C005</td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                          </tr>
                        </tbody>
                        </table>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Showing entries text -->
                            <div id="showingEntriesText">Showing 1 to 10 of 100 entries</div>

                            <!-- Page Numbers -->
                            <ul class="pagination mb-0">
                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">4</a></li>
                           </ul>

                            <!-- Previous & Next Buttons -->
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
</body>

</html>