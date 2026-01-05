<?php
// test_db.php
echo "Testing database connection...<br>";

try {
    require_once 'config/database.php';
    echo "✅ Database connected successfully!<br>";
    
    // Test faculty query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM faculty");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Faculty table exists with " . $result['count'] . " records<br>";
    
    // Test classes query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM classes");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Classes table exists with " . $result['count'] . " records<br>";
    
    // Test students query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Students table exists with " . $result['count'] . " records<br>";
    
    // Test enrollments query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Enrollments table exists with " . $result['count'] . " records<br>";
    
} catch(PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}
?>