<?php
session_start();

// Check if session variables are set
echo "<h1>Session Debug Dashboard</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if we're logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo "<div style='background: green; color: white; padding: 10px;'>✓ LOGGED IN SUCCESSFULLY</div>";
    
    // Display student info if available
    if (isset($_SESSION['user_info'])) {
        echo "<h2>Student Information:</h2>";
        echo "<p><strong>Name:</strong> " . ($_SESSION['user_info']['name'] ?? 'Not set') . "</p>";
        echo "<p><strong>Email:</strong> " . ($_SESSION['user_info']['email'] ?? 'Not set') . "</p>";
        echo "<p><strong>Course:</strong> " . ($_SESSION['user_info']['course'] ?? 'Not set') . "</p>";
        echo "<p><strong>Year Level:</strong> " . ($_SESSION['user_info']['year_level'] ?? 'Not set') . "</p>";
    }
    
    echo "<p><strong>Username:</strong> " . ($_SESSION['username'] ?? 'Not set') . "</p>";
    echo "<p><strong>User Type:</strong> " . ($_SESSION['user_type'] ?? 'Not set') . "</p>";
    
    echo '<br><a href="student-dashboards.php">Go to Real Dashboard</a>';
} else {
    echo "<div style='background: red; color: white; padding: 10px;'>✗ NOT LOGGED IN</div>";
    echo '<br><a href="test_login.php">Go to Test Login</a>';
}
?>