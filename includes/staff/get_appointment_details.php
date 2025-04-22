<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<p class="text-danger">Not authorized</p>';
    exit();
}

// Get appointment ID
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($appointment_id <= 0) {
    echo '<p class="text-danger">Invalid appointment ID</p>';
    exit();
}

// Fetch appointment details
$query = "SELECT a.*, 
         c.name AS client_name, c.email AS client_email, c.phone AS client_phone,
         cs.case_no, cs.case_type,
         u.name AS staff_name,
         cu.name AS created_by_name
         FROM appointments a
         LEFT JOIN clients c ON a.client_id = c.id
         LEFT JOIN cases cs ON a.case_id = cs.id
         LEFT JOIN users u ON a.staff_id = u.id
         LEFT JOIN users cu ON a.created_by = cu.id
         WHERE a.id = ? AND (a.staff_id = ? OR a.created_by = ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $appointment_id, $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<p class="text-danger">Appointment not found or you do not have permission to view it.</p>';
    exit();
}

$appointment = $result->fetch_assoc();

// Format date and time
$date = date('d F Y', strtotime($appointment['appointment_date']));
$time = date('h:i A', strtotime($appointment['appointment_time']));
$duration = $appointment['duration'] . ' minutes';

// Status badge
$statusBadge = '';
switch($appointment['status']) {
    case 'Scheduled':
        $statusBadge = '<span class="badge bg-primary">Scheduled</span>';
        break;
    case 'Completed':
        $statusBadge = '<span class="badge bg-success">Completed</span>';
        break;
    case 'Cancelled':
        $statusBadge = '<span class="badge bg-danger">Cancelled</span>';
        break;
    case 'Rescheduled':
        $statusBadge = '<span class="badge bg-warning">Rescheduled</span>';
        break;
}

// Build HTML output for appointment details
?>

<div class="row">
    <div class="col-12">
        <h4 class="mb-3"><?= htmlspecialchars($appointment['title']) ?> <?= $statusBadge ?></h4>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h5 class="mb-3">Appointment Details</h5>
        <div class="mb-2">
            <strong>Date:</strong> <?= $date ?>
        </div>
        <div class="mb-2">
            <strong>Time:</strong> <?= $time ?>
        </div>
        <div class="mb-2">
            <strong>Duration:</strong> <?= $duration ?>
        </div>
        <div class="mb-2">
            <strong>Location:</strong> <?= htmlspecialchars($appointment['location'] ?? 'Not specified') ?>
        </div>
        <div class="mb-2">
            <strong>Status:</strong> <?= $appointment['status'] ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <h5 class="mb-3">Related Information</h5>
        <?php if (!empty($appointment['client_name'])): ?>
        <div class="mb-2">
            <strong>Client:</strong> <?= htmlspecialchars($appointment['client_name']) ?>
            <?php if (!empty($appointment['client_email']) || !empty($appointment['client_phone'])): ?>
            <br>
            <small class="text-muted">
                <?= !empty($appointment['client_email']) ? htmlspecialchars($appointment['client_email']) : '' ?>
                <?= !empty($appointment['client_email']) && !empty($appointment['client_phone']) ? ' | ' : '' ?>
                <?= !empty($appointment['client_phone']) ? htmlspecialchars($appointment['client_phone']) : '' ?>
            </small>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="mb-2">
            <strong>Client:</strong> <span class="text-muted">No client associated</span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($appointment['case_no'])): ?>
        <div class="mb-2">
            <strong>Case:</strong> <?= htmlspecialchars($appointment['case_no']) ?> 
            <br><small class="text-muted"><?= htmlspecialchars($appointment['case_type'] ?? '') ?></small>
        </div>
        <?php else: ?>
        <div class="mb-2">
            <strong>Case:</strong> <span class="text-muted">No case associated</span>
        </div>
        <?php endif; ?>
        
        <div class="mb-2">
            <strong>Assigned To:</strong> <?= htmlspecialchars($appointment['staff_name']) ?>
        </div>
        
        <div class="mb-2">
            <strong>Created By:</strong> <?= htmlspecialchars($appointment['created_by_name']) ?>
            <br><small class="text-muted">on <?= date('d M Y H:i', strtotime($appointment['created_at'])) ?></small>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h5 class="mb-3">Description</h5>
        <div class="p-3 bg-light rounded">
            <?= !empty($appointment['description']) ? nl2br(htmlspecialchars($appointment['description'])) : 
                '<span class="text-muted">No description provided</span>' ?>
        </div>
    </div>
</div>

<?php if ($appointment['status'] !== 'Completed' && $appointment['status'] !== 'Cancelled'): ?>
<div class="row mt-4">
    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-warning me-2" onclick="window.location='edit_appointment.php?id=<?= $appointment_id ?>'">
            <i class="fa fa-edit me-1"></i> Edit
        </button>
        <button class="btn btn-success me-2 complete-appointment" data-id="<?= $appointment_id ?>" data-bs-dismiss="modal">
            <i class="fa fa-check me-1"></i> Mark as Completed
        </button>
        <button class="btn btn-danger cancel-appointment" data-id="<?= $appointment_id ?>" data-bs-dismiss="modal">
            <i class="fa fa-times me-1"></i> Cancel Appointment
        </button>
    </div>
</div>
<?php endif; ?>