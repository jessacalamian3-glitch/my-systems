<?php
// emergency_fix.php - This will fix your broken website

// Step 1: Check what tables exist
$conn = mysqli_connect('localhost', 'root', '', 'msubuug_db');

if (!$conn) {
    die("Database connection failed. Check XAMPP.");
}

echo "<h3>Checking Database...</h3>";

// List all tables
$tables = [];
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_array($result)) {
    $tables[] = $row[0];
}

echo "Found tables: " . implode(', ', $tables) . "<br><br>";

// If missing tables, create them
if (!in_array('fees', $tables)) {
    echo "Creating fees table...<br>";
    mysqli_query($conn, "CREATE TABLE fees (
        fee_id INT AUTO_INCREMENT PRIMARY KEY,
        fee_name VARCHAR(255),
        amount DECIMAL(10,2),
        due_date DATE,
        fee_type VARCHAR(100),
        academic_year VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created fees table.<br>";
}

if (!in_array('fines', $tables)) {
    echo "Creating fines table...<br>";
    mysqli_query($conn, "CREATE TABLE fines (
        fine_id INT AUTO_INCREMENT PRIMARY KEY,
        fine_name VARCHAR(255),
        amount DECIMAL(10,2),
        event_date DATE,
        due_date DATE,
        fine_type VARCHAR(100),
        description TEXT,
        academic_year VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created fines table.<br>";
}

echo "<h3>Database check complete!</h3>";
echo "<a href='financial_management_simple.php'>Go to Financial Management</a>";
?>