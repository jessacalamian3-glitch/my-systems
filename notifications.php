<?php
// ==================== SESSION & SECURITY ====================
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in - USING SAME METHOD AS STUDENT_PROFILE.PHP
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

// ==================== DATABASE CONNECTION ====================
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

// Function to get student data - SAME AS DASHBOARD
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

$student_id = $_SESSION['username'] ?? '';
$student_data = getStudentData($student_id);

// Fetch student data and set session info - SAME AS DASHBOARD
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

// ==================== ASSIGNMENT FUNCTIONS ====================
function getStudentClasses($db, $student_id) {
    $query = "SELECT c.class_id, subj.subject_code, c.section, 
                     subj.subject_name, CONCAT(f.first_name, ' ', f.last_name) as instructor_name
              FROM enrollments e
              JOIN classes c ON e.class_id = c.class_id
              JOIN subjects subj ON c.subject_id = subj.subject_id
              JOIN faculty f ON c.faculty_id = f.faculty_id
              WHERE e.student_id = :student_id
              ORDER BY subj.subject_code";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAssignmentDetails($db, $assignment_id, $student_id) {
    $query = "SELECT a.*, ac.category_name, subj.subject_code, c.section,
                     subj.subject_name, CONCAT(f.first_name, ' ', f.last_name) as instructor_name,
                     f.email as instructor_email, sub.submission_id, sub.submitted_at,
                     sub.file_path, sub.file_name, sub.score, sub.feedback, sub.status
              FROM assignments a
              JOIN assignment_categories ac ON a.category_id = ac.category_id
              JOIN classes c ON a.class_id = c.class_id
              JOIN subjects subj ON c.subject_id = subj.subject_id
              JOIN faculty f ON c.faculty_id = f.faculty_id
              LEFT JOIN assignment_submissions sub ON a.assignment_id = sub.assignment_id 
                AND sub.student_id = :student_id
              WHERE a.assignment_id = :assignment_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':student_id' => $student_id, ':assignment_id' => $assignment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllAssignments($db, $student_id, $class_id = null) {
    $where = $class_id ? "AND c.class_id = :class_id" : "";
    $params = [':student_id' => $student_id];
    
    $query = "SELECT a.assignment_id, a.title, a.description, a.deadline, a.max_score,
                     subj.subject_code, c.section, subj.subject_name,
                     CONCAT(f.first_name, ' ', f.last_name) as instructor_name,
                     ac.category_name, sub.submission_id, sub.submitted_at,
                     sub.score, sub.status
              FROM enrollments e
              JOIN classes c ON e.class_id = c.class_id
              JOIN assignments a ON c.class_id = a.class_id
              JOIN subjects subj ON c.subject_id = subj.subject_id
              JOIN faculty f ON c.faculty_id = f.faculty_id
              JOIN assignment_categories ac ON a.category_id = ac.category_id
              LEFT JOIN assignment_submissions sub ON a.assignment_id = sub.assignment_id 
                AND sub.student_id = :student_id
              WHERE e.student_id = :student_id $where
              ORDER BY a.deadline ASC";
    
    $stmt = $db->prepare($query);
    if ($class_id) {
        $params[':class_id'] = $class_id;
    }
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== GET DATA ====================
$database = new Database();
$db = $database->getConnection();

// Check if database connection is successful
if (!$db) {
    die("Database connection failed. Please try again later.");
}

$classes = getStudentClasses($db, $student_id);
$selected_class = $_GET['class_id'] ?? null;
$selected_assignment = $_GET['assignment_id'] ?? null;
$assignments = getAllAssignments($db, $student_id, $selected_class);

if ($selected_assignment) {
    $assignment_details = getAssignmentDetails($db, $selected_assignment, $student_id);
}

// Helper functions - SAME AS DASHBOARD
function getDisplayName($info) {
    if (!$info) return 'Student';
    return htmlspecialchars($info['name'] ?? 'Student');
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
    <title>My Assignments - MSU Buug</title>
    
    <!-- Bootstrap 5 & Font Awesome - SAME AS DASHBOARD -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* EXACT SAME BASE STYLES AS STUDENT_PROFILE.PHP */
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
        
        /* CARDS - SAME AS DASHBOARD */
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
        
        .btn-outline-msu {
            background: transparent;
            color: #800000;
            border: 2px solid #800000;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-msu:hover {
            background: #800000;
            color: white;
        }
        
        .section-title {
            border-left: 4px solid #800000;
            padding-left: 15px;
            margin: 25px 0 20px 0;
            color: #800000;
        }
        
        .mobile-user-info {
            display: none;
            background: #5a0000;
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        /* ===== ASSIGNMENT SPECIFIC STYLES ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 4px solid;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-card.overdue { border-color: #dc3545; }
        .stats-card.due-today { border-color: #ffc107; }
        .stats-card.upcoming { border-color: #17a2b8; }
        .stats-card.submitted { border-color: #28a745; }
        
        .stats-icon {
            font-size: 2.2rem;
            margin-bottom: 12px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            line-height: 1;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Table styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .data-table thead {
            background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
        }
        
        .data-table th {
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: none;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .data-table tbody tr:hover {
            background: rgba(128, 0, 0, 0.03);
        }
        
        /* Badges */
        .badge {
            padding: 6px 10px;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        
        .badge-overdue { background: #dc3545; color: white; }
        .badge-due-today { background: #ffc107; color: #212529; }
        .badge-upcoming { background: #17a2b8; color: white; }
        .badge-submitted { background: #28a745; color: white; }
        .badge-graded { background: #6f42c1; color: white; }
        
        /* Assignment Details */
        .assignment-header {
            background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 25px;
        }
        
        .assignment-body {
            background: white;
            border-radius: 0 0 10px 10px;
            padding: 25px;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #800000;
        }
        
        /* ===== DESKTOP STYLES (992px and up) ===== */
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
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* ===== LARGE DESKTOP STYLES (1200px and up) ===== */
        @media (min-width: 1200px) {
            .main-content {
                padding: 40px;
            }
            
            .dashboard-card {
                padding: 2rem;
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
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .sidebar {
                width: 260px;
                left: -260px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar - EXACTLY SAME AS DASHBOARD -->
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
                        <li><a class="dropdown-item" href="student_dashboard.php"><i class="fas fa-tachometer-alt me-2 text-msu"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="student_profile.php"><i class="fas fa-user me-2 text-msu"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="student_settings.php"><i class="fas fa-cog me-2 text-msu"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="student_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay - SAME AS DASHBOARD -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Layout - SAME STRUCTURE AS DASHBOARD -->
    <div class="d-flex">
        <!-- Sidebar - EXACTLY SAME AS DASHBOARD -->
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
                <a href="notifications.php" class="nav-link active">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if (count($assignments) > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo count($assignments); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="dashboard-card mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-2">
                            <i class="fas fa-bell me-2 text-maroon"></i>Notifications & Announcements
                        </h2>
                        <p class="mb-0 text-muted">View your assignments and announcements (View Only)</p>
                    </div>
                    
                    <?php if ($selected_class): ?>
                        <a href="notifications.php" class="btn btn-outline-msu btn-sm">
                            <i class="fas fa-times me-1"></i> Clear Filter
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <?php if (!$selected_assignment): ?>
            <?php
            $stats = [
                'overdue' => 0,
                'due_today' => 0,
                'upcoming' => 0,
                'submitted' => 0
            ];
            
            $now = time();
            foreach ($assignments as $assignment) {
                $deadline = strtotime($assignment['deadline']);
                
                if ($assignment['submission_id']) {
                    $stats['submitted']++;
                } elseif ($now > $deadline) {
                    $stats['overdue']++;
                } elseif (date('Y-m-d', $now) == date('Y-m-d', $deadline)) {
                    $stats['due_today']++;
                } elseif ($now < $deadline) {
                    $stats['upcoming']++;
                }
            }
            ?>
            
            <div class="stats-grid">
                <div class="stats-card overdue">
                    <div class="stats-icon text-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stats-number text-danger"><?php echo $stats['overdue']; ?></div>
                    <div class="stats-label">Overdue</div>
                </div>
                
                <div class="stats-card due-today">
                    <div class="stats-icon text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number text-warning"><?php echo $stats['due_today']; ?></div>
                    <div class="stats-label">Due Today</div>
                </div>
                
                <div class="stats-card upcoming">
                    <div class="stats-icon text-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-number text-info"><?php echo $stats['upcoming']; ?></div>
                    <div class="stats-label">Upcoming</div>
                </div>
                
                <div class="stats-card submitted">
                    <div class="stats-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number text-success"><?php echo $stats['submitted']; ?></div>
                    <div class="stats-label">Submitted</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Class Filter -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <h5 class="text-maroon mb-3"><i class="fas fa-filter me-2"></i>Filter by Class</h5>
                    <form method="GET" class="d-flex gap-2">
                        <select name="class_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['class_id']; ?>" 
                                    <?php echo ($selected_class == $class['class_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['subject_code'] . ' - Section ' . $class['section'] . ' (' . $class['instructor_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="btn btn-outline-msu">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($selected_assignment && isset($assignment_details)): ?>
            <!-- VIEW ASSIGNMENT (VIEW ONLY) -->
            <div class="dashboard-card">
                <div class="assignment-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-2"><?php echo htmlspecialchars($assignment_details['title'] ?? 'Assignment'); ?></h4>
                            <p class="mb-0">
                                <i class="fas fa-book me-2"></i>
                                <?php echo htmlspecialchars($assignment_details['subject_code'] ?? ''); ?> - 
                                Section <?php echo htmlspecialchars($assignment_details['section'] ?? ''); ?>
                            </p>
                        </div>
                        <div>
                            <?php
                            $deadline = strtotime($assignment_details['deadline'] ?? '');
                            $now = time();
                            
                            if ($assignment_details['submission_id'] ?? false) {
                                if ($assignment_details['score'] !== null) {
                                    echo '<span class="badge badge-graded">Graded</span>';
                                } else {
                                    echo '<span class="badge badge-submitted">Submitted</span>';
                                }
                            } elseif ($now > $deadline) {
                                echo '<span class="badge badge-overdue">Overdue</span>';
                            } elseif (date('Y-m-d', $now) == date('Y-m-d', $deadline)) {
                                echo '<span class="badge badge-due-today">Due Today</span>';
                            } else {
                                echo '<span class="badge badge-upcoming">Upcoming</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="assignment-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="text-maroon mb-3"><i class="fas fa-info-circle me-2"></i>Assignment Details</h6>
                                <div class="mb-2">
                                    <strong>Category:</strong>
                                    <span class="badge bg-maroon ms-2"><?php echo htmlspecialchars($assignment_details['category_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="mb-2">
                                    <strong>Max Score:</strong>
                                    <span class="fw-bold"><?php echo $assignment_details['max_score'] ?? 0; ?> points</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Created:</strong>
                                    <?php echo date('M j, Y g:i A', strtotime($assignment_details['created_at'] ?? '')); ?>
                                </div>
                                <div>
                                    <strong>Deadline:</strong>
                                    <?php echo date('M j, Y g:i A', strtotime($assignment_details['deadline'] ?? '')); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="text-maroon mb-3"><i class="fas fa-user-tie me-2"></i>Instructor</h6>
                                <div class="mb-2">
                                    <strong>Name:</strong>
                                    <?php echo htmlspecialchars($assignment_details['instructor_name'] ?? 'N/A'); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Email:</strong>
                                    <a href="mailto:<?php echo htmlspecialchars($assignment_details['instructor_email'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($assignment_details['instructor_email'] ?? ''); ?>
                                    </a>
                                </div>
                                <div>
                                    <strong>Subject:</strong>
                                    <?php echo htmlspecialchars($assignment_details['subject_name'] ?? ''); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="info-card">
                        <h6 class="text-maroon mb-3"><i class="fas fa-file-alt me-2"></i>Description</h6>
                        <p><?php echo nl2br(htmlspecialchars($assignment_details['description'] ?? 'No description provided.')); ?></p>
                    </div>
                    
                    <!-- Submission Status -->
                    <div class="info-card">
                        <h6 class="text-maroon mb-3"><i class="fas fa-paper-plane me-2"></i>Submission Status</h6>
                        
                        <?php if ($assignment_details['submission_id'] ?? false): ?>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Submitted on:</strong> <?php echo date('M j, Y g:i A', strtotime($assignment_details['submitted_at'] ?? '')); ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Submitted File:</strong>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div>
                                        <i class="fas fa-file me-2 text-maroon"></i>
                                        <?php echo htmlspecialchars($assignment_details['file_name'] ?? ''); ?>
                                    </div>
                                    <?php if (!empty($assignment_details['file_path'])): ?>
                                    <div>
                                        <a href="<?php echo htmlspecialchars($assignment_details['file_path'] ?? '#'); ?>" 
                                           class="btn btn-msu-sm" download>
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($assignment_details['score'] !== null): ?>
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Grade:</strong> 
                                            <span class="fw-bold"><?php echo $assignment_details['score']; ?>/<?php echo $assignment_details['max_score']; ?></span>
                                        </div>
                                        <div>
                                            <span class="badge bg-info">Graded</span>
                                        </div>
                                    </div>
                                    <?php if (!empty($assignment_details['feedback'])): ?>
                                        <div class="mt-2">
                                            <strong>Feedback:</strong>
                                            <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($assignment_details['feedback'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-hourglass-half me-2"></i>
                                    Your submission is pending review by the instructor.
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Not Submitted Yet</strong>
                            </div>
                            
                            <div class="text-center py-3">
                                <p class="text-muted"><em>This is a view-only page. To submit assignments, please use the classroom portal.</em></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <a href="notifications.php" class="btn btn-outline-msu">
                            <i class="fas fa-arrow-left me-1"></i> Back to Notifications
                        </a>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- NOTIFICATIONS TABLE (VIEW ONLY) -->
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="text-maroon">
                            <i class="fas fa-tasks me-2"></i>All Assignments
                            <?php if ($selected_class): ?>
                                <small class="ms-2 text-muted">Filtered by selected class</small>
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-maroon"><?php echo count($assignments); ?> assignments</span>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($assignments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Assignments Found</h5>
                            <p>You don't have any assignments at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Class</th>
                                        <th>Deadline</th>
                                        <th>Max Score</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): 
                                        $deadline = strtotime($assignment['deadline']);
                                        $now = time();
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                            <?php if (!empty($assignment['description'])): ?>
                                                <br>
                                                <small class="text-muted"><?php echo substr(htmlspecialchars($assignment['description']), 0, 50); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($assignment['subject_code']); ?><br>
                                            <small class="text-muted">Sec <?php echo htmlspecialchars($assignment['section']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', $deadline); ?><br>
                                            <small class="text-muted"><?php echo date('g:i A', $deadline); ?></small>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?php echo $assignment['max_score']; ?></span> pts
                                        </td>
                                        <td>
                                            <?php
                                            if ($assignment['submission_id']) {
                                                if ($assignment['score'] !== null) {
                                                    echo '<span class="badge badge-graded">Graded (' . $assignment['score'] . '/' . $assignment['max_score'] . ')</span>';
                                                } else {
                                                    echo '<span class="badge badge-submitted">Submitted</span>';
                                                }
                                            } elseif ($now > $deadline) {
                                                $days = floor(($now - $deadline) / 86400);
                                                echo '<span class="badge badge-overdue">Overdue (' . $days . 'd)</span>';
                                            } elseif (date('Y-m-d', $now) == date('Y-m-d', $deadline)) {
                                                $hours = floor(($deadline - $now) / 3600);
                                                echo '<span class="badge badge-due-today">Due Today (' . $hours . 'h)</span>';
                                            } else {
                                                echo '<span class="badge badge-upcoming">Upcoming</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="notifications.php?assignment_id=<?php echo $assignment['assignment_id']; ?>" 
                                                   class="btn btn-sm btn-outline-maroon">
                                                    <i class="fas fa-eye me-1"></i> View Details
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Note for students -->
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This is a view-only page for checking assignments. 
                            To submit assignments, please use the designated classroom portal or follow your instructor's submission guidelines.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript - SAME AS DASHBOARD -->
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
    </script>
</body>
</html>