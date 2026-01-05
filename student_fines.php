<?php
// student_fines.php - SIMPLIFIED VERSION (Only Fines & Penalties)
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection - SAME AS DASHBOARD
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

// Check if user is logged in - SAME AS DASHBOARD
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
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

// Function to get student fines
function getStudentFines($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT * FROM fines WHERE student_id = :student_id ORDER BY due_date ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to classify and summarize fines
function classifyAndSummarizeFines($fines) {
    $department_fines = [];
    $ssc_fines = [];
    $summary = [
        'department' => [
            'total' => 0,
            'overdue' => 0,
            'count' => 0,
            'overdue_count' => 0
        ],
        'ssc' => [
            'total' => 0,
            'overdue' => 0,
            'count' => 0,
            'overdue_count' => 0
        ]
    ];
    
    $current_date = date('Y-m-d');
    
    foreach ($fines as $fine) {
        $amount = floatval($fine['amount']);
        $fine_name = strtolower($fine['fine_name'] ?? '');
        
        // Classify fine based on fine_name
        $is_ssc_fine = false;
        
        // Check if this is an SSC fine (Student Services/Student Council)
        if (strpos($fine_name, 'ssc') !== false || 
            strpos($fine_name, 'student council') !== false ||
            strpos($fine_name, 'student services') !== false ||
            strpos($fine_name, 'organization') !== false ||
            strpos($fine_name, 'union') !== false ||
            strpos($fine_name, 'association') !== false ||
            strpos($fine_name, 'activity') !== false ||
            strpos($fine_name, 'event') !== false) {
            $is_ssc_fine = true;
        }
        
        if ($is_ssc_fine) {
            $ssc_fines[] = $fine;
            $summary['ssc']['total'] += $amount;
            $summary['ssc']['count']++;
            
            if ($fine['due_date'] < $current_date) {
                $summary['ssc']['overdue'] += $amount;
                $summary['ssc']['overdue_count']++;
            }
        } else {
            $department_fines[] = $fine;
            $summary['department']['total'] += $amount;
            $summary['department']['count']++;
            
            if ($fine['due_date'] < $current_date) {
                $summary['department']['overdue'] += $amount;
                $summary['department']['overdue_count']++;
            }
        }
    }
    
    return [
        'department_fines' => $department_fines,
        'ssc_fines' => $ssc_fines,
        'summary' => $summary
    ];
}

$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);
$all_fines = getStudentFines($student_id);
$fine_data = classifyAndSummarizeFines($all_fines);

$department_fines = $fine_data['department_fines'];
$ssc_fines = $fine_data['ssc_fines'];
$summary = $fine_data['summary'];

$has_fines = !empty($all_fines);
$has_department_fines = !empty($department_fines);
$has_ssc_fines = !empty($ssc_fines);

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

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return 'Not set';
    return date('F j, Y', strtotime($date));
}

function getStatusBadge($due_date = null) {
    $current_date = date('Y-m-d');
    $is_overdue = $due_date && ($due_date < $current_date);
    
    if ($is_overdue) {
        return '<span class="badge-overdue">Overdue</span>';
    } else {
        return '<span class="badge-pending">Pending</span>';
    }
}

