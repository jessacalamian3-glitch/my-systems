<?php
echo "<h3>STEP 2: Checking Database Tables</h3>";
echo "Database: msubuug_db<br><br>";

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "msubuug_db";

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    echo "✅ Connected to database '$dbname'<br><hr>";
    
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>Total tables found:</strong> " . count($tables) . "<br><br>";
    
    if (count($tables) > 0) {
        echo "<strong>List of tables:</strong><br>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul><hr>";
        
        // Check for SPECIFIC tables na kailangan ng portal mo
        $required_tables = ['latest_updates', 'upcoming_events', 'announcements'];
        
        echo "<strong>Checking required tables:</strong><br>";
        foreach ($required_tables as $table) {
            if (in_array($table, $tables)) {
                // Check if table has data
                $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
                echo "✅ $table - EXISTS ($count records)<br>";
            } else {
                echo "❌ $table - MISSING<br>";
            }
        }
    } else {
        echo "⚠ Database exists but has NO TABLES.<br>";
        echo "You need to create the tables for your portal to work.";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>