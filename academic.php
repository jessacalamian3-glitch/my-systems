<?php
// my_subjects.php - STUDENT CURRICULUM VIEW (TABLE FORMAT ONLY)
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Check if user is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);

// Handle filter requests
$selected_year = $_GET['year'] ?? 1;
$selected_semester = $_GET['semester'] ?? 'all';

// Fetch student data and set session info
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

// Function to get matching program code from curriculum
function getMatchingProgramCode($student_course) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) return null;
    
    // Get all program codes from curriculum
    $query = "SELECT DISTINCT program_code FROM curriculum ORDER BY program_code";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $all_programs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($all_programs)) {
        return null;
    }
    
    $student_course_upper = strtoupper(trim($student_course));
    
    // 1. Try exact match first
    foreach ($all_programs as $program) {
        if (strtoupper($program) === $student_course_upper) {
            return $program;
        }
    }
    
    // 2. Try partial match (e.g., "BSIT" in "Bachelor of Science in Information Technology")
    foreach ($all_programs as $program) {
        $program_upper = strtoupper($program);
        
        // Check if program code is found in student course
        if (strpos($student_course_upper, $program_upper) !== false) {
            return $program;
        }
        
        // Check if student course is found in program code
        if (strpos($program_upper, $student_course_upper) !== false) {
            return $program;
        }
    }
    
    // 3. For common program patterns
    $common_patterns = [
        'BSIT' => ['INFORMATION TECHNOLOGY', 'IT', 'INFO TECH'],
        'BSCS' => ['COMPUTER SCIENCE', 'CS'],
        'BSIS' => ['INFORMATION SYSTEMS', 'IS'],
        'FORESTRY' => ['FORESTRY', 'BSF', 'BS FORESTRY'],
        'BSEE' => ['ELECTRICAL ENGINEERING', 'EE'],
        'BSECE' => ['ELECTRONICS ENGINEERING', 'ECE'],
    ];
    
    foreach ($common_patterns as $program_code => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($student_course_upper, $keyword) !== false) {
                // Check if this program code exists in database
                if (in_array($program_code, $all_programs)) {
                    return $program_code;
                }
            }
        }
    }
    
    return null; // No matching program found
}

// Function to get curriculum for student's specific program
function getCurriculum($student_id, $selected_year = null, $selected_semester = 'all') {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) return [];
    
    // Get student's course
    $query = "SELECT course FROM students WHERE student_id = :student_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student || empty($student['course'])) {
        return [
            'program_code' => null,
            'original_program' => 'No course assigned',
            'subjects' => [],
            'has_curriculum' => false
        ];
    }
    
    $original_program = $student['course'];
    
    // Get matching program code from curriculum database
    $program_code = getMatchingProgramCode($original_program);
    
    if (!$program_code) {
        return [
            'program_code' => null,
            'original_program' => $original_program,
            'subjects' => [],
            'has_curriculum' => false
        ];
    }
    
    // Now get subjects for this specific program code
    // ONLY subjects for this program (no core subjects from other programs)
    $sql = "SELECT 
                c.subject_code, 
                s.subject_name as descriptive_title, 
                s.units,
                c.year_level,
                c.semester,
                c.program_code,
                c.is_core
            FROM curriculum c
            JOIN subjects s ON c.subject_code = s.subject_code
            WHERE c.program_code = :program_code
            AND c.subject_code IS NOT NULL
            AND s.subject_name IS NOT NULL";
    
    $params = [':program_code' => $program_code];
    
    if ($selected_year) {
        $sql .= " AND c.year_level = :year_level";
        $params[':year_level'] = $selected_year;
    }
    
    if ($selected_semester !== 'all') {
        $sql .= " AND c.semester = :semester";
        $params[':semester'] = $selected_semester;
    }
    
    $sql .= " ORDER BY c.year_level, c.semester, c.subject_code";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'program_code' => $program_code,
            'original_program' => $original_program,
            'subjects' => $subjects,
            'has_curriculum' => !empty($subjects)
        ];
        
    } catch (PDOException $e) {
        error_log("Curriculum query error: " . $e->getMessage());
        return [
            'program_code' => $program_code,
            'original_program' => $original_program,
            'subjects' => [],
            'has_curriculum' => false
        ];
    }
}

// Get curriculum data
$curriculum_result = getCurriculum($student_id, $selected_year, $selected_semester);
$curriculum_data = $curriculum_result['subjects'] ?? [];
$program_code = $curriculum_result['program_code'] ?? '';
$original_program = $curriculum_result['original_program'] ?? '';
$has_curriculum = $curriculum_result['has_curriculum'] ?? false;

// Calculate total units
$total_units = 0;
foreach ($curriculum_data as $subject) {
    $total_units += (float) $subject['units'];
}

