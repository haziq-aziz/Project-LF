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

    $respondentName = sanitize_input($_POST["respondentName"] ?? '');
    $respondentAdvocate = sanitize_input($_POST["respondentAdvocate"] ?? '');
    $role = sanitize_input($_POST["role"] ?? '');
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
    if (empty($caseNo) || empty($filingNo) || empty($caseType) || empty($lawyer_id)) {
        $_SESSION['error'] = "Required fields are missing!";
        header("Location: ../../staff/case_add.php");
        exit();
    }

    // Prepare SQL statement
    $query = "INSERT INTO cases 
        (respondent_name, respondent_advocate, role, case_no, filing_no, register_no, case_no_report, case_type, filing_date, register_date, description, case_stage, file_category, first_hearing_date, case_priority, court_detail, court_type, court, judge_name, remarks, lawyer_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssi", 
            $respondentName, $respondentAdvocate, $role, $caseNo, $filingNo, $registerNo, 
            $caseNoReport, $caseType, $filingDate, $registerDate, $description, $caseStage, 
            $fileCategory, $firstHearingDate, $casePriority, $courtDetail, $courtType, 
            $court, $judgeName, $remarks, $lawyer_id
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Case added successfully!";
        } else {
            $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Failed to prepare statement: " . mysqli_error($conn);
    }

    mysqli_close($conn);
    header("Location: ../../staff/case_add.php");
    exit();
} else {
    header("Location: ../../staff/case_add.php");
    exit();
}

?>