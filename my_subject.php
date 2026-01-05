<?php
// my_subject.php - UPDATED WITH CONTAINER/CARD VIEW FOR AVAILABLE SUBJECTS

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection - SAME AS BEFORE
class Database {
    private $host = "localhost";
    private $db_name = "msubuug_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// Function to get student data - SAME AS BEFORE
function getStudentData($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT * FROM students WHERE student_id = :student_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

// Check if user is logged in - SAME AS BEFORE
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);
$success_message = '';
$error_message = '';

// Handle enrollment request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_class_id'])) {
    $class_id = $_POST['enroll_class_id'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $check_stmt = $db->prepare("SELECT * FROM enrollments WHERE student_id = ? AND class_id = ?");
            $check_stmt->execute([$student_id, $class_id]);
            
            if ($check_stmt->rowCount() === 0) {
                $enroll_stmt = $db->prepare("INSERT INTO enrollments (student_id, class_id, enrollment_date, status) VALUES (?, ?, NOW(), 'Active')");
                $enroll_result = $enroll_stmt->execute([$student_id, $class_id]);
                
                if ($enroll_result) {
                    $update_stmt = $db->prepare("UPDATE classes SET current_enrollment = current_enrollment + 1 WHERE class_id = ?");
                    $update_stmt->execute([$class_id]);
                    $success_message = "Successfully enrolled in the subject!";
                }
            } else {
                $error_message = "You are already enrolled in this subject.";
            }
        }
    } catch(PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Handle drop subject request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drop_class_id'])) {
    $class_id = $_POST['drop_class_id'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $drop_stmt = $db->prepare("DELETE FROM enrollments WHERE student_id = ? AND class_id = ?");
            $drop_result = $drop_stmt->execute([$student_id, $class_id]);
            
            if ($drop_result && $drop_stmt->rowCount() > 0) {
                $update_stmt = $db->prepare("UPDATE classes SET current_enrollment = current_enrollment - 1 WHERE class_id = ?");
                $update_stmt->execute([$class_id]);
                $success_message = "Successfully dropped the subject!";
            }
        }
    } catch(PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Fetch student data and set session info - SAME AS BEFORE
if ($student_data) {
    $course_name = $student_data['course'];
    $_SESSION['user_info'] = [
        'name' => $student_data['first_name'] . ' ' . $student_data['last_name'],
        'course' => $course_name,
        'year_level' => $student_data['year_level'] . (isset($student_data['year_level']) ? ' Year' : ''),
        'email' => $student_data['email']
    ];
} else {
    if (!isset($_SESSION['user_info'])) {
        $_SESSION['user_info'] = [
            'name' => 'Student Name',
            'course' => 'Course Not Set', 
            'year_level' => 'Year Level Not Set',
            'email' => 'email@example.com'
        ];
    }
}

$student_info = $_SESSION['user_info'];

// Function to get student's current enrolled subjects
function getCurrentSubjects($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    e.enrollment_id,
                    c.class_id,
                    s.subject_code,
                    s.subject_name,
                    s.units,
                    c.section,
                    CONCAT(f.first_name, ' ', f.last_name) as instructor,
                    e.enrollment_date,
                    e.status as enrollment_status,
                    GROUP_CONCAT(DISTINCT CONCAT(cs.day, ' ', TIME_FORMAT(cs.start_time, '%h:%i %p'), '-', TIME_FORMAT(cs.end_time, '%h:%i %p')) SEPARATOR ', ') as schedule,
                    GROUP_CONCAT(DISTINCT cs.room) as rooms
                  FROM enrollments e
                  JOIN classes c ON e.class_id = c.class_id
                  JOIN subjects s ON c.subject_id = s.subject_id
                  JOIN faculty f ON c.faculty_id = f.faculty_id
                  LEFT JOIN class_schedule cs ON c.class_id = cs.class_id
                  WHERE e.student_id = :student_id 
                  AND e.status = 'Active'
                  AND c.status = 'active'
                  GROUP BY c.class_id, s.subject_code, s.subject_name, s.units, c.section, f.first_name, f.last_name, e.enrollment_date, e.status
                  ORDER BY s.subject_code";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get available subjects for enrollment
function getAvailableSubjects($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    c.class_id,
                    s.subject_code,
                    s.subject_name,
                    s.units,
                    c.section,
                    c.max_students,
                    c.current_enrollment,
                    (c.max_students - c.current_enrollment) as available_slots,
                    CONCAT(f.first_name, ' ', f.last_name) as instructor,
                    c.academic_year,
                    c.semester
                  FROM classes c
                  JOIN subjects s ON c.subject_id = s.subject_id
                  JOIN faculty f ON c.faculty_id = f.faculty_id
                  WHERE c.status = 'active'
                  AND c.academic_year = '2023-2024'
                  AND c.semester = '2nd Semester'
                  AND c.class_id NOT IN (
                      SELECT class_id FROM enrollments 
                      WHERE student_id = :student_id AND status = 'Active'
                  )
                  AND c.current_enrollment < c.max_students
                  ORDER BY s.subject_code, c.section";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Get current and available subjects
$current_subjects = getCurrentSubjects($student_id);
$available_subjects = getAvailableSubjects($student_id);

// Helper functions - SAME AS BEFORE
function getDisplayName($info) {
    if (!$info) return 'Student';
    return htmlspecialchars($info['name'] ?? 'Student');
}

function getFirstLetter($info) {
    if (!$info) return 'S';
    $name = $info['name'] ?? '';
    return !empty($name) ? strtoupper(substr($name, 0, 1)) : 'S';
}

function getStudentField($student_data, $field, $default = 'Not set') {
    if (!$student_data || !isset($student_data[$field]) || $student_data[$field] === '' || $student_data[$field] === null) {
        return $default;
    }
    return htmlspecialchars($student_data[$field]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* EXACT SAME STYLES AS BEFORE FOR CONSISTENCY */
        .msu-maroon { color: #800000; }
        .msu-maroon-dark { color: #5a0000; }
        .msu-maroon-light { color: #a30000; }
        .msu-gold { color: #FFD700; }
        
        .bg-msu-maroon { background-color: #800000; }
        .bg-msu-maroon-dark { background-color: #5a0000; }
        .bg-msu-maroon-light { background-color: #a30000; }
        .bg-msu-gold { background-color: #FFD700; }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* ===== MOBILE FIRST STYLES - EXACT SAME ===== */
        
        /* NAVIGATION */
        .navbar {
            background: #800000 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }
        
        .nav-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.1rem;
        }
        
        /* SIDEBAR - MOBILE FIRST (HIDDEN BY DEFAULT) */
        .sidebar {
            background: #5a0000;
            min-height: 100vh;
            position: fixed;
            width: 280px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            left: -280px;
            transition: all 0.3s ease;
            top: 0;
            padding-top: 70px;
        }
        
        .sidebar.show {
            left: 0;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
        
        .main-content {
            padding: 20px 15px;
            background: #f8f9fa;
            min-height: 100vh;
            margin-top: 70px;
            width: 100%;
        }
        
        .sidebar .nav-link {
            color: white;
            padding: 15px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 0.95rem;
        }
        
        .sidebar .nav-link:hover {
            background: #a30000;
            border-left-color: #FFD700;
            color: #FFD700;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: #a30000;
            border-left-color: #FFD700;
            color: #FFD700;
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        
        /* MOBILE MENU TOGGLE */
        .mobile-menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-toggle:hover {
            color: #FFD700;
            transform: scale(1.1);
        }
        
        /* CARDS - SAME AS DASHBOARD AND PROFILE */
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 4px solid #800000;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .dashboard-card:hover::before {
            left: 100%;
        }
        
        /* SUBJECT CONTAINER CARD - NEW DESIGN */
        .subject-container-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid #e0e0e0;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .subject-container-card:hover {
            transform: translateY(-5px);
            border-color: #800000;
            box-shadow: 0 8px 25px rgba(128,0,0,0.15);
        }
        
        .subject-container-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,215,0,0.1), transparent);
            transition: left 0.5s;
        }
        
        .subject-container-card:hover::before {
            left: 100%;
        }
        
        /* SUBJECT ITEM CARD - FOR AVAILABLE SUBJECTS */
        .subject-item-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .subject-item-card:hover {
            border-color: #800000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        
        .subject-item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #800000;
            border-radius: 4px 0 0 4px;
        }
        
        .subject-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .subject-code {
            font-size: 1.1rem;
            font-weight: 700;
            color: #800000;
            margin-bottom: 0.25rem;
        }
        
        .subject-name {
            font-size: 0.95rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .subject-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #555;
        }
        
        .meta-icon {
            color: #800000;
            width: 20px;
            text-align: center;
        }
        
        .meta-value {
            font-weight: 600;
            color: #333;
        }
        
        .slots-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px dashed #e0e0e0;
            margin-top: 1rem;
        }
        
        .slots-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .slots-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .slots-available {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .slots-full {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .enroll-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .enroll-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .enroll-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .enroll-btn:disabled:hover {
            background: #6c757d;
            transform: none;
        }
        
        /* PERFECT CIRCLE USER AVATAR FOR NAVIGATION */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFD700;
            transition: all 0.3s ease;
            background: #800000;
        }
        
        .user-avatar:hover {
            transform: scale(1.1);
            border-color: white;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .letter-avatar {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        /* BUTTONS - SAME AS DASHBOARD */
        .btn-msu {
            background: #800000;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn-msu:hover {
            background: #a30000;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128,0,0,0.3);
        }
        
        .btn-msu::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-msu:hover::before {
            left: 100%;
        }
        
        .btn-msu-sm {
            background: #800000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .btn-msu-sm:hover {
            background: #a30000;
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-danger-sm {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .btn-danger-sm:hover {
            background: #c82333;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220,53,69,0.3);
        }
        
        /* TABLE STYLING - FOR CURRENT SUBJECTS ONLY */
        .table-msu {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: none;
        }
        
        .table-msu thead {
            background: #800000;
            color: white;
            font-weight: 600;
            border: none;
        }
        
        .table-msu thead th {
            padding: 15px 12px;
            border: none;
            font-size: 0.9rem;
        }
        
        .table-msu tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table-msu tbody tr:hover {
            background: #fff8f8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(128,0,0,0.1);
        }
        
        .table-msu tbody td {
            padding: 14px 12px;
            vertical-align: middle;
            color: #333;
            border: none;
        }
        
        .table-msu tbody tr:last-child {
            border-bottom: none;
        }
        
        /* BADGES - MATCHING DESIGN */
        .badge-msu {
            background: #800000;
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-secondary {
            background: #6c757d;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-danger {
            background: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* SECTION TITLE - SAME AS PROFILE */
        .section-title {
            border-left: 4px solid #800000;
            padding-left: 15px;
            margin: 25px 0 20px 0;
            color: #800000;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .module-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .mobile-user-info {
            display: none;
            background: #5a0000;
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        /* EMPTY STATE - MATCHING DESIGN */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        
        .empty-state p {
            font-size: 0.95rem;
            margin-bottom: 0;
        }
        
        /* ===== DESKTOP STYLES (992px and up) - SAME AS DASHBOARD ===== */
        @media (min-width: 992px) {
            .sidebar {
                position: fixed;
                left: 0;
                width: 250px;
                padding-top: 70px;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
                padding: 30px;
                margin-top: 70px;
            }
            
            .mobile-menu-toggle {
                display: none;
            }
            
            .sidebar-overlay {
                display: none !important;
            }
            
            .mobile-user-info {
                display: block;
            }
            
            .nav-brand {
                font-size: 1.4rem;
            }
            
            .dashboard-card {
                padding: 2rem;
            }
            
            .subject-container-card {
                padding: 2rem;
            }
        }

        /* ===== LARGE DESKTOP STYLES (1200px and up) ===== */
        @media (min-width: 1200px) {
            .main-content {
                padding: 40px;
            }
            
            .table-msu thead th {
                padding: 18px 15px;
            }
            
            .table-msu tbody td {
                padding: 16px 15px;
            }
        }

        /* ===== EXTRA SMALL DEVICES (phones, 480px and down) ===== */
        @media (max-width: 480px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .dashboard-card {
                padding: 1rem;
                margin-bottom: 15px;
            }
            
            .subject-container-card {
                padding: 1rem;
            }
            
            .subject-item-card {
                padding: 1rem;
            }
            
            .sidebar {
                width: 260px;
                left: -260px;
            }
            
            .table-msu thead th,
            .table-msu tbody td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
            
            .btn-danger-sm {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .subject-meta {
                gap: 0.5rem;
            }
            
            .meta-item {
                font-size: 0.8rem;
            }
            
            .slots-container {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .enroll-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar - EXACTLY SAME AS BEFORE -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle d-lg-none" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand nav-brand" href="student-dashboard.php">
                <i class="fas fa-user-graduate me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Student Portal</span>
                <span class="d-inline d-sm-none">Student Portal</span>
            </a>
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <!-- PERFECT CIRCLE PROFILE PICTURE IN NAVIGATION -->
                        <?php
                        $profile_pic_path = $student_data['profile_picture'] ?? null;
                        $first_letter = getFirstLetter($student_info);
                        ?>
                        
                        <div class="user-avatar me-2">
                            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" 
                                      alt="Profile">
                            <?php else: ?>
                                <div class="letter-avatar">
                                    <?php echo $first_letter; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-none d-md-block text-white">
                            <strong><?php echo getDisplayName($student_info); ?></strong><br>
                            <small>Student</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="student-dashboard.php"><i class="fas fa-tachometer-alt me-2 text-msu"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="student_profile.php"><i class="fas fa-user me-2 text-msu"></i>My Profile</a></li>
                        <li><a class="dropdown-item active" href="my_subject.php"><i class="fas fa-book me-2 text-msu"></i>My Subjects</a></li>
                        <li><a class="dropdown-item" href="student_settings.php"><i class="fas fa-cog me-2 text-msu"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="student_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay - SAME AS BEFORE -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Layout - SAME STRUCTURE AS BEFORE -->
    <div class="d-flex">
        <!-- Sidebar - EXACTLY SAME AS BEFORE -->
        <div class="sidebar">
            <!-- Mobile User Info -->
            <div class="mobile-user-info d-lg-none">
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">
                        <?php echo getFirstLetter($student_info); ?>
                    </div>
                    <div>
                        <strong><?php echo getDisplayName($student_info); ?></strong><br>
                        <small>Student ID: <?php echo htmlspecialchars($student_id); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="d-flex flex-column pt-3">
                <a href="student_dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="my_subject.php" class="nav-link active">
                    <i class="fas fa-book"></i> My Subjects
                </a>
                <a href="student_grades.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Grades & Progress
                </a>
                <a href="class_schedule.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i> Class Schedule
                </a>
                <a href="student_fees.php" class="nav-link">
                    <i class="fas fa-file-invoice-dollar"></i> Fees & Payments
                </a>
                <a href="student_fines.php" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i> Fines & Penalties
                </a>
                <a href="academic.php" class="nav-link">
                    <i class="fas fa-file-alt"></i> Academic Curriculum
                </a>
               
                <a href="student_support.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Notifications
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Messages - SAME DESIGN AS BEFORE -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Module Header -->
            <div class="module-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="section-title mb-2">
                            <i class="fas fa-book me-2"></i>My Subjects
                        </h4>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user-graduate me-1"></i>
                            <?php echo htmlspecialchars($student_info['name']); ?> | 
                            <i class="fas fa-id-card ms-2 me-1"></i>
                            <?php echo htmlspecialchars($student_id); ?> | 
                            <i class="fas fa-graduation-cap ms-2 me-1"></i>
                            <?php echo getStudentField($student_data, 'course'); ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <span class="badge-msu">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('F j, Y'); ?>
                        </span>
                        <?php if (count($current_subjects) > 0): ?>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-check-circle"></i>
                                <?php echo count($current_subjects); ?> Enrolled
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Current Enrolled Subjects Card - TABLE VIEW -->
            <div class="dashboard-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2 text-msu"></i>
                        Current Enrolled Subjects
                    </h5>
                    <div>
                        <span class="badge bg-msu-maroon">
                            <i class="fas fa-weight-hanging"></i>
                            Total Units: 
                            <?php 
                            $total_units = 0;
                            foreach ($current_subjects as $subject) {
                                $total_units += (int)$subject['units'];
                            }
                            echo $total_units;
                            ?>
                        </span>
                        <span class="badge-msu ms-2">
                            <i class="fas fa-book-open"></i>
                            <?php echo count($current_subjects); ?> Subjects
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($current_subjects)): ?>
                    <div class="table-responsive">
                        <table class="table table-msu table-hover">
                            <thead>
                                <tr>
                                    <th><i class=></i>Subject Code</th>
                                    <th><i class=></i>Subject Name</th>
                                    <th><i class=></i>Units</th>
                                    <th><i class=></i>Section</th>
                                    <th><i class=></i>Instructor</th>
                                    <th><i class=></i>Schedule</th>
                                    <th><i class=></i>Room</th>
                                   <th><i class=></i>Action</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($current_subjects as $subject): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-msu"><?php echo htmlspecialchars($subject['subject_code']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo htmlspecialchars($subject['units']); ?> unit<?php echo $subject['units'] > 1 ? 's' : ''; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo htmlspecialchars($subject['section']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($subject['instructor']); ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($subject['schedule'] ?? 'TBA'); ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($subject['rooms'] ?? 'TBA'); ?></small>
                                        </td>
                                        <td>
                                            
                                                
                                           
                                        
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirmDropSubject();">
                                                <input type="hidden" name="drop_class_id" value="<?php echo $subject['class_id']; ?>">
                                                <button type="submit" class="btn btn-danger-sm">
                                                    <i class="fas fa-trash-alt me-1"></i>Drop
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open text-muted"></i>
                        <h6 class="mt-3 mb-2 text-muted">No Enrolled Subjects</h6>
                        <p class="text-muted mb-0">You haven't enrolled in any subjects for this semester.</p>
                    </div>
                <?php endif; ?>
            </div>

          

    <!-- JavaScript - SAME AS BEFORE -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle - SAME AS DASHBOARD
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            
            if (sidebar.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Close sidebar when clicking on overlay
        document.querySelector('.sidebar-overlay').addEventListener('click', function() {
            toggleSidebar();
        });

        // Auto-close sidebar when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        // Set active nav link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });

        // Confirm before dropping subject
        function confirmDropSubject() {
            return confirm('Are you sure you want to drop this subject? This action cannot be undone.');
        }
        
        // Confirm before enrolling in subject
        function confirmEnrollSubject() {
            return confirm('Are you sure you want to enroll in this subject?');
        }
        
        // Add click effect to subject cards
        document.addEventListener('DOMContentLoaded', function() {
            const subjectCards = document.querySelectorAll('.subject-item-card');
            
            subjectCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking on the enroll button
                    if (e.target.closest('.enroll-btn')) {
                        return;
                    }
                    
                    // Find the enroll button in this card
                    const enrollBtn = this.querySelector('.enroll-btn');
                    if (enrollBtn && !enrollBtn.disabled) {
                        enrollBtn.click();
                    }
                });
                
                // Add pointer cursor to indicate it's clickable
                if (!card.querySelector('.enroll-btn') || !card.querySelector('.enroll-btn').disabled) {
                    card.style.cursor = 'pointer';
                }
            });
        });
    </script>
</body>
</html>