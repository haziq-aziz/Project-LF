<?php

session_start();
require_once('../db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Process password change if requested
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $password_change = false;
    
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } else {
            // Verify current password
            $pwd_stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
            $pwd_stmt->bind_param("i", $user_id);
            $pwd_stmt->execute();
            $pwd_result = $pwd_stmt->get_result();
            $user_pwd = $pwd_result->fetch_assoc();
            
            if (!password_verify($current_password, $user_pwd['password'])) {
                $errors[] = "Current password is incorrect";
            }
        }
        
        if (empty($new_password)) {
            $errors[] = "New password cannot be empty";
        } else if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } else if (strlen($new_password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        } else {
            $password_change = true;
        }
    }
    
    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['name'] !== '') {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading file. Error code: " . $file['error'];
        } else if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG and GIF are allowed.";
        } else if ($file['size'] > $max_size) {
            $errors[] = "File size too large. Maximum size is 2MB.";
        } else {
            $upload_dir = '../../uploads/profile_picture/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = uniqid() . '_' . basename($file['name']);
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $profile_picture = $file_name;
                
                // Delete old profile picture if not default
                $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id=?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_pic = $result->fetch_assoc();
                
                if ($old_pic['profile_picture'] && $old_pic['profile_picture'] !== 'default.jpg' && 
                    file_exists('../../uploads/profile_picture/' . $old_pic['profile_picture'])) {
                    unlink('../../uploads/profile_picture/' . $old_pic['profile_picture']);
                }
            } else {
                $errors[] = "Failed to upload file.";
            }
        }
    }
    
    // Update user information if no errors
    if (empty($errors)) {
        // Start with basic update
        $sql = "UPDATE users SET name=?, email=?, phone=?, address=?";
        $types = "ssss";
        $params = [$name, $email, $phone, $address];
        
        // Add profile picture if changed
        if ($profile_picture) {
            $sql .= ", profile_picture=?";
            $types .= "s";
            $params[] = $profile_picture;
        }
        
        // Add password if changed
        if ($password_change) {
            $sql .= ", password=?";
            $types .= "s";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        // Complete the query
        $sql .= " WHERE id=?";
        $types .= "i";
        $params[] = $user_id;
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            // Update session values
            $_SESSION['name'] = $name;
            $_SESSION['success_message'] = "Profile updated successfully!";
            header('Location: ../../staff/profile.php');
            exit();
        } else {
            $errors[] = "Error updating profile: " . $conn->error;
            $_SESSION['profile_errors'] = $errors;
            header('Location: ../../staff/profile.php');
            exit();
        }
    } else {
        // Store errors in session and redirect
        $_SESSION['profile_errors'] = $errors;
        header('Location: ../../staff/profile.php');
        exit();
    }
} else {
    // Not a POST request, redirect
    header('Location: ../../staff/profile.php');
    exit();
}