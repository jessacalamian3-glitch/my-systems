<?php
// grades_management.php - CORRECTED GRADES MANAGEMENT MODULE
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// STRICT SESSION VALIDATION - ADMIN ONLY
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// STRICT ADMIN ACCESS CONTROL
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    if (isset($_SESSION['user_type'])) {
        switch ($_SESSION['user_type']) {
            case 'student':
                header("Location: student_dashboard.php");
                exit();
            case 'faculty':
                header("Location: faculty_dashboard.php");
                exit();
            default:
                header("Location: index.php");
                exit();
        }
    } else {
        header("Location: index.php");
        exit();
    }
}

// Database connection
$host = 'localhost';
$db   = 'msubuug_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get admin info from session
$admin_info = [
    'name' => $_SESSION['user_info']['name'] ?? 'System Administrator',
    'role' => $_SESSION['user_info']['role'] ?? 'Admin',
    'username' => $_SESSION['username'] ?? 'admin'
];

// Function to get all students
function getAllStudents($pdo) {
    $query = "SELECT student_id, first_name, last_name, course, year_level 
              FROM students 
              ORDER BY last_name, first_name";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all subjects
function getAllSubjects($pdo) {
    $query = "SELECT subject_id, subject_code, subject_name, units 
              FROM subjects 
              ORDER BY subject_code";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// CORRECTED: Function to get all grades from grades table (where faculty actually stores grades)
function getAllGrades($pdo) {
    $query = "SELECT 
                g.grade_id,
                g.student_id,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                s.course as student_course,
                s.year_level,
                g.course_code,
                g.subject_name,
                g.instructor,
                g.prelim_grade,
                g.midterm_grade,
                g.final_grade,
                g.overall_grade,
                g.remarks,
                g.grade_date,
                g.created_at
              FROM grades g
              JOIN students s ON g.student_id = s.student_id
              ORDER BY g.grade_date DESC, g.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get grade equivalent (Philippine System)
function getGradeEquivalent($grade) {
    if ($grade >= 1.00 && $grade <= 1.25) return 'A';
    if ($grade >= 1.26 && $grade <= 1.50) return 'B+';
    if ($grade >= 1.51 && $grade <= 1.75) return 'B';
    if ($grade >= 1.76 && $grade <= 2.00) return 'C+';
    if ($grade >= 2.01 && $grade <= 2.25) return 'C';
    if ($grade >= 2.26 && $grade <= 2.50) return 'D';
    if ($grade >= 2.51 && $grade <= 2.75) return 'E';
    if ($grade >= 2.76 && $grade <= 3.00) return 'F';
    if ($grade >= 3.01 && $grade <= 4.00) return 'INC';
    if ($grade >= 4.01 && $grade <= 5.00) return '5.0';
    return 'NG'; // No Grade
}

// Function to get grade remarks
function getGradeRemarks($grade) {
    if ($grade >= 1.00 && $grade <= 3.00) return 'Passed';
    if ($grade >= 3.01 && $grade <= 4.00) return 'Incomplete';
    return 'Failed';
}

// Function to get all classes
function getAllClasses($pdo) {
    $query = "SELECT 
                c.class_id,
                s.subject_code,
                s.subject_name,
                c.section,
                CONCAT(f.first_name, ' ', f.last_name) as instructor
              FROM classes c
              JOIN subjects s ON c.subject_id = s.subject_id
              JOIN faculty f ON c.faculty_id = f.faculty_id
              WHERE c.status = 'active'
              ORDER BY s.subject_code, c.section";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// CORRECTED: Function to update grade in grades table
function updateGrade($pdo, $grade_id, $prelim, $midterm, $final, $overall, $remarks) {
    $query = "UPDATE grades 
              SET prelim_grade = :prelim,
                  midterm_grade = :midterm,
                  final_grade = :final,
                  overall_grade = :overall,
                  remarks = :remarks,
                  grade_date = NOW()
              WHERE grade_id = :grade_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':prelim', $prelim);
    $stmt->bindParam(':midterm', $midterm);
    $stmt->bindParam(':final', $final);
    $stmt->bindParam(':overall', $overall);
    $stmt->bindParam(':remarks', $remarks);
    $stmt->bindParam(':grade_id', $grade_id);
    
    return $stmt->execute();
}

// CORRECTED: Function to delete grade from grades table
function deleteGrade($pdo, $grade_id) {
    $query = "DELETE FROM grades WHERE grade_id = :grade_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':grade_id', $grade_id);
    return $stmt->execute();
}

// CORRECTED: Function to add grade to grades table
function addGradeToDatabase($pdo, $student_id, $class_id, $prelim, $midterm, $final, $overall, $remarks) {
    // First get class details
    $classQuery = "SELECT 
                    s.subject_code, 
                    s.subject_name,
                    CONCAT(f.first_name, ' ', f.last_name) as instructor
                   FROM classes c
                   JOIN subjects s ON c.subject_id = s.subject_id
                   JOIN faculty f ON c.faculty_id = f.faculty_id
                   WHERE c.class_id = :class_id";
    
    $classStmt = $pdo->prepare($classQuery);
    $classStmt->bindParam(':class_id', $class_id);
    $classStmt->execute();
    $classInfo = $classStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$classInfo) {
        return false;
    }
    
    // Check if grade already exists
    $checkQuery = "SELECT grade_id FROM grades 
                   WHERE student_id = :student_id 
                   AND course_code = :course_code";
    
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':student_id', $student_id);
    $checkStmt->bindParam(':course_code', $classInfo['subject_code']);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        // Update existing
        $query = "UPDATE grades 
                 SET subject_name = :subject_name,
                     instructor = :instructor,
                     prelim_grade = :prelim,
                     midterm_grade = :midterm,
                     final_grade = :final,
                     overall_grade = :overall,
                     remarks = :remarks,
                     grade_date = NOW()
                 WHERE student_id = :student_id 
                 AND course_code = :course_code";
    } else {
        // Insert new
        $query = "INSERT INTO grades 
                 (student_id, class_id, course_code, subject_name, instructor,
                  prelim_grade, midterm_grade, final_grade, overall_grade,
                  remarks, grade_date, created_at)
                 VALUES 
                 (:student_id, :class_id, :course_code, :subject_name, :instructor,
                  :prelim, :midterm, :final, :overall, :remarks, NOW(), NOW())";
    }
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    if ($checkStmt->rowCount() > 0) {
        $stmt->bindParam(':subject_name', $classInfo['subject_name']);
        $stmt->bindParam(':instructor', $classInfo['instructor']);
        $stmt->bindParam(':prelim', $prelim);
        $stmt->bindParam(':midterm', $midterm);
        $stmt->bindParam(':final', $final);
        $stmt->bindParam(':overall', $overall);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':course_code', $classInfo['subject_code']);
    } else {
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':course_code', $classInfo['subject_code']);
        $stmt->bindParam(':subject_name', $classInfo['subject_name']);
        $stmt->bindParam(':instructor', $classInfo['instructor']);
        $stmt->bindParam(':prelim', $prelim);
        $stmt->bindParam(':midterm', $midterm);
        $stmt->bindParam(':final', $final);
        $stmt->bindParam(':overall', $overall);
        $stmt->bindParam(':remarks', $remarks);
    }
    
    return $stmt->execute();
}

