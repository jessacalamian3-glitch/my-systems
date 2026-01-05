<?php
// student_support.php - STUDENT SUPPORT MODULE - UPDATED FOR CONSISTENT DESIGN

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Session check - SAME STRUCTURE AS PROFILE
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

// Database connection - SAME AS PROFILE
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

// Function to get student data - SAME AS PROFILE
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

// Helper functions - SAME AS PROFILE
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

// Function to submit support ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $student_id = $_SESSION['username'];
        $category = $_POST['category'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $priority = $_POST['priority'];
        
        $query = "INSERT INTO support_tickets (student_id, category, subject, message, priority, status) 
                  VALUES (:student_id, :category, :subject, :message, :priority, 'open')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':priority', $priority);
        
        if ($stmt->execute()) {
            $ticket_success = "Your support ticket has been submitted successfully. Ticket ID: #" . $db->lastInsertId();
        } else {
            $ticket_error = "There was an error submitting your ticket. Please try again.";
        }
    }
}

// Function to get FAQ categories
function getFAQCategories() {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT DISTINCT category FROM faqs WHERE status = 'active' ORDER BY category";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get FAQs by category
function getFAQsByCategory($category) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT question, answer FROM faqs WHERE category = :category AND status = 'active' ORDER BY display_order";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Fetch student data - SAME STRUCTURE AS PROFILE
$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);

// Set session info - SAME AS PROFILE
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

