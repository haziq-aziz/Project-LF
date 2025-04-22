<?php 
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

// Get current user id
$staff_id = $_SESSION['user_id'];
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - My Appointments</title>
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
                <h3 class="text-primary mb-0">My Appointments</h3>
                <div class="d-flex">
                    <a href="set_appointment.php" class="btn btn-primary">
                        <i class="fa fa-plus me-2"></i> Set New Appointment
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

            <!-- Display Tabs for Different Time Periods -->
            <ul class="nav nav-tabs mb-4" id="appointmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                        Upcoming
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button" role="tab">
                        Today
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab">
                        Past
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        All
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="appointmentTabsContent">
                <!-- Upcoming Appointments Tab -->
                <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <!-- Top Controls -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <!-- Show Entries -->
                                <div>
                                    <label>
                                        Show 
                                        <select id="entriesPerPage_upcoming" class="form-select d-inline-block w-auto entriesPerPage">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select> 
                                        entries
                                    </label>
                                </div>
                                <!-- Search Field -->
                                <div>
                                    <input type="text" id="searchField_upcoming" class="form-control searchField" placeholder="Search...">
                                </div>
                            </div>

                            <!-- Upcoming Appointments Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Title</th>
                                            <th>Date & Time</th>
                                            <th>Client</th>
                                            <th>Case</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="upcomingAppointmentsBody">
                                        <!-- Data will be loaded here dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div id="showingEntriesText_upcoming">Showing 0 entries</div>
                                <div>
                                    <button class="btn btn-outline-primary prevPage" data-target="upcoming">Previous</button>
                                    <button class="btn btn-outline-primary nextPage" data-target="upcoming">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Appointments Tab -->
                <div class="tab-pane fade" id="today" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Title</th>
                                            <th>Time</th>
                                            <th>Client</th>
                                            <th>Case</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="todayAppointmentsBody">
                                        <!-- Today's appointments will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- No pagination needed for today's appointments as they're likely few -->
                        </div>
                    </div>
                </div>

                <!-- Past Appointments Tab -->
                <div class="tab-pane fade" id="past" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <!-- Top Controls -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <label>
                                        Show 
                                        <select id="entriesPerPage_past" class="form-select d-inline-block w-auto entriesPerPage">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select> 
                                        entries
                                    </label>
                                </div>
                                <div>
                                    <input type="text" id="searchField_past" class="form-control searchField" placeholder="Search...">
                                </div>
                            </div>

                            <!-- Past Appointments Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Title</th>
                                            <th>Date & Time</th>
                                            <th>Client</th>
                                            <th>Case</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pastAppointmentsBody">
                                        <!-- Past appointments will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div id="showingEntriesText_past">Showing 0 entries</div>
                                <div>
                                    <button class="btn btn-outline-primary prevPage" data-target="past">Previous</button>
                                    <button class="btn btn-outline-primary nextPage" data-target="past">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- All Appointments Tab -->
                <div class="tab-pane fade" id="all" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <!-- Top Controls -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <label>
                                        Show 
                                        <select id="entriesPerPage_all" class="form-select d-inline-block w-auto entriesPerPage">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select> 
                                        entries
                                    </label>
                                </div>
                                <div>
                                    <input type="text" id="searchField_all" class="form-control searchField" placeholder="Search...">
                                </div>
                            </div>

                            <!-- All Appointments Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Title</th>
                                            <th>Date & Time</th>
                                            <th>Client</th>
                                            <th>Case</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="allAppointmentsBody">
                                        <!-- All appointments will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div id="showingEntriesText_all">Showing 0 entries</div>
                                <div>
                                    <button class="btn btn-outline-primary prevPage" data-target="all">Previous</button>
                                    <button class="btn btn-outline-primary nextPage" data-target="all">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      
      <!-- Modal for Viewing Appointment Details -->
      <div class="modal fade" id="viewAppointmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Appointment Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appointmentDetailsBody">
              <!-- Appointment details will be loaded here -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  
  <!-- AJAX Script for Loading Appointments -->
  <script>
    $(document).ready(function () {
        // Track pagination for each tab
        const tabState = {
            upcoming: { page: 1, entries: 10, search: "" },
            past: { page: 1, entries: 10, search: "" },
            all: { page: 1, entries: 10, search: "" }
        };
        
        // Function to load appointments
        function loadAppointments(tabId) {
            const state = tabState[tabId];
            $.ajax({
                url: "../includes/staff/fetch_my_appointments.php",
                type: "GET",
                data: { 
                    tab: tabId, 
                    entries: state.entries, 
                    page: state.page, 
                    search: state.search 
                },
                success: function (data) {
                    $(`#${tabId}AppointmentsBody`).html(data.html);
                    $(`#showingEntriesText_${tabId}`).text(`Showing ${Math.min(data.total, state.entries)} out of ${data.total} entries`);
                    
                    // Disable prev button on first page
                    $(`.prevPage[data-target="${tabId}"]`).prop('disabled', state.page <= 1);
                    
                    // Disable next button on last page
                    const lastPage = Math.ceil(data.total / state.entries);
                    $(`.nextPage[data-target="${tabId}"]`).prop('disabled', state.page >= lastPage || data.total === 0);
                },
                error: function() {
                    $(`#${tabId}AppointmentsBody`).html('<tr><td colspan="7" class="text-center">Error loading appointments</td></tr>');
                }
            });
        }
        
        // Load today's appointments separately (no pagination)
        function loadTodayAppointments() {
            $.ajax({
                url: "../includes/staff/fetch_my_appointments.php",
                type: "GET",
                data: { tab: 'today' },
                success: function (data) {
                    $("#todayAppointmentsBody").html(data.html);
                },
                error: function() {
                    $("#todayAppointmentsBody").html('<tr><td colspan="7" class="text-center">Error loading today\'s appointments</td></tr>');
                }
            });
        }

        // Initial load for each tab
        loadAppointments('upcoming');
        loadTodayAppointments();
        
        // Handle tab changes
        $('#appointmentTabs button').on('shown.bs.tab', function (e) {
            const target = $(e.target).attr("data-bs-target").substring(1);
            if (target === 'today') {
                loadTodayAppointments();
            } else if (['upcoming', 'past', 'all'].includes(target)) {
                loadAppointments(target);
            }
        });

        // Handle entries per page change
        $(".entriesPerPage").change(function () {
            const tabId = $(this).attr('id').split('_')[1];
            tabState[tabId].entries = parseInt($(this).val());
            tabState[tabId].page = 1;
            loadAppointments(tabId);
        });

        // Handle search
        $(".searchField").on("keyup", function () {
            const tabId = $(this).attr('id').split('_')[1];
            tabState[tabId].search = $(this).val();
            tabState[tabId].page = 1;
            loadAppointments(tabId);
        });

        // Handle pagination
        $(".prevPage").click(function () {
            const tabId = $(this).data('target');
            if (tabState[tabId].page > 1) {
                tabState[tabId].page--;
                loadAppointments(tabId);
            }
        });

        $(".nextPage").click(function () {
            const tabId = $(this).data('target');
            tabState[tabId].page++;
            loadAppointments(tabId);
        });
        
        // View appointment details
        $(document).on('click', '.view-appointment', function() {
            const appointmentId = $(this).data('id');
            $.ajax({
                url: "../includes/staff/get_appointment_details.php",
                type: "GET",
                data: { id: appointmentId },
                success: function (data) {
                    $("#appointmentDetailsBody").html(data);
                    $("#viewAppointmentModal").modal('show');
                },
                error: function() {
                    $("#appointmentDetailsBody").html('<p class="text-danger">Error loading appointment details</p>');
                }
            });
        });
        
        // Mark as completed
        $(document).on('click', '.complete-appointment', function() {
            if (confirm('Are you sure you want to mark this appointment as completed?')) {
                const appointmentId = $(this).data('id');
                const tabId = $('.tab-pane.active').attr('id');
                
                $.ajax({
                    url: "../includes/staff/update_appointment_status.php",
                    type: "POST",
                    data: { 
                        id: appointmentId,
                        status: 'Completed'
                    },
                    success: function (response) {
                        if (response.success) {
                            // Reload the appropriate tabs
                            if (tabId === 'today') {
                                loadTodayAppointments();
                            } else {
                                loadAppointments(tabId);
                            }
                            // Also reload upcoming as the appointment might have been there
                            if (tabId !== 'upcoming') loadAppointments('upcoming');
                            
                            // Show success message
                            alert('Appointment marked as completed');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error updating appointment status');
                    }
                });
            }
        });
        
        // Cancel appointment
        $(document).on('click', '.cancel-appointment', function() {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                const appointmentId = $(this).data('id');
                const tabId = $('.tab-pane.active').attr('id');
                
                $.ajax({
                    url: "../includes/staff/update_appointment_status.php",
                    type: "POST",
                    data: { 
                        id: appointmentId,
                        status: 'Cancelled'
                    },
                    success: function (response) {
                        if (response.success) {
                            // Reload the appropriate tabs
                            if (tabId === 'today') {
                                loadTodayAppointments();
                            } else {
                                loadAppointments(tabId);
                            }
                            // Also reload upcoming as the appointment might have been there
                            if (tabId !== 'upcoming') loadAppointments('upcoming');
                            
                            // Show success message
                            alert('Appointment has been cancelled');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error updating appointment status');
                    }
                });
            }
        });
    });
  </script>
</body>

</html>