// Process form submissions
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add_grade') {
            $student_id = $_POST['student_id'] ?? '';
            $class_id = $_POST['class_id'] ?? '';
            $prelim = $_POST['prelim_grade'] ?? null;
            $midterm = $_POST['midterm_grade'] ?? null;
            $final = $_POST['final_grade'] ?? null;
            $overall = $_POST['overall_grade'] ?? null;
            $remarks = $_POST['remarks'] ?? '';
            
            // Calculate overall if not provided
            if (!$overall && $prelim !== null && $midterm !== null && $final !== null) {
                $overall = ($prelim * 0.3) + ($midterm * 0.3) + ($final * 0.4);
                $overall = round($overall, 2);
            }
            
            if (!empty($student_id) && !empty($class_id)) {
                $success = addGradeToDatabase($pdo, $student_id, $class_id, $prelim, $midterm, $final, $overall, $remarks);
                
                if ($success) {
                    $message = "Grade added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to add grade. Please try again.";
                    $message_type = "danger";
                }
            } else {
                $message = "Please select student and class!";
                $message_type = "danger";
            }
        }
        elseif ($action === 'update_grade') {
            $grade_id = $_POST['grade_id'] ?? '';
            $prelim = $_POST['prelim_grade'] ?? null;
            $midterm = $_POST['midterm_grade'] ?? null;
            $final = $_POST['final_grade'] ?? null;
            $overall = $_POST['overall_grade'] ?? null;
            $remarks = $_POST['remarks'] ?? '';
            
            // Calculate overall if not provided
            if (!$overall && $prelim !== null && $midterm !== null && $final !== null) {
                $overall = ($prelim * 0.3) + ($midterm * 0.3) + ($final * 0.4);
                $overall = round($overall, 2);
            }
            
            if (!empty($grade_id)) {
                $success = updateGrade($pdo, $grade_id, $prelim, $midterm, $final, $overall, $remarks);
                
                if ($success) {
                    $message = "Grade updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to update grade. Please try again.";
                    $message_type = "danger";
                }
            } else {
                $message = "Invalid grade ID!";
                $message_type = "danger";
            }
        }
        elseif ($action === 'delete_grade') {
            $grade_id = $_POST['grade_id'] ?? '';
            
            if (!empty($grade_id)) {
                $success = deleteGrade($pdo, $grade_id);
                
                if ($success) {
                    $message = "Grade deleted successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to delete grade. Please try again.";
                    $message_type = "danger";
                }
            }
        }
    }
}

