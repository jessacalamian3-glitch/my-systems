<?php
session_start();
echo "<h1>Admin Area - Test Page</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "<a href='logout.php'>Logout</a>";
?>