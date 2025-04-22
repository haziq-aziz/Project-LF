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

// Updated query to only fetch cases assigned to the current user
$latest_cases_query = "SELECT c.id, c.case_no, c.case_type, c.case_stage as status, c.description, 
                      c.first_hearing_date as hearing_date, cl.name as client_name, 
                      c.respondent_name as opponent_name,
                      CONCAT(c.case_no, ' - ', cl.name) as case_title
                      FROM cases c
                      LEFT JOIN clients cl ON c.client_id = cl.id
                      WHERE c.lawyer_id = ? 
                      ORDER BY c.created_at DESC 
                      LIMIT 3";

// Use prepared statement to prevent SQL injection
$latest_cases_stmt = $conn->prepare($latest_cases_query);
$latest_cases_stmt->bind_param("i", $user_id);
$latest_cases_stmt->execute();
$latest_cases_result = $latest_cases_stmt->get_result();
$latest_cases = [];

if ($latest_cases_result && $latest_cases_result->num_rows > 0) {
  while ($case = $latest_cases_result->fetch_assoc()) {
    $latest_cases[] = $case;
  }
}

// Add this after your existing case query code

// Fetch upcoming appointments for the current user
$appointments_query = "SELECT a.id, a.title, a.description, a.appointment_date, a.appointment_time, 
                     a.duration, a.location, a.status, c.name as client_name, cs.case_no,
                     cs.case_type 
                     FROM appointments a
                     LEFT JOIN clients c ON a.client_id = c.id
                     LEFT JOIN cases cs ON a.case_id = cs.id
                     WHERE a.staff_id = ? 
                       AND a.appointment_date >= CURDATE()
                       AND a.status != 'Cancelled'
                     ORDER BY a.appointment_date ASC, a.appointment_time ASC
                     LIMIT 5";

$appointments_stmt = $conn->prepare($appointments_query);
$appointments_stmt->bind_param("i", $user_id);
$appointments_stmt->execute();
$appointments_result = $appointments_stmt->get_result();
$upcoming_appointments = [];

if ($appointments_result && $appointments_result->num_rows > 0) {
  while ($appointment = $appointments_result->fetch_assoc()) {
    $upcoming_appointments[] = $appointment;
  }
}

// Function to get image based on case type
function getCaseImage($case_type) {
  $case_type = strtolower($case_type);
  
  if (strpos($case_type, 'personal injury') !== false) {
    return "img_1.jpg";
  } elseif (strpos($case_type, 'conveyancing') !== false) {
    return "img_2.jpg";
  } elseif (strpos($case_type, 'criminal') !== false) {
    return "img_3.jpg";
  } else {
    // Default image if no match
    return "img_1.jpg";
  }
}

// Function to get status badge class
function getStatusBadgeClass($status) {
  switch (strtolower($status)) {
    case 'active':
    case 'ongoing':
    case 'in progress':
      return 'bg-success';
    case 'pending':
    case 'new':
      return 'bg-warning';
    case 'closed':
    case 'completed':
      return 'bg-secondary';
    default:
      return 'bg-primary';
  }
}