// Process Delete Request via GET
if (isset($_GET['delete_grade'])) {
    $success = deleteGrade($pdo, $_GET['delete_grade']);
    
    if ($success) {
        $message = "Grade deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to delete grade. Please try again.";
        $message_type = "danger";
    }
    
    // Redirect to remove delete parameter from URL
    header("Location: grades_management.php");
    exit();
}

// Get real data from database
$students = getAllStudents($pdo);
$subjects = getAllSubjects($pdo);
$classes = getAllClasses($pdo);
$grades = getAllGrades($pdo);

// Calculate statistics
$total_grades = count($grades);
$passed_grades = count(array_filter($grades, function($g) { 
    return isset($g['remarks']) && $g['remarks'] === 'Passed'; 
}));
$incomplete_grades = count(array_filter($grades, function($g) { 
    return isset($g['remarks']) && $g['remarks'] === 'Incomplete'; 
}));
$failed_grades = count(array_filter($grades, function($g) { 
    return isset($g['remarks']) && $g['remarks'] === 'Failed'; 
}));

$unique_students = count(array_unique(array_column($grades, 'student_id')));

// Sample data for dropdowns
$academic_years = ['2023-2024', '2024-2025', '2025-2026'];
$semesters = ['1st Semester', '2nd Semester', 'Summer'];

