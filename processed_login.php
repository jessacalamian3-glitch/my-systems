<?php
// I-enable ang error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $user_type = trim($_POST["user_type"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // Simple validation
    if(empty($username) || empty($password)){
        header("Location: login.php?error=empty_fields");
        exit();
    }
    
    // Check credentials (simplified for testing)
    $valid_student = ($username === "2020-12345" && $password === "password");
    $valid_faculty = ($username === "prof_reyes" && $password === "password");
    
    if(($user_type === "student" && $valid_student) || 
       ($user_type === "faculty" && $valid_faculty)){
        
        // Set session variables
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;
        $_SESSION["user_type"] = $user_type;
        $_SESSION["full_name"] = $user_type === "student" ? "Juan Dela Cruz" : "Dr. Maria Reyes";
        
        // Redirect based on user type
        if($user_type === "faculty"){
            header("Location: faculty_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
        
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit();
    }
    
} else {
    header("Location: login.php");
    exit();
}
?>