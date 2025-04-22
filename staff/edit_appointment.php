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

// Check if appointment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment ID";
    header("Location: my_appointments.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Get the appointment data
$query = "SELECT * FROM appointments WHERE id = ? AND (staff_id = ? OR created_by = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $appointment_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Appointment not found or you don't have permission to edit it";
    header("Location: my_appointments.php");
    exit();
}

$appointment = $result->fetch_assoc();

// Process form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['title', 'appointment_date', 'appointment_time'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Process if no errors
    if (empty($errors)) {
        // Sanitize and prepare data
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];
        $duration = !empty($_POST['duration']) ? intval($_POST['duration']) : 60;
        $location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
        $client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;
        $case_id = !empty($_POST['case_id']) ? intval($_POST['case_id']) : null;
        $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : $user_id;
        $status = trim($_POST['status']);
        
        // Validate status - must be one of the allowed ENUM values
        $allowed_statuses = ['Scheduled', 'Completed', 'Cancelled', 'Rescheduled'];
        if (!in_array($status, $allowed_statuses)) {
            $errors[] = "Invalid status value. Please select a valid status.";
        } else {
            // Update the appointment
            $update_query = "UPDATE appointments SET 
                            title = ?, 
                            description = ?, 
                            appointment_date = ?, 
                            appointment_time = ?, 
                            duration = ?, 
                            location = ?, 
                            client_id = ?, 
                            case_id = ?, 
                            staff_id = ?,
                            status = ?
                            WHERE id = ?";
            
            $update_stmt = $conn->prepare($update_query);
            
            // Handle NULL values properly for client_id and case_id
            if ($client_id === null) {
                $client_id = NULL;
            }
            if ($case_id === null) {
                $case_id = NULL;
            }
            
            $update_stmt->bind_param("ssssississi", $title, $description, $appointment_date, $appointment_time, 
                             $duration, $location, $client_id, $case_id, $staff_id, $status, $appointment_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Appointment updated successfully";
                header("Location: my_appointments.php");
                exit();
            } else {
                $errors[] = "Error updating appointment: " . $conn->error;
            }
        }
    }
} else {
    // Pre-fill form with existing appointment data
    $_POST = $appointment;
}

// Get list of clients for dropdown
$client_query = "SELECT id, name, email, phone FROM clients ORDER BY name";
$client_result = $conn->query($client_query);

// Get list of cases for dropdown
$case_query = "SELECT c.id, c.case_no, c.case_type, cl.name as client_name, c.client_id 
              FROM cases c 
              LEFT JOIN clients cl ON c.client_id = cl.id 
              ORDER BY c.case_no";
$case_result = $conn->query($case_query);

