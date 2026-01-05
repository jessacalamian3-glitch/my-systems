<?php
// student_fees.php - FIXED VERSION (No fee_category column error)
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

// Function to get student fees - REMOVED fee_category from ORDER BY
function getStudentFees($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT * FROM fees WHERE student_id = :student_id ORDER BY due_date ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to classify and summarize fees - BASED ON fee_type only
function classifyAndSummarizeFees($fees) {
    $department_fees = [];
    $ssc_fees = [];
    $summary = [
        'department' => [
            'total' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'total_count' => 0,
            'paid_count' => 0,
            'unpaid_count' => 0,
            'overdue_count' => 0
        ],
        'ssc' => [
            'total' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'total_count' => 0,
            'paid_count' => 0,
            'unpaid_count' => 0,
            'overdue_count' => 0
        ],
        'overall' => [
            'total' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'total_count' => 0,
            'paid_count' => 0,
            'unpaid_count' => 0,
            'overdue_count' => 0
        ]
    ];
    
    $current_date = date('Y-m-d');
    
    foreach ($fees as $fee) {
        $amount = floatval($fee['amount']);
        $fee_type = strtolower($fee['fee_type'] ?? '');
        
        // Overall summary
        $summary['overall']['total'] += $amount;
        $summary['overall']['total_count']++;
        
        // Classify fee based on fee_type
        $is_ssc_fee = false;
        
        // Check if this is an SSC fee (Student Services/Student Council)
        if (strpos($fee_type, 'ssc') !== false || 
            strpos($fee_type, 'student council') !== false ||
            strpos($fee_type, 'student services') !== false ||
            strpos($fee_type, 'organization') !== false ||
            strpos($fee_type, 'union') !== false ||
            strpos($fee_type, 'association') !== false) {
            $is_ssc_fee = true;
        }
        
        if ($is_ssc_fee) {
            $ssc_fees[] = $fee;
            $summary['ssc']['total'] += $amount;
            $summary['ssc']['total_count']++;
            
            if ($fee['status'] == 'Paid') {
                $summary['ssc']['paid'] += $amount;
                $summary['ssc']['paid_count']++;
                $summary['overall']['paid'] += $amount;
                $summary['overall']['paid_count']++;
            } else {
                $summary['ssc']['unpaid'] += $amount;
                $summary['ssc']['unpaid_count']++;
                $summary['overall']['unpaid'] += $amount;
                $summary['overall']['unpaid_count']++;
                
                if ($fee['due_date'] < $current_date) {
                    $summary['ssc']['overdue'] += $amount;
                    $summary['ssc']['overdue_count']++;
                    $summary['overall']['overdue'] += $amount;
                    $summary['overall']['overdue_count']++;
                }
            }
        } else {
            $department_fees[] = $fee;
            $summary['department']['total'] += $amount;
            $summary['department']['total_count']++;
            
            if ($fee['status'] == 'Paid') {
                $summary['department']['paid'] += $amount;
                $summary['department']['paid_count']++;
                $summary['overall']['paid'] += $amount;
                $summary['overall']['paid_count']++;
            } else {
                $summary['department']['unpaid'] += $amount;
                $summary['department']['unpaid_count']++;
                $summary['overall']['unpaid'] += $amount;
                $summary['overall']['unpaid_count']++;
                
                if ($fee['due_date'] < $current_date) {
                    $summary['department']['overdue'] += $amount;
                    $summary['department']['overdue_count']++;
                    $summary['overall']['overdue'] += $amount;
                    $summary['overall']['overdue_count']++;
                }
            }
        }
    }
    
    return [
        'department_fees' => $department_fees,
        'ssc_fees' => $ssc_fees,
        'summary' => $summary
    ];
}

$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);
$all_fees = getStudentFees($student_id);
$fee_data = classifyAndSummarizeFees($all_fees);

$department_fees = $fee_data['department_fees'];
$ssc_fees = $fee_data['ssc_fees'];
$summary = $fee_data['summary'];

$has_fees = !empty($all_fees);
$has_department_fees = !empty($department_fees);
$has_ssc_fees = !empty($ssc_fees);

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

function getStatusBadge($status, $due_date = null) {
    $current_date = date('Y-m-d');
    $is_overdue = $due_date && ($due_date < $current_date);
    
    if ($status == 'Paid') {
        return '<span class="badge-paid">Paid</span>';
    } elseif ($is_overdue) {
        return '<span class="badge-overdue">Overdue</span>';
    } else {
        return '<span class="badge-pending">Pending</span>';
    }
}

