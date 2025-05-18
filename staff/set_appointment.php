<?php 
session_start();
require '../includes/db_connection.php';
require '../includes/notifications_helper.php'; // Add notification helper

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

// Get current user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';

// Process form submission
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
        $status = 'Scheduled';
        $created_by = $user_id;
        
        // Insert into database
        $query = "INSERT INTO appointments (title, description, appointment_date, appointment_time, 
                 duration, location, client_id, case_id, staff_id, status, created_by) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        // Handle NULL values properly for client_id and case_id
        if ($client_id === null) {
            $client_id = NULL;
        }
        if ($case_id === null) {
            $case_id = NULL;
        }
        
        $stmt->bind_param("ssssisiissi", $title, $description, $appointment_date, $appointment_time, 
                         $duration, $location, $client_id, $case_id, $staff_id, $status, $created_by);
          if ($stmt->execute()) {
            $appointment_id = $stmt->insert_id;
            
            // If client is selected, notify them about the appointment
            if ($client_id) {
                // Get formatted date for better readability
                $formatted_date = date('F j, Y', strtotime($appointment_date)) . ' at ' . date('g:i A', strtotime($appointment_time));
                
                // Send notification to client                $title = "New Appointment Scheduled";
                $message = "An appointment has been scheduled for you on {$formatted_date}";
                $link = "/client/dashboard.php"; // Link to client dashboard
                add_notification($client_id, 'client', $title, $message, $link);
            }
            
            // If staff member is different from current user, notify them
            if ($staff_id && $staff_id != $user_id) {
                // Get client name
                $client_name = "Unknown";
                if ($client_id) {
                    $client_query = "SELECT name FROM clients WHERE id = ?";
                    $client_stmt = $conn->prepare($client_query);
                    $client_stmt->bind_param('i', $client_id);
                    $client_stmt->execute();
                    $client_result = $client_stmt->get_result();
                    if ($client_data = $client_result->fetch_assoc()) {
                        $client_name = $client_data['name'];
                    }
                    $client_stmt->close();
                }
                
                // Notify staff member about the appointment
                notify_staff_new_appointment($staff_id, $appointment_id, $client_name, date('F j, Y', strtotime($appointment_date)));
            }
            
            $_SESSION['success'] = "Appointment scheduled successfully";
            header("Location: my_appointments.php");
            exit();
        } else {
            $errors[] = "Error creating appointment: " . $conn->error;
        }
    }
}

// Get list of clients for dropdown
$client_query = "SELECT id, name, email, phone FROM clients ORDER BY name";
$client_result = $conn->query($client_query);

// Get list of cases for dropdown
$case_query = "SELECT c.id, c.case_no, c.case_type, cl.name as client_name 
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
  <title>Nabihah Ishak & CO. - Set Appointment</title>
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
                <h3 class="card-title">Schedule New Appointment</h3>
                
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
                      <input type="text" class="form-control" id="title" name="title" value="<?= $_POST['title'] ?? '' ?>" required>
                    </div>
                    
                    <!-- Client Selection -->
                    <div class="col-md-6">
                      <label for="client_id" class="form-label">Client</label>
                      <select class="form-select" id="client_id" name="client_id">
                        <option value="">-- Select Client (Optional) --</option>
                        <?php while ($client = $client_result->fetch_assoc()): ?>
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
                        <?php while ($case = $case_result->fetch_assoc()): ?>
                          <option value="<?= $case['id'] ?>" data-client-id="<?= $case['client_id'] ?? '' ?>" <?= (isset($_POST['case_id']) && $_POST['case_id'] == $case['id']) ? 'selected' : '' ?>>
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
                      <input type="date" class="form-control datepicker" id="appointment_date" name="appointment_date" value="<?= $_POST['appointment_date'] ?? '' ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="appointment_time" class="form-label">Time <span class="text-danger">*</span></label>
                      <input type="time" class="form-control timepicker" id="appointment_time" name="appointment_time" value="<?= $_POST['appointment_time'] ?? '' ?>" required>
                    </div>
                    
                    <!-- Duration and Location -->
                    <div class="col-md-6">
                      <label for="duration" class="form-label">Duration (minutes)</label>
                      <input type="number" class="form-control" id="duration" name="duration" value="<?= $_POST['duration'] ?? '60' ?>" min="15" step="15">
                    </div>
                    
                    <div class="col-md-6">
                      <label for="location" class="form-label">Location</label>
                      <input type="text" class="form-control" id="location" name="location" value="<?= $_POST['location'] ?? '' ?>" placeholder="Office, Court, etc.">
                    </div>
                    
                    <!-- Assign to Staff -->
                    <div class="col-md-12">
                      <label for="staff_id" class="form-label">Assign To</label>
                      <select class="form-select" id="staff_id" name="staff_id">
                        <?php while ($staff = $staff_result->fetch_assoc()): ?>
                          <option value="<?= $staff['id'] ?>" <?= ($staff['id'] == $user_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($staff['name']) ?> 
                            <?= ($staff['id'] == $user_id) ? '(Me)' : '' ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    
                    <!-- Description -->
                    <div class="col-md-12">
                      <label for="description" class="form-label">Description</label>
                      <textarea class="form-control" id="description" name="description" rows="4"><?= $_POST['description'] ?? '' ?></textarea>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="col-12 mt-4">
                      <button type="submit" class="btn btn-primary">
                        <i class="fa fa-calendar-plus me-2"></i> Schedule Appointment
                      </button>
                      <a href="my_appointments.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                  </div>
                </form>
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
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  
  <script>
    $(document).ready(function() {
      // Initialize flatpickr for better date/time pickers
      $(".datepicker").flatpickr({
        minDate: "today",
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
  </script>
</body>

</html>