// Function to get appointment status badge class
function getAppointmentStatusBadge($status) {
  switch ($status) {
    case 'Scheduled':
      return 'bg-primary';
    case 'Completed':
      return 'bg-success';
    case 'Cancelled':
      return 'bg-danger';
    case 'Rescheduled':
      return 'bg-warning text-dark';
    default:
      return 'bg-secondary';
  }
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
  <link rel="stylesheet" href="../assets/css/others.css" />
  <style>
    /* Add this to the head section of your dashboard.php file */
    .case-card {
      height: 100%; /* Make cards fill their container height */
      display: flex;
      flex-direction: column;
    }
    
    .case-card .card-body {
      flex-grow: 1; /* Allow card body to grow and fill available space */
      display: flex;
      flex-direction: column;
    }
    
    .case-description {
      flex-grow: 1; /* Allow description to take available space */
      min-height: 80px; /* Minimum height for description */
      overflow: hidden;
    }
    
    .case-details {
      margin-top: auto; /* Push details to bottom */
    }
    
    .case-action {
      margin-top: 1rem; /* Consistent spacing for action button */
    }
    
    .img-container {
      height: 150px; /* Fixed height for image container */
      overflow: hidden;
    }
  </style>
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
                        <h2 class="fw-bold mb-2 client-count">0</h2>
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
                        <h2 class="fw-bold mb-2 case-open-count">0</h2>
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
                        <h2 class="fw-bold mb-2 case-ongoing-count">0</h2>
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
                        <h2 class="fw-bold mb-2 case-closed-count">0</h2>
                        <p class="text-muted">total case closed</p>
                    </div>
                    <div>
                        <i class="fa-solid fa-envelope fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fw-semibold mb-2 text-primary">Case Board</h5>
                            <p class="mb-2">Manage your cases</p>
                        </div>
                        <div>
                            <a href="case_assigned.php" class="btn btn-primary">Manage Cases</a>
                        </div>
                    </div>
                    <div class="row">
                        <?php if (count($latest_cases) > 0): ?>
                            <?php foreach ($latest_cases as $case): ?>
                                <div class="col-lg-4 col-md-6 mb-4 d-flex">
                                    <div class="card overflow-hidden hover-img case-card w-100">
                                        <div class="position-relative img-container">
                                            <!-- Case Image based on type -->
                                            <img src="../assets/images/staff_dashboard/<?= getCaseImage($case['case_type']) ?>" 
                                                class="card-img-top" style="height: 100%; object-fit: cover;">
                                            
                                            <!-- Case Type Badge -->
                                            <span class="badge text-bg-light text-dark fs-2 lh-sm py-1 px-2 fw-semibold position-absolute bottom-0 end-0 m-2">
                                                <?= htmlspecialchars($case['case_type']) ?>
                                            </span>
                                        </div>
                                        <div class="card-body p-4">
                                            <!-- Case Title -->
                                            <h5 class="text-primary fw-bold mb-1">
                                                <?= htmlspecialchars($case['client_name']) ?> 
                                                <?= !empty($case['opponent_name']) ? ' vs. ' . htmlspecialchars($case['opponent_name']) : '' ?>
                                            </h5>

                                            <!-- Case Description -->
                                            <div class="case-description">
                                                <p class="text-muted mb-2">
                                                    <?= htmlspecialchars(substr($case['description'] ?? '', 0, 100)) . (strlen($case['description'] ?? '') > 100 ? '...' : '') ?>
                                                </p>
                                            </div>

                                            <!-- Case Details -->
                                            <div class="case-details">
                                                <p class="mb-1"><strong>Case ID:</strong> <?= htmlspecialchars($case['case_no']) ?></p>
                                                <p class="mb-1"><strong>Status:</strong> 
                                                    <span class="badge <?= getStatusBadgeClass($case['status']) ?>">
                                                        <?= htmlspecialchars($case['status']) ?>
                                                    </span>
                                                </p>
                                                <?php if (!empty($case['hearing_date'])): ?>
                                                    <p class="mb-1"><strong>Hearing Date:</strong> 
                                                        <?= date('d M Y', strtotime($case['hearing_date'])) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>

                                            <!-- View More Button -->
                                            <div class="d-flex justify-content-center case-action">
                                                <a href="case_details.php?id=<?= $case['id'] ?>" class="btn btn-outline-primary mt-3">View Case Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 mb-4">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-2"></i> No cases are currently assigned to you. Contact an administrator if you believe this is an error.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
            </div>
        </div>
           <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fw-semibold mb-2 text-primary">Appointments</h5>
                            <p class="mb-2">Manage your upcoming appointments</p>
                        </div>
                        <div>
                            <a href="set_appointment.php" class="btn btn-primary">
                                <i class="fa fa-calendar-plus me-2"></i>Schedule Appointment
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Client</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($upcoming_appointments) > 0): ?>
                                    <?php foreach ($upcoming_appointments as $appointment): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($appointment['title']) ?></strong>
                                                <?php if (!empty($appointment['case_no'])): ?>
                                                    <br><small class="text-muted">Case: <?= htmlspecialchars($appointment['case_no']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($appointment['client_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?= date('d M Y', strtotime($appointment['appointment_date'])) ?><br>
                                                <span class="text-muted">
                                                    <?= date('h:i A', strtotime($appointment['appointment_time'])) ?> 
                                                    (<?= $appointment['duration'] ?> mins)
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($appointment['location'] ?? 'Office') ?></td>
                                            <td>
                                                <span class="badge <?= getAppointmentStatusBadge($appointment['status']) ?>">
                                                    <?= htmlspecialchars($appointment['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="view_appointment.php?id=<?= $appointment['id'] ?>" class="btn btn-outline-primary">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($appointment['status'] !== 'Completed' && $appointment['status'] !== 'Cancelled'): ?>
                                                        <a href="edit_appointment.php?id=<?= $appointment['id'] ?>" class="btn btn-outline-secondary">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        
                                                        <button type="button" class="btn btn-outline-danger" onclick="updateAppointmentStatus(<?= $appointment['id'] ?>, 'Cancelled')">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                        
                                                        <?php if ($appointment['status'] === 'Scheduled'): ?>
                                                            <button type="button" class="btn btn-outline-success" onclick="updateAppointmentStatus(<?= $appointment['id'] ?>, 'Completed')">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info mb-0">
                                                <i class="fa fa-info-circle me-2"></i> No upcoming appointments found.
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($upcoming_appointments) > 0): ?>
                        <div class="text-end mt-3">
                            <a href="appointments.php" class="btn btn-outline-primary">View All Appointments</a>
                        </div>
                    <?php endif; ?>
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

  <script>
    $(document).ready(function() {
        $.ajax({
            url: '../includes/staff/fetch_count.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('.client-count').text(response.total_clients);
                $('.case-open-count').text(response.open_cases);
                $('.case-ongoing-count').text(response.ongoing_cases);
                $('.case-closed-count').text(response.closed_cases);
            },
            error: function() {
                console.log("Error fetching case data.");
            }
        });
    });

    // Add this to your existing $(document).ready function
  
    // Function to update appointment status
    function updateAppointmentStatus(id, status) {
      if (confirm(`Are you sure you want to mark this appointment as ${status}?`)) {
        $.ajax({
          url: '../includes/staff/update_appointment_status.php',
          type: 'POST',
          data: {
            appointment_id: id,
            status: status
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Reload the page to reflect changes
              location.reload();
            } else {
              alert('Error: ' + response.message);
            }
          },
          error: function() {
            alert('An error occurred while updating the appointment status.');
          }
        });
      }
    }
  </script>
</body>

</html>