// Helper functions
function getDisplayName($info) {
    if (!$info) return 'Student';
    $name = $info['name'] ?? '';
    return !empty($name) ? htmlspecialchars($name) : 'Student';
}

function getFirstLetter($info) {
    if (!$info) return 'S';
    $name = $info['name'] ?? '';
    return !empty($name) ? strtoupper(substr($name, 0, 1)) : 'S';
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
        /* SIMPLE STYLES - TABLE FORMAT ONLY */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
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
        
        /* SIMPLE FILTER BOX */
        .filter-box {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        }
        
        /* SUMMARY BOX */
        .summary-box {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
            text-align: center;
        }
        
        .summary-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #800000;
            margin-bottom: 5px;
        }
        
        .summary-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* TABLE STYLES */
        .curriculum-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .curriculum-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        
        .curriculum-table thead {
            background: #800000;
            color: white;
        }
        
        .curriculum-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 3px solid #5a0000;
        }
        
        .curriculum-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .curriculum-table tbody tr:hover {
            background-color: rgba(128, 0, 0, 0.05);
        }
        
        .units-badge {
            background: #800000;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .core-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .program-badge {
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .semester-header-row {
            background-color: #e9ecef !important;
            font-weight: 600;
            color: #800000;
        }
        
        .semester-header-row td {
            border-bottom: 2px solid #800000;
            padding: 15px;
        }
        
        /* MOBILE USER INFO */
        .mobile-user-info {
            display: none;
            background: #5a0000;
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        /* INFO ALERT */
        .info-alert {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .warning-alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        /* DESKTOP STYLES */
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
        }

        /* EXTRA SMALL DEVICES */
        @media (max-width: 480px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .filter-box, .summary-box {
                padding: 15px;
            }
            
            .sidebar {
                width: 260px;
                left: -260px;
            }
            
            .curriculum-table {
                font-size: 0.85rem;
            }
            
            .curriculum-table th,
            .curriculum-table td {
                padding: 10px;
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
            <a class="navbar-brand nav-brand" href="student_dashboard.php">
                <i class="fas fa-user-graduate me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Student Portal</span>
                <span class="d-inline d-sm-none">Student Portal</span>
            </a>
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <?php
                        $profile_pic_path = $student_data['profile_picture'] ?? null;
                        $first_letter = getFirstLetter($student_info);
                        ?>
                        
                        <div class="user-avatar me-2">
                            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile">
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
                        <li><a class="dropdown-item" href="student_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="student_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item active" href="my_subjects.php"><i class="fas fa-book me-2"></i>My Subjects</a></li>
                        <li><a class="dropdown-item" href="student_grades.php"><i class="fas fa-chart-line me-2"></i>Grades & Progress</a></li>
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
                <a href="my_subjects.php" class="nav-link active">
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
                <a href="academic.php" class="nav-link">
                    <i class="fas fa-file-alt"></i> Academic Curriculum
                </a>
                <a href="student_support.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-bell"></i> Notifications
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="mb-4">
                <h4 class="text-800 mb-2">
                    <i class="fas fa-book me-2"></i>My Academic Curriculum
                </h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    <strong>Student:</strong> <?php echo getDisplayName($student_info); ?> | 
                    <strong>ID:</strong> <?php echo htmlspecialchars($student_id); ?> | 
                    <strong>Program:</strong> <?php echo htmlspecialchars($original_program); ?>
                    <?php if ($program_code): ?>
                        <span class="program-badge"><?php echo htmlspecialchars($program_code); ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Info/Warning Alert -->
            <?php if (!$program_code): ?>
                <div class="warning-alert">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                    <strong>No Curriculum Available!</strong> Your program "<?php echo htmlspecialchars($original_program); ?>" 
                    doesn't have a curriculum in the system yet. Please contact the administrator to add subjects for your program.
                </div>
            <?php elseif ($program_code && !$has_curriculum): ?>
                <div class="warning-alert">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                    <strong>No Subjects Found!</strong> Your program "<?php echo htmlspecialchars($original_program); ?>" 
                    is registered in the system (<?php echo htmlspecialchars($program_code); ?>), but no subjects have been added yet.
                </div>
            <?php elseif ($program_code && $has_curriculum): ?>
                <div class="info-alert">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    <strong>Curriculum Found:</strong> Displaying subjects for <?php echo htmlspecialchars($program_code); ?> program.
                </div>
            <?php endif; ?>

            <!-- Filter Box -->
            <div class="filter-box">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-2"></i>Year Level
                        </label>
                        <select class="form-select" onchange="window.location.href='?year='+this.value+'&semester=<?php echo $selected_semester; ?>'">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $selected_year == $i ? 'selected' : ''; ?>>
                                    Year <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-layer-group me-2"></i>Semester
                        </label>
                        <select class="form-select" onchange="window.location.href='?year=<?php echo $selected_year; ?>&semester='+this.value">
                            <option value="all" <?php echo $selected_semester == 'all' ? 'selected' : ''; ?>>All Semesters</option>
                            <option value="1" <?php echo $selected_semester == '1' ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2" <?php echo $selected_semester == '2' ? 'selected' : ''; ?>>2nd Semester</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-chart-bar me-2"></i>Summary
                        </label>
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="summary-number"><?php echo count($curriculum_data); ?></div>
                                <div class="summary-label">Subjects</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="summary-number"><?php echo $total_units; ?></div>
                                <div class="summary-label">Total Units</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Header -->
            <?php if ($program_code && $has_curriculum): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-800">
                    <i class="fas fa-list-alt me-2"></i>
                    Year <?php echo $selected_year; ?> Curriculum
                    <?php if ($selected_semester !== 'all'): ?>
                        - Semester <?php echo $selected_semester; ?>
                    <?php endif; ?>
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Curriculum Table -->
            <div class="curriculum-table-container">
                <?php if ($program_code && $has_curriculum && !empty($curriculum_data)): ?>
                    <table class="curriculum-table">
                        <thead>
                            <tr>
                                <th width="15%">Subject Code</th>
                                <th>Descriptive Title</th>
                                <th width="10%">Units</th>
                                <th width="10%">Year</th>
                                <th width="10%">Semester</th>
                                <th width="15%">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Group by semester if viewing all semesters
                            if ($selected_semester === 'all') {
                                $grouped_subjects = [];
                                foreach ($curriculum_data as $subject) {
                                    $semester = $subject['semester'];
                                    $grouped_subjects[$semester][] = $subject;
                                }
                                ksort($grouped_subjects);
                                
                                foreach ($grouped_subjects as $semester => $subjects):
                            ?>
                                    <tr class="semester-header-row">
                                        <td colspan="6">
                                            <i class="fas fa-layer-group me-2"></i>
                                            Semester <?php echo $semester; ?>
                                        </td>
                                    </tr>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td>
                                                <strong class="text-800"><?php echo htmlspecialchars($subject['subject_code']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($subject['descriptive_title']); ?></td>
                                            <td><span class="units-badge"><?php echo htmlspecialchars($subject['units']); ?></span></td>
                                            <td>Year <?php echo $subject['year_level']; ?></td>
                                            <td>Sem <?php echo $subject['semester']; ?></td>
                                            <td>
                                                <?php if ($subject['is_core'] == 1): ?>
                                                    <span class="core-badge">Core Subject</span>
                                                <?php else: ?>
                                                    <span class="program-badge"><?php echo htmlspecialchars($subject['program_code']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                            <?php endforeach; ?>
                            <?php } else { ?>
                                <!-- Single semester view -->
                                <?php foreach ($curriculum_data as $subject): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-800"><?php echo htmlspecialchars($subject['subject_code']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($subject['descriptive_title']); ?></td>
                                        <td><span class="units-badge"><?php echo htmlspecialchars($subject['units']); ?></span></td>
                                        <td>Year <?php echo $subject['year_level']; ?></td>
                                        <td>Sem <?php echo $subject['semester']; ?></td>
                                        <td>
                                            <?php if ($subject['is_core'] == 1): ?>
                                                <span class="core-badge">Core Subject</span>
                                            <?php else: ?>
                                                <span class="program-badge"><?php echo htmlspecialchars($subject['program_code']); ?></span>
                                            <?php endif; ?>
                                            </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php elseif (!$program_code): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5 class="text-muted">No Curriculum Program Found</h5>
                        <p class="text-muted">Your program "<?php echo htmlspecialchars($original_program); ?>" doesn't match any curriculum in the system.</p>
                        <p class="text-muted small">Please contact the administrator to add your program to the curriculum database.</p>
                    </div>
                <?php elseif ($program_code && !$has_curriculum): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Subjects Found for <?php echo htmlspecialchars($program_code); ?></h5>
                        <p class="text-muted">Your program is registered but no subjects have been added yet.</p>
                        <p class="text-muted small">Try changing the year level or semester filter, or contact the administrator.</p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No subjects found for the selected criteria.</h5>
                        <p class="text-muted">Try changing the year level or semester filter.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Table Footer -->
            <?php if ($program_code && $has_curriculum && !empty($curriculum_data)): ?>
            <div class="mt-3 text-end">
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing <?php echo count($curriculum_data); ?> subjects • Total Units: <?php echo $total_units; ?>
                    • Program: <?php echo htmlspecialchars($program_code); ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript -->
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
    </script>
</body>
</html>