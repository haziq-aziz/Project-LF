<?php
session_start();
require '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../staff/case_view.php');
    exit();
}

// Get case ID
$case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : 0;

if ($case_id === 0) {
    $_SESSION['error'] = "Invalid case ID.";
    header('Location: ../../staff/case_view.php');
    exit();
}

// Validate & sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Get client details
$client_id = isset($_POST["client_id"]) ? intval($_POST["client_id"]) : null;
$client_role = sanitize_input($_POST["client_role"] ?? '');

// Get party names based on client role
$respondentName = '';
$petitionerName = '';
$role = $client_role;  // Use client_role directly for the case role

if ($client_role === 'Petitioner') {
    $petitionerName = '';  // Client is the petitioner
    $respondentName = sanitize_input($_POST["respondentName"] ?? '');
} else {
    $respondentName = '';  // Client is the respondent
    $petitionerName = sanitize_input($_POST["petitionerName"] ?? '');
}

$advocateName = sanitize_input($_POST["advocateName"] ?? '');

// Validate that role is one of the allowed values
if ($role && $role !== 'Petitioner' && $role !== 'Respondent') {
    $_SESSION['error'] = "Invalid role value. Must be 'Petitioner' or 'Respondent'.";
    header("Location: ../../staff/edit_case.php?id=$case_id");
    exit();
}

// Rest of your form fields
$caseNo = sanitize_input($_POST["caseNo"] ?? '');
$filingNo = sanitize_input($_POST["filingNo"] ?? '');
$registerNo = sanitize_input($_POST["registerNo"] ?? '');
$caseNoReport = sanitize_input($_POST["caseNoReport"] ?? '');
$caseType = sanitize_input($_POST["caseType"] ?? '');
$filingDate = sanitize_input($_POST["filingDate"] ?? '');
$registerDate = sanitize_input($_POST["registerDate"] ?? '');
$description = sanitize_input($_POST["description"] ?? '');
$caseStage = sanitize_input($_POST["caseStage"] ?? '');
$fileCategory = sanitize_input($_POST["fileCategory"] ?? '');
$firstHearingDate = sanitize_input($_POST["firstHearingDate"] ?? '');
$casePriority = sanitize_input($_POST["casePriority"] ?? '');
$courtDetail = sanitize_input($_POST["courtDetail"] ?? '');
$courtType = sanitize_input($_POST["courtType"] ?? '');
$court = sanitize_input($_POST["court"] ?? '');
$judgeName = sanitize_input($_POST["judgeName"] ?? '');
$remarks = sanitize_input($_POST["remarks"] ?? '');
$lawyer_id = isset($_POST["lawyer_id"]) ? intval($_POST["lawyer_id"]) : null;

// Check if required fields are empty
if (empty($caseNo) || empty($filingNo) || empty($registerNo) || empty($caseType)) {
    $_SESSION['error'] = "Required fields are missing!";
    header("Location: ../../staff/edit_case.php?id=$case_id");
    exit();
}

// Check if case number is already in use by a different case
$check_query = "SELECT id FROM cases WHERE case_no = ? AND id != ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "si", $caseNo, $case_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    $_SESSION['error'] = "A different case with this case number already exists. Please choose a different case number.";
    header("Location: ../../staff/edit_case.php?id=$case_id");
    exit();
}
mysqli_stmt_close($check_stmt);

// Do the same check for filing_no
$check_query = "SELECT id FROM cases WHERE filing_no = ? AND id != ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "si", $filingNo, $case_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    $_SESSION['error'] = "A different case with this filing number already exists. Please choose a different filing number.";
    header("Location: ../../staff/edit_case.php?id=$case_id");
    exit();
}
mysqli_stmt_close($check_stmt);

// Do the same check for register_no
$check_query = "SELECT id FROM cases WHERE register_no = ? AND id != ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "si", $registerNo, $case_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    $_SESSION['error'] = "A different case with this register number already exists. Please choose a different register number.";
    header("Location: ../../staff/edit_case.php?id=$case_id");
    exit();
}
mysqli_stmt_close($check_stmt);

// Update case record
$query = "UPDATE cases SET 
          client_id = ?, 
          client_role = ?, 
          respondent_name = ?, 
          petitioner_name = ?, 
          advocate_name = ?, 
          role = ?, 
          case_no = ?, 
          filing_no = ?, 
          register_no = ?, 
          case_no_report = ?, 
          case_type = ?, 
          filing_date = ?, 
          register_date = ?, 
          description = ?, 
          case_stage = ?, 
          file_category = ?, 
          first_hearing_date = ?, 
          case_priority = ?, 
          court_detail = ?, 
          court_type = ?, 
          court = ?, 
          judge_name = ?, 
          remarks = ?, 
          lawyer_id = ?
          WHERE id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "issssssssssssssssssssssis", 
    $client_id, $client_role, $respondentName, $petitionerName, $advocateName, $role, 
    $caseNo, $filingNo, $registerNo, $caseNoReport, $caseType, $filingDate, $registerDate, 
    $description, $caseStage, $fileCategory, $firstHearingDate, $casePriority, $courtDetail, 
    $courtType, $court, $judgeName, $remarks, $lawyer_id, $case_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Case updated successfully!";
    header('Location: ../../staff/case_view.php');
    exit();
} else {
    $_SESSION['error'] = "Failed to update case: " . mysqli_error($conn);
    header("Location: ../../staff/edit_case.php?id=$case_id");
    exit();
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>