function getFeeCategoryBadge($fee_type) {
    $fee_type_lower = strtolower($fee_type);
    
    // Check if this is an SSC fee
    if (strpos($fee_type_lower, 'ssc') !== false || 
        strpos($fee_type_lower, 'student council') !== false ||
        strpos($fee_type_lower, 'student services') !== false ||
        strpos($fee_type_lower, 'organization') !== false ||
        strpos($fee_type_lower, 'union') !== false ||
        strpos($fee_type_lower, 'association') !== false) {
        return '<span class="badge-ssc">SSC Fee</span>';
    } else {
       
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees & Payments - MSU Buug</title>
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
        
        /* FEE SUMMARY CARDS */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #800000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .summary-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #800000;
            margin: 10px 0 5px 0;
        }
        
        .summary-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        /* STATUS BADGES */
        .badge-paid {
            background: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
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
        
        /* FEE CATEGORY BADGES */
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
        
        .fee-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .fee-table thead {
            background: #800000;
            color: white;
        }
        
        .fee-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .fee-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.9rem;
        }
        
        .fee-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .fee-table tbody tr:hover {
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
            
            .summary-grid {
                grid-template-columns: repeat(4, 1fr);
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
            
            .summary-grid {
                gap: 20px;
            }
            
            .summary-card {
                padding: 25px;
            }
            
            .summary-value {
                font-size: 2rem;
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
            
            .summary-card {
                padding: 15px;
            }
            
            .summary-value {
                font-size: 1.5rem;
            }
            
            .fee-table th,
            .fee-table td {
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
                        <li><a class="dropdown-item active" href="student_fees.php"><i class="fas fa-file-invoice-dollar me-2 text-msu"></i>Fees & Payments</a></li>
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
                <a href="student_fees.php" class="nav-link active">
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
            <!-- Page Title -->
            <h3 class="section-title"><i class="fas fa-file-invoice-dollar me-2"></i>Fees & Payments</h3>
            
           

            <!-- Department Fees Section -->
            <?php if ($has_department_fees): ?>
            <div class="dashboard-card">
                <!-- Department Fees Header -->
                <div class="category-header department">
                    <div>
                        <h4 class="category-title"><i class="fas fa-university me-2"></i> Fees</h4>
                        <div class="category-summary">
                            <?php echo $summary['department']['total_count']; ?> fees • 
                            Total: <?php echo formatCurrency($summary['department']['total']); ?> • 
                            <span style="color: #28a745;">Paid: <?php echo formatCurrency($summary['department']['paid']); ?></span> • 
                            <span style="color: #ffc107;">Due: <?php echo formatCurrency($summary['department']['unpaid']); ?></span>
                        </div>
                    </div>
                    <div>
                        <span class="badge-department"><i class="fas fa-tag me-1"></i> Department</span>
                    </div>
                </div>
                
                <!-- Department Fees Table -->
                <div class="table-container mt-4">
                    <table class="fee-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($department_fees as $fee): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($fee['description']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($fee['academic_year'] ?? ''); ?> - <?php echo htmlspecialchars($fee['semester'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <?php echo getFeeCategoryBadge($fee['fee_type']); ?>
                                        <div class="mt-1"><?php echo htmlspecialchars($fee['fee_type']); ?></div>
                                    </td>
                                    <td class="amount-cell"><?php echo formatCurrency($fee['amount']); ?></td>
                                    <td><?php echo formatDate($fee['due_date']); ?></td>
                                    <td>
                                        <?php echo getStatusBadge($fee['status'], $fee['due_date']); ?>
                                        <?php if ($fee['status'] != 'Paid' && $fee['due_date'] < date('Y-m-d')): ?>
                                            <br><small class="text-danger">Overdue</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($fee['status'] != 'Paid'): ?>
                                            <button class="btn-msu-sm" onclick="printPaymentSlip(
                                                '<?php echo $fee['fee_id']; ?>',
                                                '<?php echo addslashes($fee['description']); ?>',
                                                '<?php echo addslashes($fee['fee_type']); ?>',
                                                <?php echo $fee['amount']; ?>,
                                                '<?php echo $fee['due_date']; ?>',
                                                '<?php echo $fee['status']; ?>',
                                                '<?php echo addslashes($fee['academic_year'] ?? ''); ?>',
                                                '<?php echo addslashes($fee['semester'] ?? ''); ?>',
                                                'department'
                                            )">
                                                <i class="fas fa-print me-1"></i> Print
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Paid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Department Summary Footer -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="text-primary">Department Total</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['department']['total']); ?></div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div style="color: #28a745;">Paid</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['department']['paid']); ?></div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div style="color: #ffc107;">Pending</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['department']['unpaid']); ?></div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div style="color: #dc3545;">Overdue</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['department']['overdue']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- SSC Fees Section -->
            <?php if ($has_ssc_fees): ?>
            <div class="dashboard-card">
                <!-- SSC Fees Header -->
                <div class="category-header ssc">
                    <div>
                        <h4 class="category-title"><i class="fas fa-users me-2"></i>Student Services & Council (SSC) Fees</h4>
                        <div class="category-summary">
                            <?php echo $summary['ssc']['total_count']; ?> fees • 
                            Total: <?php echo formatCurrency($summary['ssc']['total']); ?> • 
                            <span style="color: #28a745;">Paid: <?php echo formatCurrency($summary['ssc']['paid']); ?></span> • 
                            <span style="color: #ffc107;">Due: <?php echo formatCurrency($summary['ssc']['unpaid']); ?></span>
                        </div>
                    </div>
                    <div>
                        <span class="badge-ssc"><i class="fas fa-users me-1"></i> SSC</span>
                    </div>
                </div>
                
                <!-- SSC Fees Table -->
                <div class="table-container mt-4">
                    <table class="fee-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ssc_fees as $fee): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($fee['description']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($fee['academic_year'] ?? ''); ?> - <?php echo htmlspecialchars($fee['semester'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <?php echo getFeeCategoryBadge($fee['fee_type']); ?>
                                        <div class="mt-1"><?php echo htmlspecialchars($fee['fee_type']); ?></div>
                                    </td>
                                    <td class="amount-cell"><?php echo formatCurrency($fee['amount']); ?></td>
                                    <td><?php echo formatDate($fee['due_date']); ?></td>
                                    <td>
                                        <?php echo getStatusBadge($fee['status'], $fee['due_date']); ?>
                                        <?php if ($fee['status'] != 'Paid' && $fee['due_date'] < date('Y-m-d')): ?>
                                            <br><small class="text-danger">Overdue</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($fee['status'] != 'Paid'): ?>
                                            <button class="btn-msu-sm" onclick="printPaymentSlip(
                                                '<?php echo $fee['fee_id']; ?>',
                                                '<?php echo addslashes($fee['description']); ?>',
                                                '<?php echo addslashes($fee['fee_type']); ?>',
                                                <?php echo $fee['amount']; ?>,
                                                '<?php echo $fee['due_date']; ?>',
                                                '<?php echo $fee['status']; ?>',
                                                '<?php echo addslashes($fee['academic_year'] ?? ''); ?>',
                                                '<?php echo addslashes($fee['semester'] ?? ''); ?>',
                                                'ssc'
                                            )">
                                                <i class="fas fa-print me-1"></i> Print
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Paid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- SSC Summary Footer -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 mb-3">
                            <div style="color: #6f42c1;">SSC Total</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['ssc']['total']); ?></div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div style="color: #28a745;">Paid</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['ssc']['paid']); ?></div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div style="color: #ffc107;">Pending</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['ssc']['unpaid']); ?></div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div style="color: #dc3545;">Overdue</div>
                            <div class="fw-bold"><?php echo formatCurrency($summary['ssc']['overdue']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- No Fees Message -->
            <?php if (!$has_fees): ?>
            <div class="dashboard-card">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-3x text-success opacity-50"></i>
                    </div>
                    <h5 class="text-muted">No Fees Assessment Found</h5>
                    <p class="text-muted">You currently have no fees assessed to your account.</p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Payment Instructions Card -->
            <div class="dashboard-card">
                <h4 class="mb-4"><i class="fas fa-info-circle me-2"></i>Payment Instructions</h4>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-4 p-3" style="background: #f8f9fa; border-radius: 8px; border-left: 4px solid #800000;">
                            <h6 class="mb-3"><i class="fas fa-university me-2"></i> Payment Procedure</h6>
                            <ol class="mb-0">
                                <li class="mb-2">Print the payment slip for the fee you want to pay</li>
                                <li class="mb-2">Go to the College Treasurer's Office (Main Building, Ground Floor)</li>
                                <li class="mb-2">Present your Student ID and the printed payment slip</li>
                                <li class="mb-2">Pay the exact amount in <strong>CASH ONLY</strong></li>
                                <li>Get an official receipt and keep it as proof of payment</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 8px;">
                            <h6 class="mb-3"><i class="fas fa-clock me-2"></i> Office Hours</h6>
                            <div class="mb-2">
                                <strong>Mon - Fri:</strong> 8:00 AM - 5:00 PM<br>
                                <strong>Saturday:</strong> 8:00 AM - 12:00 PM<br>
                                <strong>Sunday:</strong> Closed
                            </div>
                            <div class="mt-3">
                                <h6 class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Location</h6>
                                <p class="mb-0">College Treasurer's Office<br>Main Building, Ground Floor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                <h4 style="color: #800000; margin: 10px 0 0 0; font-size: 18px; font-weight: bold;">STUDENT FEE PAYMENT SLIP</h4>
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
            
            <!-- Fee Details -->
            <div style="margin-bottom: 25px;">
                <div style="background-color: #800000; color: white; padding: 10px; text-align: center; font-weight: bold; margin-bottom: 15px;">
                    FEE PAYMENT DETAILS
                </div>
                <div id="slipContent"></div>
            </div>
            
            <!-- Payment Instructions -->
            <div style="margin-bottom: 25px;">
                <div style="background-color: #f8f9fa; border-left: 4px solid #800000; padding: 15px;">
                    <h5 style="color: #800000; margin-top: 0; margin-bottom: 10px;">PAYMENT INSTRUCTIONS</h5>
                    <ol style="margin: 0; padding-left: 20px;">
                        <li style="margin-bottom: 5px;">Present this slip and your valid Student ID at the College Treasurer's Office</li>
                        <li style="margin-bottom: 5px;">Pay the exact amount due in <strong>CASH ONLY</strong></li>
                        <li style="margin-bottom: 5px;">Get an official receipt from the cashier</li>
                        <li>Keep the receipt as your proof of payment</li>
                    </ol>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="text-align: center; border-top: 2px solid #800000; padding-top: 15px;">
                <p style="margin: 5px 0; font-size: 14px;"><strong>Office Hours:</strong> Monday-Friday: 8:00 AM - 5:00 PM | Saturday: 8:00 AM - 12:00 PM</p>
                <p style="margin: 5px 0; font-size: 14px;"><strong>Location:</strong> Main Building, Ground Floor, MSU Buug Campus</p>
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
        function printPaymentSlip(feeId, description, feeType, amount, dueDate, status, academicYear, semester, category) {
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
            let categoryName = category === 'ssc' ? 'SSC Fees' : 'Department Fees';
            
            slipContent.innerHTML = `
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Fee ID</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 25%;">Description</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Fee Type</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Due Date</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Status</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left; width: 15%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">${feeId}</td>
                            <td style="border: 1px solid #ddd; padding: 10px;">${description}</td>
                            <td style="border: 1px solid #ddd; padding: 10px;">
                                <span style="background-color: ${categoryColor}; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                    ${categoryName}
                                </span>
                                <div style="margin-top: 5px; font-size: 12px;">${feeType}</div>
                            </td>
                            <td style="border: 1px solid #ddd; padding: 10px;">${formatDate(dueDate)}</td>
                            <td style="border: 1px solid #ddd; padding: 10px;">
                                ${status === 'Paid' ? 
                                    '<span style="background-color: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">PAID</span>' : 
                                    '<span style="background-color: #ffc107; color: #212529; padding: 3px 8px; border-radius: 3px; font-size: 12px;">PENDING</span>'
                                }
                            </td>
                            <td style="border: 1px solid #ddd; padding: 10px; font-weight: bold;">₱${formattedAmount}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="background-color: #f8f9fa; border-left: 4px solid #800000; padding: 15px; margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="margin: 0; font-size: 16px;"><strong>Academic Year:</strong> ${academicYear}</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px;"><strong>Semester:</strong> ${semester}</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px;"><strong>Fee Category:</strong> ${categoryName}</p>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 0; font-size: 14px; color: #666;">Amount to Pay</p>
                            <h2 style="margin: 5px 0 0 0; color: #800000; font-size: 24px;">₱${formattedAmount}</h2>
                        </div>
                    </div>
                    <p style="margin: 10px 0 0 0; font-size: 14px;"><strong>Payment Method:</strong> CASH PAYMENT at Treasury Office</p>
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