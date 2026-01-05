<?php
// student_grades.php - NO HORIZONTAL SCROLL VERSION

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Session check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

// Database connection
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

// Function to get student data
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

// Fetch student data
$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);

if ($student_data) {
    $_SESSION['user_info'] = [
        'name' => $student_data['first_name'] . ' ' . $student_data['last_name'],
        'course' => $student_data['course'],
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

// Function to get student grades
function getStudentGrades($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    grade_id,
                    student_id,
                    course_code as subject_code,
                    subject_name,
                    instructor,
                    prelim_grade, 
                    midterm_grade, 
                    final_grade,
                    overall_grade,
                    remarks,
                    grade_date,
                    '3' as units
                  FROM grades 
                  WHERE student_id = :student_id
                  ORDER BY course_code";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to calculate GPA
function calculateGPA($grades) {
    if (empty($grades)) return 0;
    
    $total_grade = 0;
    $count = 0;
    
    foreach ($grades as $grade) {
        if (!empty($grade['overall_grade']) && is_numeric($grade['overall_grade'])) {
            $total_grade += $grade['overall_grade'];
            $count++;
        }
    }
    
    return $count > 0 ? round($total_grade / $count, 2) : 0;
}

// Function to get grade statistics
function getGradeStatistics($grades) {
    $statistics = [
        'total_subjects' => count($grades),
        'passed' => 0,
        'failed' => 0,
        'in_progress' => 0,
        'average_grade' => 0
    ];
    
    $total_grade = 0;
    $count = 0;
    
    foreach ($grades as $grade) {
        if (!empty($grade['overall_grade'])) {
            $total_grade += $grade['overall_grade'];
            $count++;
            
            if ($grade['overall_grade'] <= 3.00) {
                $statistics['passed']++;
            } else {
                $statistics['failed']++;
            }
        } else {
            $statistics['in_progress']++;
        }
    }
    
    $statistics['average_grade'] = $count > 0 ? round($total_grade / $count, 2) : 0;
    
    return $statistics;
}

// Helper function to determine grade color
function getGradeClass($grade) {
    if ($grade >= 1.00 && $grade <= 1.50) {
        return 'grade-excellent';
    } elseif ($grade > 1.50 && $grade <= 2.50) {
        return 'grade-good';
    } elseif ($grade > 2.50 && $grade <= 3.00) {
        return 'grade-average';
    } elseif ($grade > 3.00 && $grade <= 5.00) {
        return 'grade-failed';
    } else {
        return 'text-muted';
    }
}

// Helper functions
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

// Fetch grades
$grades = getStudentGrades($student_id);
$gpa = calculateGPA($grades);
$statistics = getGradeStatistics($grades);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades & Progress - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* EXACT SAME BASE STYLES */
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
        
        /* ===== NO HORIZONTAL SCROLL ===== */
        .main-content {
            max-width: 100%;
            overflow-x: hidden;
        }
        
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
        
        /* SIDEBAR */
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
        
        .sidebar.show { left: 0; }
        
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
        
        .sidebar-overlay.show { display: block; }
        
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
        }
        
        /* CARDS - CLEAN DESIGN */
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 4px solid #800000;
            margin-bottom: 20px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        /* GRADE CARD DESIGN - NO HORIZONTAL SCROLL */
        .grade-card {
            background: white;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .grade-card:hover {
            border-color: #800000;
            box-shadow: 0 5px 20px rgba(128,0,0,0.1);
            transform: translateY(-3px);
        }
        
        .grade-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #800000;
            border-radius: 4px 0 0 4px;
        }
        
        .grade-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .subject-info {
            flex: 1;
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
            gap: 1rem;
            font-size: 0.85rem;
            color: #666;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* GRADE GRID - CLEAN & SIMPLE */
        .grade-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .grade-item {
            text-align: center;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .grade-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .grade-value {
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        /* GRADE COLORS - CLEAN */
        .grade-excellent { 
            color: #28a745;
        }
        
        .grade-good { 
            color: #20c997;
        }
        
        .grade-average { 
            color: #ffc107;
        }
        
        .grade-failed { 
            color: #dc3545;
        }
        
        /* GRADE STATUS */
        .grade-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px dashed #e9ecef;
            margin-top: 1rem;
        }
        
        .remarks-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .remarks-passed {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .remarks-failed {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .remarks-inprogress {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .last-updated {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* STATS CARDS - CLEAN */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            border-color: #800000;
            transform: translateY(-3px);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1rem;
            background: #800000;
            color: white;
        }
        
        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #800000;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* USER AVATAR */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFD700;
            background: #800000;
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
        
        /* SECTION TITLE */
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
        
        /* EMPTY STATE */
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
        
        /* LEGEND */
        .grade-legend {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
            margin-top: 1.5rem;
            border: 1px solid #e9ecef;
        }
        
        .legend-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .legend-text {
            font-size: 0.9rem;
        }
        
        /* ===== DESKTOP STYLES ===== */
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
            
            .grade-card {
                padding: 1.75rem;
            }
            
            .grade-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .dashboard-card {
                padding: 1rem;
            }
            
            .grade-card {
                padding: 1rem;
            }
            
            .grade-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .grade-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .grade-status {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .subject-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .grade-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .legend-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
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
                        <div class="user-avatar me-2">
                            <?php
                            $profile_pic_path = $student_data['profile_picture'] ?? null;
                            $first_letter = getFirstLetter($student_info);
                            ?>
                            
                            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile">
                            <?php else: ?>
                                <div class="letter-avatar"><?php echo $first_letter; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-none d-md-block text-white">
                            <strong><?php echo getDisplayName($student_info); ?></strong><br>
                            <small>Student</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="student-dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="student_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="my_subject.php"><i class="fas fa-book me-2"></i>My Subjects</a></li>
                        <li><a class="dropdown-item active" href="student_grades.php"><i class="fas fa-chart-line me-2"></i>Grades & Progress</a></li>
                        <li><a class="dropdown-item" href="student_settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="student_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Layout -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Mobile User Info -->
            <div class="mobile-user-info d-lg-none">
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3"><?php echo getFirstLetter($student_info); ?></div>
                    <div>
                        <strong><?php echo getDisplayName($student_info); ?></strong><br>
                        <small>Student ID: <?php echo htmlspecialchars($student_id); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="d-flex flex-column pt-3">
                <a href="student_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="my_subject.php" class="nav-link"><i class="fas fa-book"></i> My Subjects</a>
                <a href="student_grades.php" class="nav-link active"><i class="fas fa-chart-line"></i> Grades & Progress</a>
                <a href="class_schedule.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Class Schedule</a>
                <a href="student_fees.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Fees & Payments</a>
                <a href="student_fines.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Fines & Penalties</a>
                <a href="academic.php" class="nav-link"><i class="fas fa-file-alt"></i>Academic Curriculum </a>

                <a href="student_support.php" class="nav-link"><i class="fas fa-question-circle"></i> Help & Support</a>
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Notifications
                </a>
            </div>
        </div>

        <!-- Main Content - NO HORIZONTAL SCROLL -->
        <div class="main-content">
            <!-- Module Header -->
            <div class="module-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="section-title mb-2">Grades & Progress</h4>
                        <p class="text-muted mb-0">
                            Student: <strong><?php echo htmlspecialchars($student_info['name']); ?></strong> | 
                            ID: <?php echo htmlspecialchars($student_id); ?> | 
                            Course: <?php echo getStudentField($student_data, 'course'); ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <span class="badge bg-msu-maroon text-white px-3 py-2">
                            Academic Year: 2023-2024
                        </span>
                    </div>
                </div>
            </div>

            <!-- Academic Summary -->
            <div class="dashboard-card mb-4">
                <h5 class="mb-4">Academic Summary</h5>
                
                <div class="stats-container">
                    <!-- GPA -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-number"><?php echo $gpa; ?></div>
                        <div class="stat-label">Current GPA</div>
                    </div>
                    
                    <!-- Total Subjects -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-number"><?php echo $statistics['total_subjects']; ?></div>
                        <div class="stat-label">Total Subjects</div>
                    </div>
                    
                    <!-- Passed -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $statistics['passed']; ?></div>
                        <div class="stat-label">Passed</div>
                    </div>
                    
                    <!-- In Progress -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo $statistics['in_progress']; ?></div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
            </div>

            <!-- Detailed Grades - CARD VIEW NO SCROLL -->
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Subject Grades</h5>
                    <div>
                        <span class="badge bg-light text-dark border px-3 py-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Passing Grade: 3.00
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($grades)): ?>
                    <!-- Grade Cards Container -->
                    <div class="grades-container">
                        <?php foreach ($grades as $grade): ?>
                            <div class="grade-card">
                                <!-- Subject Header -->
                                <div class="grade-header">
                                    <div class="subject-info">
                                        <div class="subject-code"><?php echo htmlspecialchars($grade['subject_code']); ?></div>
                                        <div class="subject-name"><?php echo htmlspecialchars($grade['subject_name']); ?></div>
                                        <div class="subject-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-chalkboard-teacher text-muted"></i>
                                                <span><?php echo htmlspecialchars($grade['instructor']); ?></span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-weight-hanging text-muted"></i>
                                                <span><?php echo htmlspecialchars($grade['units']); ?> Units</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Grade Grid -->
                                <div class="grade-grid">
                                    <!-- Prelim -->
                                    <div class="grade-item">
                                        <div class="grade-label">Prelim</div>
                                        <div class="grade-value <?php echo !empty($grade['prelim_grade']) ? getGradeClass($grade['prelim_grade']) : 'text-muted'; ?>">
                                            <?php echo !empty($grade['prelim_grade']) ? $grade['prelim_grade'] : '--'; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Midterm -->
                                    <div class="grade-item">
                                        <div class="grade-label">Midterm</div>
                                        <div class="grade-value <?php echo !empty($grade['midterm_grade']) ? getGradeClass($grade['midterm_grade']) : 'text-muted'; ?>">
                                            <?php echo !empty($grade['midterm_grade']) ? $grade['midterm_grade'] : '--'; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Final -->
                                    <div class="grade-item">
                                        <div class="grade-label">Final</div>
                                        <div class="grade-value <?php echo !empty($grade['final_grade']) ? getGradeClass($grade['final_grade']) : 'text-muted'; ?>">
                                            <?php echo !empty($grade['final_grade']) ? $grade['final_grade'] : '--'; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Overall -->
                                    <div class="grade-item">
                                        <div class="grade-label">Overall</div>
                                        <div class="grade-value <?php echo !empty($grade['overall_grade']) ? getGradeClass($grade['overall_grade']) : 'text-muted'; ?>">
                                            <?php echo !empty($grade['overall_grade']) ? $grade['overall_grade'] : '--'; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Status & Last Updated -->
                                <div class="grade-status">
                                    <div>
                                        <?php if (!empty($grade['remarks'])): ?>
                                            <?php if ($grade['remarks'] === 'Passed'): ?>
                                                <span class="remarks-badge remarks-passed">
                                                    <i class="fas fa-check-circle me-1"></i>Passed
                                                </span>
                                            <?php elseif ($grade['remarks'] === 'Failed'): ?>
                                                <span class="remarks-badge remarks-failed">
                                                    <i class="fas fa-times-circle me-1"></i>Failed
                                                </span>
                                            <?php else: ?>
                                                <span class="remarks-badge remarks-inprogress">
                                                    <i class="fas fa-clock me-1"></i>In Progress
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="remarks-badge remarks-inprogress">
                                                <i class="fas fa-clock me-1"></i>In Progress
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="last-updated">
                                        <?php if (!empty($grade['grade_date'])): ?>
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?php echo date('M d, Y', strtotime($grade['grade_date'])); ?>
                                        <?php else: ?>
                                            <i class="fas fa-clock me-1"></i>
                                            Not yet updated
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Grade Legend -->
                    <div class="grade-legend">
                        <h6 class="mb-3">Grade Legend</h6>
                        <div class="legend-grid">
                            <div class="legend-item">
                                <div class="legend-dot" style="background: #28a745;"></div>
                                <div class="legend-text">
                                    <strong>1.00 - 1.50</strong> - Excellent
                                </div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background: #20c997;"></div>
                                <div class="legend-text">
                                    <strong>1.51 - 2.50</strong> - Good
                                </div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background: #ffc107;"></div>
                                <div class="legend-text">
                                    <strong>2.51 - 3.00</strong> - Average (Passing)
                                </div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background: #dc3545;"></div>
                                <div class="legend-text">
                                    <strong>3.01 - 5.00</strong> - Failed
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h6 class="mt-3 mb-2">No Grades Available</h6>
                        <p>Your grades will appear here once they are posted by your instructors.</p>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Check back at the end of the grading period.
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
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

        document.querySelector('.sidebar-overlay').addEventListener('click', function() {
            toggleSidebar();
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

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
    </script>
</body>
</html>