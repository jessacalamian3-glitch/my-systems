<?php
session_start();

// DEBUG: I-print ang lahat ng POST data
error_log("=== LOGIN ATTEMPT ===");
error_log("POST data: " . print_r($_POST, true));

$users = [
    'student' => [
        '2020-12345' => [
            'password' => 'password',
            'name' => 'Juan Dela Cruz',
            'course' => 'BS Computer Science',
            'year' => '3rd Year'
        ]
    ],
    'faculty' => [
        'prof_reyes' => [
            'password' => 'password', 
            'name' => 'Dr. Maria Reyes',
            'department' => 'College of Computer Studies',
            'position' => 'Professor'
        ]
    ]
];

if ($_POST) {
    $user_type = $_POST['user_type'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    error_log("User Type: $user_type");
    error_log("Username: $username");
    error_log("Password: $password");
    
    // Check if user exists
    if (isset($users[$user_type][$username]) && $users[$user_type][$username]['password'] === $password) {
        // SET SESSION VARIABLES
        $_SESSION['loggedin'] = true;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['username'] = $username;
        $_SESSION['user_info'] = $users[$user_type][$username];
        
        error_log("SESSION SET: " . print_r($_SESSION, true));
        
        // CLEAR ANY OUTPUT BUFFER
        ob_clean();
        
        // REDIRECT - DAPAT TAMA ANG USER TYPE
        if ($user_type === 'student') {
            error_log("REDIRECTING TO STUDENT DASHBOARD");
            header("Location: student_dashboard.php");
            exit();
        } elseif ($user_type === 'faculty') {
            error_log("REDIRECTING TO FACULTY DASHBOARD"); 
            header("Location: faculty_dashboard.php");
            exit();
        }
    } else {
        error_log("INVALID CREDENTIALS");
        $_SESSION['error'] = "Invalid username or password!";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>