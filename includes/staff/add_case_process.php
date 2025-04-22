<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate & sanitize input
    function sanitize_input($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Get client details
    $client_id = isset($_POST["client_id"]) ? intval($_POST["client_id"]) : 0;
    $client_role = sanitize_input($_POST["client_role"] ?? '');
    
    // Get party names based on client role
    $respondentName = '';
    $petitionerName = '';
    
    // Set the role to match the client's role - ensure it's either 'Petitioner' or 'Respondent'
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
    if ($role !== 'Petitioner' && $role !== 'Respondent') {
        $_SESSION['error'] = "Invalid role value. Must be 'Petitioner' or 'Respondent'.";
        header("Location: ../../staff/case_add.php");
        exit();
    }

    // Rest of your existing form fields
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
    $lawyer_id = sanitize_input($_POST["lawyer_id"] ?? '');

    // Check if required fields are empty
    if (empty($caseNo) || empty($filingNo) || empty($caseType) || empty($lawyer_id) || empty($client_id) || empty($client_role)) {
        $_SESSION['error'] = "Required fields are missing!";
        header("Location: ../../staff/case_add.php");
        exit();
    }

    // Prepare SQL statement
    $query = "INSERT INTO cases 
        (client_id, client_role, respondent_name, petitioner_name, advocate_name, role, case_no, filing_no, register_no, case_no_report, case_type, filing_date, register_date, description, case_stage, file_category, first_hearing_date, case_priority, court_detail, court_type, court, judge_name, remarks, lawyer_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)";

    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "issssssssssssssssssssssi", 
            $client_id, $client_role, $respondentName, $petitionerName, $advocateName, $role, $caseNo, $filingNo, $registerNo, 
            $caseNoReport, $caseType, $filingDate, $registerDate, $description, $caseStage, 
            $fileCategory, $firstHearingDate, $casePriority, $courtDetail, $courtType, 
            $court, $judgeName, $remarks, $lawyer_id
        );

        // Execute and handle result
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Case has been successfully added.";
            header('Location: ../../staff/case_view.php');
            exit();
        } else {
            $_SESSION['error'] = "Failed to add case: " . mysqli_error($conn);
            header('Location: ../../staff/case_add.php');
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to prepare statement: " . mysqli_error($conn);
        header('Location: ../../staff/case_add.php');
        exit();
    }

    mysqli_close($conn);
} else {
    header("Location: ../../staff/case_add.php");
    exit();
}

?>