function getFineCategoryBadge($fine_name) {
    $fine_name_lower = strtolower($fine_name);
    
    // Check if this is an SSC fine
    if (strpos($fine_name_lower, 'ssc') !== false || 
        strpos($fine_name_lower, 'student council') !== false ||
        strpos($fine_name_lower, 'student services') !== false ||
        strpos($fine_name_lower, 'organization') !== false ||
        strpos($fine_name_lower, 'union') !== false ||
        strpos($fine_name_lower, 'association') !== false ||
        strpos($fine_name_lower, 'activity') !== false ||
        strpos($fine_name_lower, 'event') !== false) {
        return '<span class="badge-ssc">SSC Fine</span>';
    } else {
       
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fines & Penalties - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* EXACT SAME STYLES AS DASHBOARD/PROFILE */
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
        
        /* STATUS BADGES */
        .badge-overdue {
            background: #dc3545;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #212529;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* FINE CATEGORY BADGES */
        .badge-department {
            background: #007bff;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-ssc {
            background: #6f42c1;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* TABLE STYLES */
        .table-container {
            overflow-x: auto;
        }
        
        .fine-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .fine-table thead {
            background: #800000;
            color: white;
        }
        
        .fine-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .fine-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.9rem;
        }
        
        .fine-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .fine-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .amount-cell {
            font-weight: 600;
            color: #800000;
        }
        
        /* CATEGORY SECTIONS */
        .category-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid;
            padding: 15px 20px;
            margin: 25px 0 15px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .category-header.department {
            border-left-color: #007bff;
        }
        
        .category-header.ssc {
            border-left-color: #6f42c1;
        }
        
        .category-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }
        
        .category-title i {
            margin-right: 10px;
        }
        
        .category-summary {
            font-size: 0.95rem;
            color: #666;
        }
        
        /* PRINT SLIP */
        #printSlip {
            display: none;
            font-family: Arial, sans-serif;
            padding: 20px;
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
            
            .section-title {
                font-size: 1.5rem;
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
            
            .fine-table th,
            .fine-table td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
            
            .category-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
                        <li><a class="dropdown-item" href="student_fees.php"><i class="fas fa-file-invoice-dollar me-2 text-msu"></i>Fees & Payments</a></li>
                        <li><a class="dropdown-item active" href="student_fines.php"><i class="fas fa-money-bill-wave me-2 text-msu"></i>Fines & Penalties</a></li>
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
                <a href="student_fines.php" class="nav-link active">
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

        <!-- Main Content - ONLY FINES & PENALTIES -->
        <div class="main-content">
            <!-- Page Title -->
            <h3 class="section-title"><i class="fas fa-money-bill-wave me-2"></i>Fines & Penalties</h3>

            <!-- Department Fines Section -->
            <?php if ($has_department_fines): ?>
            <div class="dashboard-card">
                <!-- Department Fines Header -->
                <div class="category-header department">
                    <div>
                        <h4 class="category-title"><i class="fas fa-university me-2"></i> Fines</h4>
                        <div class="category-summary">
                            <?php echo $summary['department']['count']; ?> fines • 
                            Total: <?php echo formatCurrency($summary['department']['total']); ?> • 
                            <span style="color: #dc3545;">Overdue: <?php echo formatCurrency($summary['department']['overdue']); ?></span>
                        </div>
                    </div>
                    <div>
                        
                    </div>
                </div>
                
                <!-- Department Fines Table -->
                <div class="table-container mt-4">
                    <table class="fine-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Event Date</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($department_fines as $fine): ?>
                                <tr>
                                    <td>
                                        <?php echo getFineCategoryBadge($fine['fine_name']); ?>
                                        <div class="mt-1"><strong><?php echo htmlspecialchars($fine['fine_name']); ?></strong></div>
                                        <small class="text-muted">ID: <?php echo $fine['fine_id']; ?></small>
                                    </td>
                                    <td><?php echo formatDate($fine['event_date']); ?></td>
                                    <td class="amount-cell"><?php echo formatCurrency($fine['amount']); ?></td>
                                    <td><?php echo formatDate($fine['due_date']); ?></td>
                                    <td>
                                        <?php echo getStatusBadge($fine['due_date']); ?>
                                        <?php if ($fine['due_date'] < date('Y-m-d')): ?>
                                            <br><small class="text-danger">Overdue</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-msu-sm" onclick="printPaymentSlip(
                                            '<?php echo $fine['fine_id']; ?>',
                                            '<?php echo addslashes($fine['fine_name']); ?>',
                                            <?php echo $fine['amount']; ?>,
                                            '<?php echo $fine['event_date']; ?>',
                                            '<?php echo $fine['due_date']; ?>',
                                            '<?php echo addslashes($fine['academic_year'] ?? ''); ?>',
                                            'department'
                                        )">
                                            <i class="fas fa-print me-1"></i> Print
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Department Summary Footer -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="text-primary">Department Total</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['department']['total']); ?></div>
                        </div>
                        <div class="col-6 mb-3">
                            <div style="color: #dc3545;">Overdue</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['department']['overdue']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- SSC Fines Section -->
            <?php if ($has_ssc_fines): ?>
            <div class="dashboard-card">
                <!-- SSC Fines Header -->
                <div class="category-header ssc">
                    <div>
                        <h4 class="category-title"><i class="fas fa-users me-2"></i>Student Services & Council (SSC) Fines</h4>
                        <div class="category-summary">
                            <?php echo $summary['ssc']['count']; ?> fines • 
                            Total: <?php echo formatCurrency($summary['ssc']['total']); ?> • 
                            <span style="color: #dc3545;">Overdue: <?php echo formatCurrency($summary['ssc']['overdue']); ?></span>
                        </div>
                    </div>
                    <div>
                        <span class="badge-ssc"><i class="fas fa-users me-1"></i> SSC</span>
                    </div>
                </div>
                
                <!-- SSC Fines Table -->
                <div class="table-container mt-4">
                    <table class="fine-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Event Date</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ssc_fines as $fine): ?>
                                <tr>
                                    <td>
                                        <?php echo getFineCategoryBadge($fine['fine_name']); ?>
                                        <div class="mt-1"><strong><?php echo htmlspecialchars($fine['fine_name']); ?></strong></div>
                                        <small class="text-muted">ID: <?php echo $fine['fine_id']; ?></small>
                                    </td>
                                    <td><?php echo formatDate($fine['event_date']); ?></td>
                                    <td class="amount-cell"><?php echo formatCurrency($fine['amount']); ?></td>
                                    <td><?php echo formatDate($fine['due_date']); ?></td>
                                    <td>
                                        <?php echo getStatusBadge($fine['due_date']); ?>
                                        <?php if ($fine['due_date'] < date('Y-m-d')): ?>
                                            <br><small class="text-danger">Overdue</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-msu-sm" onclick="printPaymentSlip(
                                            '<?php echo $fine['fine_id']; ?>',
                                            '<?php echo addslashes($fine['fine_name']); ?>',
                                            <?php echo $fine['amount']; ?>,
                                            '<?php echo $fine['event_date']; ?>',
                                            '<?php echo $fine['due_date']; ?>',
                                            '<?php echo addslashes($fine['academic_year'] ?? ''); ?>',
                                            'ssc'
                                        )">
                                            <i class="fas fa-print me-1"></i> Print
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- SSC Summary Footer -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div style="color: #6f42c1;">SSC Total</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['ssc']['total']); ?></div>
                        </div>
                        <div class="col-6 mb-3">
                            <div style="color: #dc3545;">Overdue</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['ssc']['overdue']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- No Fines Message -->
            <?php if (!$has_fines): ?>
            <div class="dashboard-card">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-3x text-success opacity-50"></i>
                    </div>
                    <h5 class="text-muted">No Fines Found</h5>
                    <p class="text-muted">You currently have no fines assigned to your account.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Printable Payment Slip (Hidden) -->
    <div id="printSlip" style="display: none; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
        <div style="border: 2px solid #800000; padding: 25px; border-radius: 10px;">
            <!-- Header -->
            <div style="text-align: center; border-bottom: 3px solid #800000; padding-bottom: 20px; margin-bottom: 25px;">
                <h1 style="color: #800000; margin: 0; font-size: 28px;">MINDANAO STATE UNIVERSITY</h1>
                <h2 style="color: #5a0000; margin: 5px 0; font-size: 22px;">MSU BUUG CAMPUS</h2>
                <h3 style="color: #000; margin: 0; font-size: 20px;">College Treasurer's Office</h3>
                <h4 style="color: #800000; margin: 10px 0 0 0; font-size: 18px; font-weight: bold;">FINE PAYMENT SLIP</h4>
            </div>
            
            <!-- Student Information -->
            <div style="margin-bottom: 25px;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <tr>
                        <td style="width: 30%; padding: 8px 0; border-bottom: 1px solid #ddd;"><strong>Student ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($student_id); ?></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #ddd;"><strong>Date Printed:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #ddd;"><?php echo date('F j, Y'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #ddd;"><strong>Student Name:</strong></td>
                        <td colspan="3" style="padding: 8px 0; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($student_info['name']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong>Course/Program:</strong></td>
                        <td colspan="3" style="padding: 8px 0;"><?php echo htmlspecialchars($student_info['course']); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Fine Details -->
            <div style="margin-bottom: 25px;">
                <div style="background-color: #800000; color: white; padding: 10px; text-align: center; font-weight: bold; margin-bottom: 15px;">
                    FINE PAYMENT DETAILS
                </div>
                <div id="slipContent"></div>
            </div>
            
            <!-- Footer -->
            <div style="text-align: center; border-top: 2px solid #800000; padding-top: 15px;">
                <p style="margin: 5px 0; font-size: 14px;"><strong>Office Hours:</strong> Monday-Friday: 8:00 AM - 5:00 PM | Saturday: 8:00 AM - 12:00 PM</p>
                <p style="margin: 5px 0; font-size: 14px;"><strong>Location:</strong> College Treasurer's Office, Main Building, Ground Floor</p>
                <p style="margin: 5px 0; font-size: 12px; color: #666;"><em>This payment slip is computer-generated. No signature required.</em></p>
            </div>
        </div>
    </div>

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

        // Print payment slip function
        function printPaymentSlip(fineId, fineName, amount, eventDate, dueDate, academicYear, category) {
            const slipContent = document.getElementById('slipContent');
            const formattedAmount = parseFloat(amount).toFixed(2);
            
            // Format date
            const formatDate = (dateString) => {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            };
            
            // Determine category color
            let categoryColor = category === 'ssc' ? '#6f42c1' : '#007bff';
            let categoryName = category === 'ssc' ? 'SSC Fine' : 'Department Fine';
            
            slipContent.innerHTML = `
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Fine ID</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 35%;">Description</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Event Date</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Due Date</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 20%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">${fineId}</td>
                            <td style="border: 1px solid #ddd; padding: 10px;">
                                <span style="background-color: ${categoryColor}; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                    ${categoryName}
                                </span>
                                <div style="margin-top: 5px;">${fineName}</div>
                            </td>
                            <td style="border: 1px solid #ddd; padding: 10px;">${formatDate(eventDate)}</td>
                            <td style="border: 1px solid #ddd; padding: 10px;">${formatDate(dueDate)}</td>
                            <td style="border: 1px solid #ddd; padding: 10px; font-weight: bold;">₱${formattedAmount}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="background-color: #f8f9fa; border-left: 4px solid #800000; padding: 15px; margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="margin: 0; font-size: 16px;"><strong>Academic Year:</strong> ${academicYear}</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px;"><strong>Category:</strong> ${categoryName}</p>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 0; font-size: 14px; color: #666;">Amount to Pay</p>
                            <h2 style="margin: 5px 0 0 0; color: #800000; font-size: 24px;">₱${formattedAmount}</h2>
                        </div>
                    </div>
                </div>
            `;
            
            // Store current display state
            const mainContent = document.querySelector('.main-content');
            const printSlip = document.getElementById('printSlip');
            
            // Hide main content and show slip
            mainContent.style.display = 'none';
            printSlip.style.display = 'block';
            
            // Print the slip
            setTimeout(() => {
                window.print();
                
                // Restore display after printing
                setTimeout(() => {
                    mainContent.style.display = 'block';
                    printSlip.style.display = 'none';
                }, 500);
            }, 500);
        }
    </script>
</body>
</html>