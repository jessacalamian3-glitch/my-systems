<?php
// test_enrollment.php
session_start();

// Simple connection
$host = "localhost";
$dbname = "msubuug_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>✅ Database Connected Successfully</h3>";
    
    // Test 1: Check all tables
    echo "<h4>📊 Table Status:</h4>";
    $tables = ['enrollment', 'student_subjects', 'class_schedule', 'students', 'courses', 'subjects'];
    
    foreach($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $conn->query($sql);
        if($stmt->rowCount() > 0) {
            // Count records
            $sql2 = "SELECT COUNT(*) as count FROM $table";
            $stmt2 = $conn->query($sql2);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            echo "<p>✅ $table: " . $row['count'] . " records</p>";
        } else {
            echo "<p style='color:red'>❌ $table: NOT FOUND</p>";
        }
    }
    
    // Test 2: Try a sample insert
    echo "<h4>🧪 Test Enrollment:</h4>";
    
    if(isset($_POST['test'])) {
        $student_id = $_POST['student_id'];
        $course_code = $_POST['course_code'];
        $instructor = $_POST['instructor'];
        
        // Insert to enrollment
        $sql = "INSERT INTO enrollment (student_id, schedule, instructor, status, academic_year, semester) 
                VALUES ('$student_id', 'MWF', '$instructor', 'Active', '2024-2025', '2nd Semester')";
        
        if($conn->exec($sql)) {
            $enrollment_id = $conn->lastInsertId();
            echo "<p style='color:green'>✅ Inserted to enrollment table. ID: $enrollment_id</p>";
            
            // Insert to student_subjects
            $sql2 = "INSERT INTO student_subjects (enrollment_id, student_id, schedule, instructor, status) 
                     VALUES ('$enrollment_id', '$student_id', 'MWF', '$instructor', 'Enrolled')";
            $conn->exec($sql2);
            echo "<p style='color:green'>✅ Inserted to student_subjects table</p>";
            
            // Insert to class_schedule
            $sql3 = "INSERT INTO class_schedule (enrollment_id, day_of_week, start_time, end_time, room, instructor) 
                     VALUES ('$enrollment_id', 'Monday', '08:00:00', '09:30:00', 'Room 101', '$instructor')";
            $conn->exec($sql3);
            echo "<p style='color:green'>✅ Inserted to class_schedule table</p>";
            
            echo "<h5 style='color:green'>🎉 TEST SUCCESSFUL! All 3 tables updated.</h5>";
        } else {
            echo "<p style='color:red'>❌ Insert failed</p>";
        }
    }
    
    // Test form
    echo "<form method='POST' style='background:#f0f0f0; padding:20px; border-radius:10px;'>
            <h5>Test Enrollment Form:</h5>
            Student ID: <input type='text' name='student_id' value='2023-0001' class='form-control mb-2'><br>
            Course Code: <input type='text' name='course_code' value='GEC102' class='form-control mb-2'><br>
            Instructor: <input type='text' name='instructor' value='Dr. Test' class='form-control mb-2'><br>
            <button type='submit' name='test' class='btn btn-primary'>Run Test</button>
          </form>";
    
    // Test 3: Check recent data
    echo "<h4>📝 Recent Data:</h4>";
    foreach($tables as $table) {
        $sql = "SELECT * FROM $table ORDER BY 1 DESC LIMIT 3";
        $stmt = $conn->query($sql);
        $count = $stmt->rowCount();
        
        echo "<p><strong>$table</strong> (Last 3 records): $count found</p>";
    }
    
} catch(PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>

<hr>
<a href="enrollment_management.php" class="btn btn-success">Go to Enrollment Management</a>