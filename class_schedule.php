<?php
// class_schedule.php - TABLE DESIGN VERSION

session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

// Database connection - SAME AS OTHER MODULES
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

// Function to get student data - SAME AS OTHER MODULES
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

// Function to get student class schedule - FOR TABLE DISPLAY
function getStudentSchedule($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    s.subject_code,
                    c.section,
                    s.units,
                    s.subject_name as descriptive_title,
                    cs.day,
                    TIME_FORMAT(cs.start_time, '%h:%i %p') as start_time,
                    TIME_FORMAT(cs.end_time, '%h:%i %p') as end_time,
                    cs.room,
                    CONCAT(f.first_name, ' ', f.last_name) as instructor
                  FROM enrollments e
                  JOIN classes c ON e.class_id = c.class_id
                  JOIN subjects s ON c.subject_id = s.subject_id
                  JOIN faculty f ON c.faculty_id = f.faculty_id
                  JOIN class_schedule cs ON c.class_id = cs.class_id
                  WHERE e.student_id = :student_id
                  AND e.status = 'Active'
                  AND c.status = 'active'
                  ORDER BY 
                    CASE cs.day
                        WHEN 'Monday' THEN 1
                        WHEN 'Tuesday' THEN 2
                        WHEN 'Wednesday' THEN 3
                        WHEN 'Thursday' THEN 4
                        WHEN 'Friday' THEN 5
                        WHEN 'Saturday' THEN 6
                        ELSE 7
                    END,
                    cs.start_time,
                    s.subject_code";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get schedule summary
function getScheduleSummary($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    COUNT(DISTINCT CONCAT(s.subject_code, '-', c.section)) as total_subjects,
                    SUM(s.units) as total_units,
                    COUNT(DISTINCT cs.schedule_id) as total_classes
                  FROM enrollments e
                  JOIN classes c ON e.class_id = c.class_id
                  JOIN subjects s ON c.subject_id = s.subject_id
                  JOIN class_schedule cs ON c.class_id = cs.class_id
                  WHERE e.student_id = :student_id
                  AND e.status = 'Active'
                  AND c.status = 'active'";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return ['total_subjects' => 0, 'total_units' => 0, 'total_classes' => 0];
}

// Fetch student data - SAME AS OTHER MODULES
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

// Fetch schedule
$schedule = getStudentSchedule($student_id);
$summary = getScheduleSummary($student_id);

// Helper functions - SAME AS OTHER MODULES
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

// Get today's day for highlighting
$today = date('l');
$day_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* EXACT SAME STYLES AS MY_SUBJECT AND GRADES */
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
        
        /* NAVIGATION - SAME AS MY_SUBJECT */
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
        
        /* SIDEBAR - EXACT SAME */
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
        
        /* CARDS - SAME AS MY_SUBJECT */
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
        
        /* TABLE STYLING - EXACT SAME AS MY_SUBJECT */
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
        
        /* TODAY'S CLASS HIGHLIGHT */
        .today-class {
            background: #f8fff8 !important;
            border-left: 4px solid #28a745 !important;
        }
        
        .today-class:hover {
            background: #f0fff0 !important;
        }
        
        /* DAY BADGE STYLING */
        .day-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .day-monday { background: #e3f2fd; color: #1565c0; }
        .day-tuesday { background: #f3e5f5; color: #7b1fa2; }
        .day-wednesday { background: #e8f5e9; color: #2e7d32; }
        .day-thursday { background: #fff3e0; color: #ef6c00; }
        .day-friday { background: #fce4ec; color: #c2185b; }
        .day-saturday { background: #f5f5f5; color: #616161; }
        
        .day-today {
            background: #28a745 !important;
            color: white !important;
            font-weight: 700;
        }
        
        /* TIME STYLING */
        .schedule-time {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #800000;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }
        
        /* PERFECT CIRCLE USER AVATAR - SAME AS MY_SUBJECT */
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
        
        /* BUTTONS - SAME AS MY_SUBJECT */
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
        
        /* BADGES - SAME AS MY_SUBJECT */
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
        
        /* SECTION TITLE - SAME AS MY_SUBJECT */
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
        
        /* EMPTY STATE - SAME AS MY_SUBJECT */
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
        
        /* SCHEDULE SUMMARY - SAME STYLE */
        .schedule-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
            margin-top: 1.5rem;
            border: 1px solid #e9ecef;
        }
        
        /* ===== DESKTOP STYLES (992px and up) - SAME AS MY_SUBJECT ===== */
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
            
            .table-msu thead th {
                padding: 18px 15px;
            }
            
            .table-msu tbody td {
                padding: 16px 15px;
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
            
            .table-msu thead th,
            .table-msu tbody td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
            
            .day-badge {
                font-size: 0.7rem;
                padding: 3px 6px;
            }
            
            .schedule-time {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar - EXACTLY SAME AS MY_SUBJECT -->
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
                        <!-- PERFECT CIRCLE PROFILE PICTURE - SAME AS MY_SUBJECT -->
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
                        <li><a class="dropdown-item" href="my_subject.php"><i class="fas fa-book me-2 text-msu"></i>My Subjects</a></li>
                        <li><a class="dropdown-item active" href="class_schedule.php"><i class="fas fa-calendar-alt me-2 text-msu"></i>Class Schedule</a></li>
                        <li><a class="dropdown-item" href="student_grades.php"><i class="fas fa-chart-line me-2 text-msu"></i>Grades & Progress</a></li>
                        <li><a class="dropdown-item" href="student_settings.php"><i class="fas fa-cog me-2 text-msu"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="student_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay - SAME AS MY_SUBJECT -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Layout - SAME STRUCTURE AS MY_SUBJECT -->
    <div class="d-flex">
        <!-- Sidebar - EXACTLY SAME AS MY_SUBJECT -->
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
                <a href="my_subject.php" class="nav-link">
                    <i class="fas fa-book"></i> My Subjects
                </a>
                <a href="student_grades.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Grades & Progress
                </a>
                <a href="class_schedule.php" class="nav-link active">
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

        <!-- Main Content - TABLE DESIGN (LIKE MY_SUBJECT) -->
        <div class="main-content">
            <!-- Module Header - SAME STYLE AS MY_SUBJECT -->
            <div class="module-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="section-title mb-2">
                            <i class="fas fa-calendar-alt me-2"></i>Class Schedule
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
                        <?php if (count($schedule) > 0): ?>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-clock"></i>
                                <?php echo $summary['total_classes']; ?> Classes
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Class Schedule Card - TABLE DESIGN (LIKE MY_SUBJECT) -->
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-week me-2 text-msu"></i>
                        Weekly Class Schedule
                    </h5>
                    <div>
                        <button class="btn btn-msu-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print Schedule
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($schedule)): ?>
                    <!-- Schedule Table - EXACT SAME STYLE AS MY_SUBJECT TABLE -->
                    <div class="table-responsive">
                        <table class="table table-msu table-hover">
                            <thead>
                                <tr>
                                    <th><i class=></i>Subject Code</th>
                                    <th><i class=></i>Subject Name</th>
                                    <th><i class=></i>Section</th>
                                    <th><i class=></i>Units</th>
                                    <th><i class=></i>Day</th>
                                    <th><i class=></i>Time</th>
                                    <th><i class=>Room</th>
                                    <th><i class=></i>Instructor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedule as $class): 
                                    $is_today = ($class['day'] === $today);
                                ?>
                                <tr class="<?php echo $is_today ? 'today-class' : ''; ?>">
                                    <td>
                                        <strong class="text-msu"><?php echo htmlspecialchars($class['subject_code']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="subject-name"><?php echo htmlspecialchars($class['descriptive_title']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($class['section']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($class['units']); ?> unit<?php echo $class['units'] > 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $day_class = 'day-' . strtolower($class['day']);
                                        if ($is_today) {
                                            $day_class .= ' day-today';
                                        }
                                        ?>
                                        <span class="day-badge <?php echo $day_class; ?>">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <?php echo htmlspecialchars($class['day']); ?>
                                            <?php if ($is_today): ?>
                                                <i class="fas fa-star ms-1"></i>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="schedule-time">
                                            <?php echo htmlspecialchars($class['start_time']); ?> - <?php echo htmlspecialchars($class['end_time']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo htmlspecialchars($class['room']); ?></span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($class['instructor']); ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Schedule Summary - SAME STYLE AS MY_SUBJECT -->
                    <div class="schedule-summary">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="summary-item">
                                    <strong>Total Subjects:</strong>
                                    <span class="text-msu ms-2"><?php echo $summary['total_subjects']; ?></span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="summary-item">
                                    <strong>Total Units:</strong>
                                    <span class="text-msu ms-2"><?php echo number_format($summary['total_units'], 1); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="summary-item">
                                    <strong>Total Classes:</strong>
                                    <span class="text-msu ms-2"><?php echo $summary['total_classes']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Green highlighted rows indicate today's classes (<?php echo $today; ?>). Schedule is sorted by day and time.
                            </small>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty State - SAME AS MY_SUBJECT -->
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h6 class="mt-3 mb-2 text-muted">No Schedule Available</h6>
                        <p class="text-muted mb-0">You are not enrolled in any subjects for this term.</p>
                        <a href="my_subject.php" class="btn btn-msu-sm mt-3">
                            <i class="fas fa-book me-1"></i>Enroll in Subjects
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript - SAME AS MY_SUBJECT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle - SAME AS MY_SUBJECT
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
            
            // Add click effect to today's classes
            const todayRows = document.querySelectorAll('.today-class');
            todayRows.forEach(row => {
                row.addEventListener('click', function() {
                    const subject = this.querySelector('.text-msu').textContent;
                    const time = this.querySelector('.schedule-time').textContent;
                    alert(`You have class today!\n\nSubject: ${subject}\nTime: ${time}`);
                });
            });
        });
    </script>
</body>
</html>