<?php
// test_config.php
echo "<h2>Checking Database Configuration</h2>";

// Check if config file exists
if (!file_exists('config/database.php')) {
    echo "<p style='color: red;'>✗ config/database.php file does not exist</p>";
} else {
    echo "<p style='color: green;'>✓ config/database.php file exists</p>";
    
    // Try to include the config file
    try {
        require_once 'config/database.php';
        echo "<p style='color: green;'>✓ Config file loaded successfully</p>";
        
        // Check if $pdo variable is set
        if (isset($pdo)) {
            echo "<p style='color: green;'>✓ \$pdo variable is set</p>";
            
            // Test the connection
            if ($pdo !== false) {
                try {
                    $pdo->query("SELECT 1");
                    echo "<p style='color: green;'>✓ Database connection successful!</p>";
                } catch (Exception $e) {
                    echo "<p style='color: red;'>✗ Database test query failed: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ \$pdo is set to FALSE</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ \$pdo variable is NOT set</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error loading config file: " . $e->getMessage() . "</p>";
    }
}

// Show current directory structure
echo "<h3>Current Directory:</h3>";
echo "<pre>" . getcwd() . "</pre>";

// Show if config directory exists
echo "<h3>Config Directory:</h3>";
if (is_dir('config')) {
    echo "<p style='color: green;'>✓ config/ directory exists</p>";
    $files = scandir('config');
    echo "Files in config/: " . implode(', ', $files);
} else {
    echo "<p style='color: red;'>✗ config/ directory does not exist</p>";
}
?>