<?php
session_start();
$_SESSION['test'] = 'working';
echo "Session ID: " . session_id() . "<br>";
echo "Session test value: " . $_SESSION['test'] . "<br>";
print_r($_SESSION);