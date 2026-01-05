<?php
echo "<h3>STEP 3: Checking Current Database Configuration</h3>";

// Check if config/database.php exists
if (file_exists('config/database.php')) {
    echo "✅ config/database.php EXISTS<br>";
    
    // Include it
    include 'config/database.php';
    
    // Check what variables are set
    echo "<br><strong>Variables set:</strong><br>";
    
    if (isset($pdo)) {
        echo "✅ \$pdo is SET<br>";
        
        // Test if it works
        try {
            $result = $pdo->query("SELECT DATABASE() as db");
            $data = $result->fetch();
            echo "✅ Connected to database: <strong>" . $data['db'] . "</strong><br>";
        } catch(Exception $e) {
            echo "❌ \$pdo query error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ \$pdo is NOT SET<br>";
    }
    
    if (isset($conn)) {
        echo "✅ \$conn is SET<br>";
    } else {
        echo "❌ \$conn is NOT SET<br>";
    }
    
    // Show first few lines of the file
    echo "<br><strong>First 10 lines of config/database.php:</strong><br>";
    $lines = file('config/database.php');
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo htmlspecialchars($lines[$i]) . "<br>";
    }
    
} else {
    echo "❌ config/database.php NOT FOUND<br>";
    echo "You need to create this file.";
}
?>