// Fetch FAQ data
$faq_categories = getFAQCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* EXACT SAME STYLES AS PROFILE MODULE */
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
        
        /* NAVIGATION - EXACT SAME */
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
        
        /* SIDEBAR - MOBILE FIRST (HIDDEN BY DEFAULT) - EXACT SAME */
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
        
        /* MOBILE MENU TOGGLE - EXACT SAME */
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
        
        /* CARDS - EXACT SAME STYLING AS PROFILE */
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
        
        /* PERFECT CIRCLE USER AVATAR FOR NAVIGATION - EXACT SAME */
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
        
        /* BUTTONS - EXACT SAME AS PROFILE */
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
        
        /* STATS CARDS - UPDATED TO MATCH PROFILE DESIGN */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border-top: 4px solid #800000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #800000;
            margin: 10px 0;
        }
        
        /* SUPPORT CARD SPECIFIC STYLES */
        .support-card {
            border-left: 4px solid #800000;
            border-radius: 8px;
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .support-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .support-card.contact {
            border-left-color: #800000;
        }
        
        .support-card.faq {
            border-left-color: #0d6efd;
        }
        
        .support-card.ticket {
            border-left-color: #198754;
        }
        
        .support-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .support-card-title {
            font-weight: 600;
            color: #800000;
            margin: 0;
            flex: 1;
        }
        
        .support-card.faq .support-card-title {
            color: #0d6efd;
        }
        
        .support-card.ticket .support-card-title {
            color: #198754;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .contact-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .contact-icon {
            width: 40px;
            height: 40px;
            background: #800000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        
        .contact-item:hover .contact-icon {
            transform: scale(1.1);
            background: #a30000;
        }
        
        .contact-details h6 {
            margin-bottom: 5px;
            color: #800000;
            font-weight: 600;
        }
        
        .contact-details p {
            margin-bottom: 0;
            color: #6c757d;
        }
        
        .faq-category {
            margin-bottom: 25px;
        }
        
        .faq-item {
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            border-color: #800000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .faq-question {
            padding: 15px;
            background: #f8f9fa;
            border: none;
            width: 100%;
            text-align: left;
            font-weight: 600;
            color: #800000;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: #e9ecef;
        }
        
        .faq-answer {
            padding: 0 15px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }
        
        .faq-answer.show {
            padding: 15px;
            max-height: 500px;
        }
        
        .ticket-form .form-group {
            margin-bottom: 20px;
        }
        
        .ticket-form label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .priority-high {
            color: #dc3545;
            font-weight: 600;
        }
        
        .priority-medium {
            color: #ffc107;
            font-weight: 600;
        }
        
        .priority-low {
            color: #28a745;
            font-weight: 600;
        }
        
        .urgent-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .urgent-notice:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .mobile-user-info {
            display: none;
            background: #5a0000;
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        /* WELCOME BANNER - UPDATED TO MATCH DESIGN */
        .welcome-banner {
            background: linear-gradient(135deg, #800000, #5a0000);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 2px solid #FFD700;
            transition: all 0.3s ease;
        }
        
        .welcome-banner:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        /* ICONS */
        .icon-circle {
            width: 50px;
            height: 50px;
            background: #800000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin: 0 auto 15px;
        }
        
        /* ===== DESKTOP STYLES (992px and up) - EXACT SAME AS PROFILE ===== */
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
            
            .welcome-banner {
                padding: 30px;
            }
            
            .stats-number {
                font-size: 2.5rem;
            }
            
            .contact-item {
                margin-bottom: 20px;
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
            
            .welcome-banner {
                padding: 40px;
            }
        }

        /* ===== EXTRA SMALL DEVICES (phones, 480px and down) ===== */
        @media (max-width: 480px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .welcome-banner {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .dashboard-card {
                padding: 1rem;
                margin-bottom: 15px;
            }
            
            .stats-card {
                padding: 15px;
            }
            
            .stats-number {
                font-size: 1.8rem;
            }
            
            .icon-circle {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
            
            .sidebar {
                width: 260px;
                left: -260px;
            }
            
            .contact-item {
                flex-direction: column;
                text-align: center;
            }
            
            .contact-icon {
                margin-right: 0;
                margin-bottom: 10px;
                align-self: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar - EXACT SAME AS PROFILE MODULE -->
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
                        <!-- PERFECT CIRCLE PROFILE PICTURE IN NAVIGATION - EXACT SAME AS PROFILE -->
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

    <!-- Sidebar Overlay - EXACT SAME -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Layout - EXACT SAME STRUCTURE -->
    <div class="d-flex">
        <!-- Sidebar - EXACT SAME AS PROFILE -->
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
                <a href="student_support.php" class="nav-link active">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Notifications
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h3 class="text-msu mb-4"><i class="fas fa-question-circle me-2"></i>Help & Support</h3>
            
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-2">We're Here to Help You</h4>
                        <p class="mb-0">Get assistance with academic, technical, and administrative issues</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="icon-circle">
                            <i class="fas fa-headset"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Cards -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <div class="icon-circle">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3 class="stats-number">24/7</h3>
                        <p class="small">Support Available</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <div class="icon-circle">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="stats-number">2-4 hrs</h3>
                        <p class="small">Average Response Time</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <div class="icon-circle">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="stats-number">98%</h3>
                        <p class="small">Issues Resolved</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="support-card-header">
                        <h5 class="support-card-title"><i class="fas fa-address-book me-2"></i>Contact Information</h5>
                    </div>
                    
                    <p class="text-muted mb-4">Get in touch with the right department for your specific concern</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="contact-details">
                                    <h6>Registrar's Office</h6>
                                    <p><strong>Phone:</strong> (062) 991-1234</p>
                                    <p><strong>Email:</strong> registrar@msubuug.edu.ph</p>
                                    <p><strong>Hours:</strong> Mon-Fri, 8:00 AM - 5:00 PM</p>
                                    <p class="text-muted">For enrollment, grades, and academic records</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="contact-details">
                                    <h6>Treasurer's Office</h6>
                                    <p><strong>Phone:</strong> (062) 991-1235</p>
                                    <p><strong>Email:</strong> treasury@msubuug.edu.ph</p>
                                    <p><strong>Hours:</strong> Mon-Fri, 8:00 AM - 5:00 PM</p>
                                    <p class="text-muted">For fees, payments, and financial concerns</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <div class="contact-details">
                                    <h6>IT Support</h6>
                                    <p><strong>Phone:</strong> (062) 991-1236</p>
                                    <p><strong>Email:</strong> itsupport@msubuug.edu.ph</p>
                                    <p><strong>Hours:</strong> 24/7</p>
                                    <p class="text-muted">For technical issues with the student portal</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="contact-details">
                                    <h6>Student Affairs</h6>
                                    <p><strong>Phone:</strong> (062) 991-1237</p>
                                    <p><strong>Email:</strong> studentaffairs@msubuug.edu.ph</p>
                                    <p><strong>Hours:</strong> Mon-Fri, 8:00 AM - 5:00 PM</p>
                                    <p class="text-muted">For student organizations and activities</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="urgent-notice">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Emergency Contact</h6>
                        <p class="mb-0">For urgent matters outside office hours, please contact the Campus Security at <strong>(062) 991-1999</strong> or send an email to <strong>emergency@msubuug.edu.ph</strong></p>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="support-card-header">
                        <h5 class="support-card-title"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h5>
                    </div>
                    
                    <p class="text-muted mb-4">Find quick answers to common questions</p>
                    
                    <div class="faq-category">
                        <h6 class="text-msu mb-3"><i class="fas fa-graduation-cap me-2"></i>Academic Questions</h6>
                        
                        <div class="faq-item">
                            <button class="faq-question">
                                How do I request for a change of grade?
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <p>To request a change of grade, you need to:</p>
                                <ol>
                                    <li>Secure a Change of Grade form from the Registrar's Office</li>
                                    <li>Have it signed by your instructor</li>
                                    <li>Submit the completed form to the Department Chair</li>
                                    <li>Wait for approval from the Dean's Office</li>
                                </ol>
                                <p>Processing usually takes 5-7 working days.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <button class="faq-question">
                                What should I do if I fail a subject?
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <p>If you fail a subject, you have several options:</p>
                                <ul>
                                    <li>Retake the subject in the next semester</li>
                                    <li>Check if you're eligible for removal examinations</li>
                                    <li>Consult with your academic advisor for guidance</li>
                                    <li>Review the curriculum to understand the impact on your graduation timeline</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <button class="faq-question">
                                How can I get an official transcript of records?
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <p>To request an official transcript:</p>
                                <ol>
                                    <li>Submit a written request to the Registrar's Office</li>
                                    <li>Pay the required fees at the Treasury Office</li>
                                    <li>Present the receipt to the Registrar's Office</li>
                                    <li>Wait for processing (usually 3-5 working days)</li>
                                </ol>
                                <p>You can also request for digital copies to be sent to specific institutions.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-category">
                        <h6 class="text-msu mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Fees and Payments</h6>
                        
                        <div class="faq-item">
                            <button class="faq-question">
                                What payment methods are accepted?
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <p>MSU Buug currently accepts the following payment methods:</p>
                                <ul>
                                    <li>Cash payments at the Treasury Office</li>
                                    <li>Check payments (subject to clearing)</li>
                                    <li>Bank transfers (details available at the Treasury)</li>
                                </ul>
                                <p>Online payment options are currently being developed and will be available soon.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <button class="faq-question">
                                What happens if I can't pay my fees on time?
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <p>If you're unable to pay your fees by the deadline:</p>
                                <ul>
                                    <li>You may request for a payment extension from the Accounting Office</li>
                                    <li>Late payment penalties may apply (1.5% per month)</li>
                                    <li>You might be restricted from enrolling in the next semester</li>
                                    <li>Academic records may be withheld until payments are settled</li>
                                </ul>
                                <p>It's important to communicate with the Treasury Office about your situation.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-category">
                        <h6 class="text-msu mb-3"><i class="fas fa-laptop me-2"></i>Technical Support</h6>
                        
                        <div class="faq-item">
                            <button class="faq-question">
                                I forgot my password. What should I do?
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <p>If you've forgotten your password:</p>
                                <ol>
                                    <li>Go to the login page and click "Forgot Password"</li>
                                    <li>Enter your student ID and registered email address</li>
                                    <li>Check your email for password reset instructions</li>
                                    <li>Follow the link to create a new password</li>
                                </ol>
                                <p>If you don't receive the email, contact IT Support for assistance.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <button class="faq-question">
                                The student portal is not loading properly. What can I do?
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <p>If you're experiencing issues with the student portal:</p>
                                <ul>
                                    <li>Clear your browser cache and cookies</li>
                                    <li>Try using a different web browser</li>
                                    <li>Check your internet connection</li>
                                    <li>Try accessing the portal during off-peak hours</li>
                                    <li>Disable any browser extensions that might interfere</li>
                                </ul>
                                <p>If problems persist, contact IT Support with details about the issue.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Ticket Section -->
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="support-card-header">
                        <h5 class="support-card-title"><i class="fas fa-ticket-alt me-2"></i>Submit a Support Ticket</h5>
                    </div>
                    
                    <p class="text-muted mb-4">Can't find what you're looking for? Submit a ticket and we'll get back to you as soon as possible.</p>
                    
                    <?php if (isset($ticket_success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $ticket_success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($ticket_error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $ticket_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form class="ticket-form" method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">Select a category</option>
                                        <option value="academic">Academic</option>
                                        <option value="financial">Financial</option>
                                        <option value="technical">Technical</option>
                                        <option value="administrative">Administrative</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select class="form-control" id="priority" name="priority" required>
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Brief description of your issue" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Please provide detailed information about your issue..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="urgent" name="urgent">
                                <label class="form-check-label" for="urgent">
                                    This is an urgent matter that requires immediate attention
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" name="submit_ticket" class="btn-msu">
                            <i class="fas fa-paper-plane me-2"></i> Submit Ticket
                        </button>
                    </form>
                </div>
            </div>

            
    <!-- JavaScript - EXACT SAME AS PROFILE MODULE -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle - EXACT SAME
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

        // Close sidebar when clicking on overlay - EXACT SAME
        document.querySelector('.sidebar-overlay').addEventListener('click', function() {
            toggleSidebar();
        });

        // Auto-close sidebar when window is resized to desktop - EXACT SAME
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        // Set active nav link based on current page - EXACT SAME
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

        // FAQ toggle functionality
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                // Toggle current answer
                answer.classList.toggle('show');
                
                // Rotate icon
                if (answer.classList.contains('show')) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
                
                // Close other open FAQs
                document.querySelectorAll('.faq-answer').forEach(otherAnswer => {
                    if (otherAnswer !== answer && otherAnswer.classList.contains('show')) {
                        otherAnswer.classList.remove('show');
                        const otherIcon = otherAnswer.previousElementSibling.querySelector('i');
                        otherIcon.classList.remove('fa-chevron-up');
                        otherIcon.classList.add('fa-chevron-down');
                    }
                });
            });
        });

        // Priority indicator
        document.getElementById('priority').addEventListener('change', function() {
            const priority = this.value;
            let indicator = document.getElementById('priority-indicator');
            
            if (!indicator) {
                indicator = document.createElement('span');
                indicator.id = 'priority-indicator';
                this.parentNode.appendChild(indicator);
            }
            
            if (priority === 'high') {
                indicator.className = 'priority-high';
                indicator.textContent = ' (Urgent)';
            } else if (priority === 'medium') {
                indicator.className = 'priority-medium';
                indicator.textContent = ' (Normal)';
            } else {
                indicator.className = 'priority-low';
                indicator.textContent = ' (Low)';
            }
        });

        // Urgent checkbox effect
        document.getElementById('urgent').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('priority').value = 'high';
                document.getElementById('priority').dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>