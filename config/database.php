<?php
// config/database.php

$host = "localhost";
$dbname = "msubuug_db";
$username = "root";
$password = "";

try {
    // Create the main PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Also set $conn for compatibility with other scripts
    $conn = $pdo;
    
} catch(PDOException $e) {
    // Try to create database if it doesn't exist
    try {
        $temp_pdo = new PDO("mysql:host=$host", $username, $password);
        $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        
        // Reconnect with database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn = $pdo;
        
    } catch(PDOException $e2) {
        die("Cannot connect to database. Please check your MySQL server.");
    }
}

// Make sure $pdo is available globally
if (!isset($pdo)) {
    die("Database connection failed. Please check configuration.");
}
?>
