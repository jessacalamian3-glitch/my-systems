<?php
// logout.php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to portal selection page
header("Location: student_login.php");
exit();
?>