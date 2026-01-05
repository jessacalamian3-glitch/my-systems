<?php
echo "<h3>STEP 1: Database Check</h3>";
echo "Checking if database exists...<br><br>";

$host = "localhost";
$user = "root";
$pass = "";

try {
    // Try to connect to MySQL server
    $temp_pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "✅ Connected to MySQL server<br>";
    
    // Check if database exists
    $stmt = $temp_pdo->query("SHOW DATABASES LIKE 'msubuug_db'");
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Database 'msubuug_db' EXISTS<br>";
    } else {
        echo "❌ Database 'msubuug_db' DOES NOT EXIST<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Cannot connect to MySQL server: " . $e->getMessage() . "<br>";
    echo "Please check if XAMPP MySQL is running.";
}
?>