// PHILIPPINE GRADE EQUIVALENTS
$grade_equivalents = [
    '1.00' => 'Excellent (96-100)',
    '1.25' => 'Very Good (91-95)',
    '1.50' => 'Good (86-90)',
    '1.75' => 'Satisfactory (81-85)',
    '2.00' => 'Fairly Satisfactory (76-80)',
    '2.25' => 'Fair (71-75)',
    '2.50' => 'Passed (66-70)',
    '2.75' => 'Conditional (60-65)',
    '3.00' => 'Failed (Below 60)',
    '4.00' => 'Incomplete',
    '5.00' => 'Failed (Below 50)'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Grades Management - MSU Buug Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        /* Your existing styles here... */
        .msu-maroon { color: #800000; }
        .bg-msu-maroon { background-color: #800000; color: #fff; }
        .btn-msu { background: #800000; color: white; border: none; padding: 10px 18px; border-radius: 8px; }
        .btn-msu-sm { background: #800000; color: white; border: none; padding: 6px 12px; border-radius: 6px; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .navbar { background: #800000 !important; padding: 1rem 0; position: fixed; top: 0; width: 100%; z-index: 1030; }
        .sidebar { background: #5a0000; min-height: 100vh; position: fixed; width: 280px; left: 0; top: 0; padding-top: 70px; }
        .sidebar .nav-link { color: white; padding: 15px 20px; border-left: 4px solid transparent; }
        .sidebar .nav-link:hover { background: #a30000; border-left-color: #FFD700; color: #FFD700; }
        .sidebar .nav-link.active { background: #a30000; border-left-color: #FFD700; color: #FFD700; font-weight:600; }
        .main-content { padding: 20px 15px; min-height: 100vh; margin-top: 70px; margin-left: 280px; width: calc(100% - 280px); }
        .dashboard-card { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-top: 4px solid #800000; margin-bottom: 20px; padding: 1.5rem; }
        .welcome-banner { background: linear-gradient(135deg, #800000, #5a0000); color: white; border-radius: 10px; padding: 20px; margin-bottom: 25px; border: 2px solid #FFD700; }
        .stats-card { background:white; border-radius:10px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.08); border-top: 4px solid #800000; }
        
        /* GRADES SPECIFIC STYLES */
        .stats-number { font-size: 2rem; font-weight: bold; color: #800000; }
        .badge-passed { background: #28a745; color: white; }
        .badge-incomplete { background: #ffc107; color: #000; }
        .badge-failed { background: #dc3545; color: white; }
        .grade-excellent { color: #28a745; font-weight: bold; }
        .grade-good { color: #20c997; font-weight: bold; }
        .grade-fair { color: #ffc107; font-weight: bold; }
        .grade-poor { color: #fd7e14; font-weight: bold; }
        .grade-failed { color: #dc3545; font-weight: bold; }
        .table th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        .search-filter-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 1.5rem; }
        
        /* Status indicators */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-passed { background: #28a745; }
        .status-incomplete { background: #ffc107; }
        .status-failed { background: #dc3545; }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .sidebar { left: -280px; }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; width: 100%; }
        }
        
        .small-grade {
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle d-lg-none btn btn-link text-white" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand nav-brand" href="admin_dashboard.php">
                <i class="fas fa-chart-line me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Grades Management</span>
                <span class="d-inline d-sm-none">Grades</span>
            </a>
            <div class="d-flex align-items-center ms-auto">
                <div class="me-3 text-white d-none d-md-block text-end">
                    <div><strong><?php echo htmlspecialchars($admin_info['name']); ?></strong></div>
                    <small><?php echo htmlspecialchars($admin_info['role']); ?></small>
                </div>
                <div class="dropdown">
                    <a class="text-white text-decoration-none d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2" style="width:44px; height:44px; font-size:18px;">
                            <?php echo strtoupper(substr(explode(' ', $admin_info['name'])[0], 0, 1)); ?>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_profile.php"><i class="fas fa-user me-2 text-msu"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="admin_settings.php"><i class="fas fa-cog me-2 text-msu"></i>Settings</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="mobile-user-info d-lg-none p-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3" style="width:48px; height:48px; font-size:16px;">
                    <?php echo strtoupper(substr(explode(' ', $admin_info['name'])[0], 0, 1)); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($admin_info['name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($admin_info['role']); ?></small>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column pt-3">
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="user_management.php" class="nav-link"><i class="fas fa-users me-2"></i> User Management</a>
            <a href="course_management.php" class="nav-link"><i class="fas fa-book me-2"></i> Course Management</a>
            <a href="enrollment_management.php" class="nav-link"><i class="fas fa-clipboard-list me-2"></i> Enrollment</a>
            <a href="grades_management.php" class="nav-link active"><i class="fas fa-chart-line me-2"></i> Grades</a>
            <a href="fees_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fees</a>
            <a href="fines_management.php" class="nav-link"><i class="fas fa-exclamation-triangle me-2"></i> Fines</a>
            <a href="reports_management.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link"><i class="fas fa-cogs me-2"></i> System Settings</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">Grades Management System</h2>
                    <p class="mb-1">View and manage student grades submitted by faculty</p>
                    <small>Total Grade Records: <?php echo $total_grades; ?> | Unique Students: <?php echo $unique_students; ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addGradeModal">
                        <i class="fas fa-plus-circle me-2"></i> Add New Grade
                    </button>
                </div>
            </div>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $total_grades; ?></h3>
                    <p class="small">Total Grades</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#28a745;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $passed_grades; ?></h3>
                    <p class="small">Passed</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#ffc107;color:#212529;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $incomplete_grades; ?></h3>
                    <p class="small">Incomplete</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#dc3545;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $failed_grades; ?></h3>
                    <p class="small">Failed</p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="search-filter-card">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search grades..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="remarksFilter">
                        <option value="">All Remarks</option>
                        <option value="Passed">Passed</option>
                        <option value="Incomplete">Incomplete</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="subjectFilter">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo htmlspecialchars($subject['subject_code']); ?>">
                                <?php echo htmlspecialchars($subject['subject_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-msu w-100" data-bs-toggle="modal" data-bs-target="#addGradeModal">
                        <i class="fas fa-plus me-1"></i> Add Grade
                    </button>
                </div>
            </div>
        </div>

        <!-- Grades Management Table -->
        <div class="dashboard-card">
            <h5 class="text-msu mb-4">
                <i class="fas fa-table me-2"></i>All Grade Records 
                <small class="text-muted">(from grades table - faculty submitted)</small>
            </h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="gradesTable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Subject</th>
                            <th>Instructor</th>
                            <th>Prelim</th>
                            <th>Midterm</th>
                            <th>Final</th>
                            <th>Overall</th>
                            <th>Equivalent</th>
                            <th>Remarks</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grades)): ?>
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No grades found in the database</h5>
                                    <p class="text-muted">Faculty members need to submit grades first.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grades as $grade): ?>
                            <tr data-grade-id="<?php echo $grade['grade_id']; ?>">
                                <td><strong><?php echo htmlspecialchars($grade['student_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($grade['course_code'] ?? $grade['subject_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($grade['subject_name'] ?? ''); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($grade['instructor'] ?? '-'); ?></td>
                                <td>
                                    <?php if (!empty($grade['prelim_grade'])): ?>
                                    <span class="small-grade"><?php echo number_format($grade['prelim_grade'], 2); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($grade['midterm_grade'])): ?>
                                    <span class="small-grade"><?php echo number_format($grade['midterm_grade'], 2); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($grade['final_grade'])): ?>
                                    <span class="small-grade"><?php echo number_format($grade['final_grade'], 2); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($grade['overall_grade'])): ?>
                                    <strong class="<?php echo getGradeClass($grade['overall_grade']); ?>">
                                        <?php echo number_format($grade['overall_grade'], 2); ?>
                                    </strong>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($grade['overall_grade'])): ?>
                                    <span class="badge <?php echo getRemarksBadgeClass($grade['remarks']); ?>">
                                        <?php echo getGradeEquivalent($grade['overall_grade']); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($grade['remarks'])): ?>
                                    <span class="badge <?php echo getRemarksBadgeClass($grade['remarks']); ?>">
                                        <span class="status-indicator status-<?php echo strtolower($grade['remarks']); ?>"></span>
                                        <?php echo htmlspecialchars($grade['remarks']); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo !empty($grade['grade_date']) ? date('M d, Y', strtotime($grade['grade_date'])) : '-'; ?></small>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary mb-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editGradeModal"
                                            data-id="<?php echo $grade['grade_id']; ?>"
                                            data-prelim="<?php echo $grade['prelim_grade'] ?? ''; ?>"
                                            data-midterm="<?php echo $grade['midterm_grade'] ?? ''; ?>"
                                            data-final="<?php echo $grade['final_grade'] ?? ''; ?>"
                                            data-overall="<?php echo $grade['overall_grade'] ?? ''; ?>"
                                            data-remarks="<?php echo htmlspecialchars($grade['remarks'] ?? 'Passed'); ?>"
                                            onclick="setEditData(this)"
                                            title="Edit Grade">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="if(confirm('Are you sure you want to delete this grade?')) window.location.href='grades_management.php?delete_grade=<?php echo $grade['grade_id']; ?>'"
                                            title="Delete Grade">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Grade Modal -->
    <div class="modal fade" id="addGradeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Grade</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_grade">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Student *</label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">Choose student...</option>
                                    <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['student_id']; ?>">
                                        <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' - ID: ' . $student['student_id']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">Choose class...</option>
                                    <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>">
                                        <?php echo htmlspecialchars($class['subject_code'] . ' - ' . $class['subject_name'] . ' - Sec: ' . $class['section']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Prelim Grade (30%)</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="prelim_grade" 
                                       placeholder="1.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Midterm Grade (30%)</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="midterm_grade" 
                                       placeholder="1.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Final Grade (40%)</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="final_grade" 
                                       placeholder="1.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Overall Grade</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="overall_grade" 
                                       placeholder="Auto-calculated">
                                <small class="text-muted">Will auto-calculate if empty</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <select class="form-select" name="remarks">
                                <option value="">Auto-detect from grade</option>
                                <option value="Passed">Passed</option>
                                <option value="Incomplete">Incomplete</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle me-1"></i> 
                                <strong>Philippine Grading System (1.00-5.00 scale):</strong><br>
                                1.00-1.75 (Excellent/Very Good), 2.00-2.75 (Good/Fair), 3.00 (Passed),<br>
                                4.00 (Incomplete), 5.00 (Failed)
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-msu">Save Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Grade Modal -->
    <div class="modal fade" id="editGradeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Grade</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_grade">
                        <input type="hidden" name="grade_id" id="edit_grade_id">
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Prelim Grade (30%)</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="prelim_grade" id="edit_prelim_grade">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Midterm Grade (30%)</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="midterm_grade" id="edit_midterm_grade">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Final Grade (40%)</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="final_grade" id="edit_final_grade">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Overall Grade</label>
                                <input type="number" step="0.01" min="1.00" max="5.00" 
                                       class="form-control" name="overall_grade" id="edit_overall_grade">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <select class="form-select" name="remarks" id="edit_remarks">
                                <option value="Passed">Passed</option>
                                <option value="Incomplete">Incomplete</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-warning">
                            <small><i class="fas fa-exclamation-triangle me-1"></i> 
                                Editing a grade will update the student's record immediately.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-msu">Update Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        // Set edit modal data
        function setEditData(button) {
            const id = button.getAttribute('data-id');
            const prelim = button.getAttribute('data-prelim');
            const midterm = button.getAttribute('data-midterm');
            const final = button.getAttribute('data-final');
            const overall = button.getAttribute('data-overall');
            const remarks = button.getAttribute('data-remarks');
            
            document.getElementById('edit_grade_id').value = id;
            document.getElementById('edit_prelim_grade').value = prelim;
            document.getElementById('edit_midterm_grade').value = midterm;
            document.getElementById('edit_final_grade').value = final;
            document.getElementById('edit_overall_grade').value = overall;
            document.getElementById('edit_remarks').value = remarks;
        }
        
        // Auto-calculate overall grade when component grades change
        document.addEventListener('DOMContentLoaded', function() {
            // For add modal
            const prelimInput = document.querySelector('input[name="prelim_grade"]');
            const midtermInput = document.querySelector('input[name="midterm_grade"]');
            const finalInput = document.querySelector('input[name="final_grade"]');
            const overallInput = document.querySelector('input[name="overall_grade"]');
            
            if (prelimInput && midtermInput && finalInput && overallInput) {
                function calculateOverall() {
                    const prelim = parseFloat(prelimInput.value) || 0;
                    const midterm = parseFloat(midtermInput.value) || 0;
                    const final = parseFloat(finalInput.value) || 0;
                    
                    if (prelim > 0 && midterm > 0 && final > 0) {
                        const overall = (prelim * 0.3) + (midterm * 0.3) + (final * 0.4);
                        overallInput.value = overall.toFixed(2);
                    }
                }
                
                prelimInput.addEventListener('input', calculateOverall);
                midtermInput.addEventListener('input', calculateOverall);
                finalInput.addEventListener('input', calculateOverall);
            }
            
            // For edit modal
            const editPrelimInput = document.getElementById('edit_prelim_grade');
            const editMidtermInput = document.getElementById('edit_midterm_grade');
            const editFinalInput = document.getElementById('edit_final_grade');
            const editOverallInput = document.getElementById('edit_overall_grade');
            
            if (editPrelimInput && editMidtermInput && editFinalInput && editOverallInput) {
                function calculateEditOverall() {
                    const prelim = parseFloat(editPrelimInput.value) || 0;
                    const midterm = parseFloat(editMidtermInput.value) || 0;
                    const final = parseFloat(editFinalInput.value) || 0;
                    
                    if (prelim > 0 && midterm > 0 && final > 0) {
                        const overall = (prelim * 0.3) + (midterm * 0.3) + (final * 0.4);
                        editOverallInput.value = overall.toFixed(2);
                    }
                }
                
                editPrelimInput.addEventListener('input', calculateEditOverall);
                editMidtermInput.addEventListener('input', calculateEditOverall);
                editFinalInput.addEventListener('input', calculateEditOverall);
            }
        });
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterGrades();
        });

        document.getElementById('remarksFilter').addEventListener('change', filterGrades);
        document.getElementById('subjectFilter').addEventListener('change', filterGrades);

        function filterGrades() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const remarksFilter = document.getElementById('remarksFilter').value;
            const subjectFilter = document.getElementById('subjectFilter').value;
            
            const rows = document.querySelectorAll('#gradesTable tbody tr');
            
            rows.forEach(row => {
                const studentId = row.cells[0].textContent.toLowerCase();
                const studentName = row.cells[1].textContent.toLowerCase();
                const subject = row.cells[2].textContent.toLowerCase();
                const remarks = row.cells[9].textContent.toLowerCase();
                
                const matchesSearch = studentId.includes(searchText) || studentName.includes(searchText) || subject.includes(searchText);
                const matchesRemarks = !remarksFilter || remarks.includes(remarksFilter.toLowerCase());
                const matchesSubject = !subjectFilter || subject.includes(subjectFilter.toLowerCase());
                
                row.style.display = matchesSearch && matchesRemarks && matchesSubject ? '' : 'none';
            });
        }
        
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>
</html>

<?php
// Helper functions for grade styling - PHILIPPINE GRADING SYSTEM
function getGradeClass($grade) {
    if ($grade >= 1.00 && $grade <= 1.75) return 'grade-excellent';
    if ($grade >= 2.00 && $grade <= 2.75) return 'grade-good';
    if ($grade == 3.00) return 'grade-fair';
    if ($grade == 4.00) return 'grade-poor';
    if ($grade == 5.00) return 'grade-failed';
    return 'grade-failed';
}

function getRemarksBadgeClass($remarks) {
    if (empty($remarks)) return 'badge-secondary';
    
    switch(strtolower($remarks)) {
        case 'passed': return 'badge-passed';
        case 'incomplete': return 'badge-incomplete';
        case 'failed': return 'badge-failed';
        default: return 'badge-secondary';
    }
}