// Get list of staff members for dropdown
$staff_query = "SELECT id, name, email FROM users WHERE role = 'staff' ORDER BY name";
$staff_result = $conn->query($staff_query);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Edit Appointment</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- Flatpickr for better date/time picker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="card-title mb-0">Edit Appointment</h3>
                  <span class="badge bg-<?= $appointment['status'] === 'Scheduled' ? 'primary' : 
                                         ($appointment['status'] === 'Completed' ? 'success' : 
                                         ($appointment['status'] === 'Cancelled' ? 'danger' : 'warning')) ?>">
                    <?= $appointment['status'] ?>
                  </span>
                </div>
                
                <?php if (!empty($errors)): ?>
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="mt-4">
                  <div class="row g-3">
                    <!-- Appointment Title -->
                    <div class="col-md-12">
                      <label for="title" class="form-label">Appointment Title <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>
                    
                    <!-- Client Selection -->
                    <div class="col-md-6">
                      <label for="client_id" class="form-label">Client</label>
                      <select class="form-select" id="client_id" name="client_id">
                        <option value="">-- Select Client (Optional) --</option>
                        <?php 
                        // Reset the result pointer
                        $client_result->data_seek(0);
                        while ($client = $client_result->fetch_assoc()): 
                        ?>
                          <option value="<?= $client['id'] ?>" <?= (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client['name']) ?> 
                            (<?= htmlspecialchars($client['email']) ?>)
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    
                    <!-- Case Selection -->
                    <div class="col-md-6">
                      <label for="case_id" class="form-label">Related Case</label>
                      <select class="form-select" id="case_id" name="case_id">
                        <option value="">-- Select Case (Optional) --</option>
                        <?php 
                        // Reset the result pointer
                        $case_result->data_seek(0);
                        while ($case = $case_result->fetch_assoc()): 
                        ?>
                          <option value="<?= $case['id'] ?>" data-client-id="<?= $case['client_id'] ?? '' ?>" 
                                 <?= (isset($_POST['case_id']) && $_POST['case_id'] == $case['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($case['case_no']) ?> - 
                            <?= htmlspecialchars($case['case_type']) ?>
                            <?= !empty($case['client_name']) ? ' (' . htmlspecialchars($case['client_name']) . ')' : '' ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    
                    <!-- Date and Time -->
                    <div class="col-md-6">
                      <label for="appointment_date" class="form-label">Date <span class="text-danger">*</span></label>
                      <input type="date" class="form-control datepicker" id="appointment_date" name="appointment_date" 
                             value="<?= $_POST['appointment_date'] ?? '' ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="appointment_time" class="form-label">Time <span class="text-danger">*</span></label>
                      <input type="time" class="form-control timepicker" id="appointment_time" name="appointment_time" 
                             value="<?= $_POST['appointment_time'] ?? '' ?>" required>
                    </div>
                    
                    <!-- Duration and Location -->
                    <div class="col-md-6">
                      <label for="duration" class="form-label">Duration (minutes)</label>
                      <input type="number" class="form-control" id="duration" name="duration" 
                             value="<?= $_POST['duration'] ?? '60' ?>" min="15" step="15">
                    </div>
                    
                    <div class="col-md-6">
                      <label for="location" class="form-label">Location</label>
                      <input type="text" class="form-control" id="location" name="location" 
                             value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" placeholder="Office, Court, etc.">
                    </div>
                    
                    <!-- Status Selection -->
                    <div class="col-md-6">
                      <label for="status" class="form-label">Status</label>
                      <select class="form-select" id="status" name="status">
                        <option value="Scheduled" <?= ($_POST['status'] ?? '') == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="Completed" <?= ($_POST['status'] ?? '') == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= ($_POST['status'] ?? '') == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="Rescheduled" <?= ($_POST['status'] ?? '') == 'Rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                      </select>
                    </div>
                    
                    <!-- Assign to Staff -->
                    <div class="col-md-6">
                      <label for="staff_id" class="form-label">Assign To</label>
                      <select class="form-select" id="staff_id" name="staff_id">
                        <?php 
                        // Reset the result pointer
                        $staff_result->data_seek(0);
                        while ($staff = $staff_result->fetch_assoc()): 
                        ?>
                          <option value="<?= $staff['id'] ?>" 
                                 <?= (isset($_POST['staff_id']) && $_POST['staff_id'] == $staff['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($staff['name']) ?> 
                            <?= ($staff['id'] == $user_id) ? '(Me)' : '' ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    
                    <!-- Description -->
                    <div class="col-md-12">
                      <label for="description" class="form-label">Description</label>
                      <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="col-12 mt-4 d-flex">
                      <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i> Update Appointment
                      </button>
                      
                      <?php if (($_POST['status'] ?? '') !== 'Completed' && ($_POST['status'] ?? '') !== 'Cancelled'): ?>
                      <button type="button" class="btn btn-success ms-2" onclick="updateStatus('Completed')">
                        <i class="fa fa-check me-2"></i> Mark as Completed
                      </button>
                      
                      <button type="button" class="btn btn-danger ms-2" onclick="updateStatus('Cancelled')">
                        <i class="fa fa-times me-2"></i> Cancel Appointment
                      </button>
                      <?php endif; ?>
                      
                      <a href="my_appointments.php" class="btn btn-secondary ms-auto">Back to Appointments</a>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Status Update Form (Hidden) -->
      <form id="statusUpdateForm" action="../includes/staff/update_appointment_status.php" method="POST" class="d-none">
        <input type="hidden" name="id" value="<?= $appointment_id ?>">
        <input type="hidden" name="status" id="statusValue">
      </form>
      
      <!-- Include Footer -->
      <?php include '../includes/footer.php'; ?>
    </div>
  </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  
  <script>
    $(document).ready(function() {
      // Initialize flatpickr for better date/time pickers
      $(".datepicker").flatpickr({
        dateFormat: "Y-m-d"
      });
      
      $(".timepicker").flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
      });
      
      // Auto-select client when case is selected
      $("#case_id").change(function() {
        const clientId = $(this).find(':selected').data('client-id');
        if (clientId) {
          $("#client_id").val(clientId);
        }
      });
      
      // Validate form before submission
      $("form").on("submit", function(e) {
        let isValid = true;
        
        // Check required fields
        if ($("#title").val().trim() === "") {
          alert("Please enter an appointment title");
          $("#title").focus();
          isValid = false;
        }
        else if ($("#appointment_date").val() === "") {
          alert("Please select an appointment date");
          $("#appointment_date").focus();
          isValid = false;
        }
        else if ($("#appointment_time").val() === "") {
          alert("Please select an appointment time");
          $("#appointment_time").focus();
          isValid = false;
        }
        
        if (!isValid) {
          e.preventDefault();
        }
      });
    });
    
    // Function to update appointment status
    function updateStatus(status) {
      if (confirm(`Are you sure you want to mark this appointment as ${status}?`)) {
        document.getElementById('statusValue').value = status;
        
        // Submit via AJAX
        const formData = new FormData(document.getElementById('statusUpdateForm'));
        
        fetch('../includes/staff/update_appointment_status.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(`Appointment has been marked as ${status}`);
            window.location.href = 'my_appointments.php';
          } else {
            alert(`Error: ${data.message}`);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while updating the appointment status.');
        });
      }
    }
  </script>
</body>

</html>