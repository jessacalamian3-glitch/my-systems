<?php
session_start();

// Include database configuration
require_once 'config/database.php';

// Security enhancements
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Session timeout (30 minutes)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login_faculty.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Session regeneration for security
session_regenerate_id(true);

// Simple check - if not logged in as faculty, go to login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'faculty') {
    header("Location: login_faculty.php");
    exit;
}

$faculty_id = $_SESSION['username'];

// Get faculty data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = ? AND status = 'active'");
    $stmt->execute([$faculty_id]);
    $faculty_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$faculty_info) {
        header("Location: login_faculty.php");
        exit;
    }
    
    // Get class count
    $stmt = $pdo->prepare("SELECT COUNT(*) as class_count FROM classes WHERE faculty_id = ? AND status = 'active'");
    $stmt->execute([$faculty_id]);
    $class_count = $stmt->fetch(PDO::FETCH_ASSOC)['class_count'];
    
    // Get student count
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.student_id) as student_count 
        FROM enrollments e 
        JOIN classes c ON e.class_id = c.class_id 
        WHERE c.faculty_id = ?
    ");
    $stmt->execute([$faculty_id]);
    $student_count = $stmt->fetch(PDO::FETCH_ASSOC)['student_count'];
    
    // Get classes
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(e.student_id) as enrolled_students 
        FROM classes c 
        LEFT JOIN enrollments e ON c.class_id = e.class_id 
        WHERE c.faculty_id = ? AND c.status = 'active' 
        GROUP BY c.class_id 
        ORDER BY c.course_code
    ");
    $stmt->execute([$faculty_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activities
    $stmt = $pdo->prepare("
        SELECT * FROM activities 
        WHERE faculty_id = ? 
        ORDER BY created_at DESC 
        LIMIT 4
    ");
    $stmt->execute([$faculty_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Fallback values
    $class_count = 0;
    $student_count = 0;
    $classes = [];
    $activities = [];
}

// Default values for other stats
$pending_tasks = 0;
$todays_classes = count($classes);

// Output compression for performance
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --maroon-light: #a30000;
            --gold: #FFD700;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 12px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
        }
        
        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, var(--maroon-dark) 0%, var(--maroon) 100%);
            min-height: calc(100vh - 70px);
            position: fixed;
            width: 280px;
            margin-top: 70px;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
            transition: margin-left 0.3s ease;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 15px 25px;
            border-left: 4px solid transparent;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--gold);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--maroon-light), var(--maroon));
            color: white;
            border-left-color: var(--gold);
            box-shadow: 0 4px 15px rgba(163, 0, 0, 0.3);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        
        /* Dashboard Sections */
        .dashboard-section {
            display: none;
            animation: fadeIn 0.5s ease-in;
        }
        
        .dashboard-section.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Dashboard Cards */
        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 20px 25px;
            font-weight: 600;
        }
        
        .card-header i {
            margin-right: 10px;
        }
        
        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(128, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .welcome-banner h2 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border-top: 5px solid var(--maroon);
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            cursor: pointer;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stats-card i {
            font-size: 2.5rem;
            color: var(--maroon);
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
        }
        
        .stats-card h3 {
            font-weight: 700;
            color: var(--maroon);
            margin: 10px 0;
            font-size: 2.2rem;
        }
        
        .stats-card p {
            color: #6c757d;
            font-weight: 500;
            margin: 0;
        }
        
        /* User Avatar */
        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--gold), #ffed4e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--maroon);
            font-weight: bold;
            font-size: 1.1rem;
            border: 2px solid white;
        }
        
        /* Quick Actions */
        .quick-actions .btn {
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 500;
            margin: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-maroon {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
        }
        
        .btn-maroon:hover {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128, 0, 0, 0.3);
        }
        
        /* Profile Info */
        .profile-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item i {
            width: 25px;
            color: var(--maroon);
            margin-right: 15px;
        }
        
        /* Recent Activity */
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: background 0.3s ease;
        }
        
        .activity-item:hover {
            background: rgba(128, 0, 0, 0.03);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: rgba(128, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--maroon);
        }
        
        /* Classes Table */
        .classes-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .classes-table th {
            background: var(--maroon);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .classes-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .classes-table tr:hover {
            background: rgba(128, 0, 0, 0.03);
        }
        
        .badge-class {
            background: var(--maroon);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        /* Students Grid */
        .student-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-top: 4px solid var(--maroon);
        }
        
        .student-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        
        .student-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 0 auto 15px;
        }
        
        /* Loading Indicator */
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .welcome-banner .text-end {
                text-align: left !important;
                margin-top: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .welcome-banner {
                padding: 20px;
            }
            
            .stats-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Indicator -->
    <div id="loading" class="loading">
        <div class="text-center">
            <div class="spinner-border text-maroon" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Loading...</p>
        </div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <button class="mobile-menu-toggle me-3">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="#">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                MSU BUUG - Faculty Portal
            </a>
            
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar me-2">
                            <?php echo strtoupper(substr($faculty_info['name'], 0, 1)); ?>
                        </div>
                        <span class="text-white"><?php echo htmlspecialchars($faculty_info['name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#profile" onclick="showSection('profile')"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="#settings" onclick="showSection('settings')"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="faculty_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-xxl-2 col-xl-3 sidebar">
                <div class="d-flex flex-column pt-4">
                    <a class="nav-link active" onclick="showSection('dashboard')">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" onclick="showSection('classes')">
                        <i class="fas fa-book"></i> My Classes
                    </a>
                    <a class="nav-link" onclick="showSection('students')">
                        <i class="fas fa-users"></i> Students
                    </a>
                    <a class="nav-link" onclick="showSection('grades')">
                        <i class="fas fa-chart-line"></i> Grades & Reports
                    </a>
                    <a class="nav-link" onclick="showSection('schedule')">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                    <a class="nav-link" onclick="showSection('assignments')">
                        <i class="fas fa-tasks"></i> Assignments
                    </a>
                    <a class="nav-link" onclick="showSection('resources')">
                        <i class="fas fa-file-alt"></i> Resources
                    </a>
                    <a class="nav-link" onclick="showSection('messages')">
                        <i class="fas fa-comments"></i> Messages
                    </a>
                    <a class="nav-link mt-4" onclick="showSection('profile')">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a class="nav-link" onclick="showSection('settings')">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-xxl-10 col-xl-9 main-content">
                
                <!-- DASHBOARD SECTION -->
                <div id="dashboard" class="dashboard-section active">
                    <!-- Welcome Banner -->
                    <div class="welcome-banner">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2>Welcome back, <?php echo htmlspecialchars($faculty_info['name']); ?>! 👋</h2>
                                <p class="mb-1 lead"><?php echo htmlspecialchars($faculty_info['position']); ?> • <?php echo htmlspecialchars($faculty_info['department']); ?></p>
                                <small><i class="fas fa-id-card me-1"></i>Faculty ID: <?php echo htmlspecialchars($faculty_id); ?> | <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($faculty_info['email']); ?></small>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="quick-actions">
                                    <button class="btn btn-light" onclick="showSection('classes')"><i class="fas fa-plus me-2"></i>New Class</button>
                                    <button class="btn btn-light" onclick="showSection('resources')"><i class="fas fa-upload me-2"></i>Upload Material</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stats-card" onclick="showSection('classes')">
                                <i class="fas fa-book-open"></i>
                                <h3><?php echo $class_count; ?></h3>
                                <p>Active Classes</p>
                                <small class="text-success"><i class="fas fa-arrow-up me-1"></i><?php echo $class_count > 0 ? 'Active' : 'No classes'; ?></small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stats-card" onclick="showSection('students')">
                                <i class="fas fa-users"></i>
                                <h3><?php echo $student_count; ?></h3>
                                <p>Total Students</p>
                                <small class="text-success"><i class="fas fa-arrow-up me-1"></i><?php echo $student_count > 0 ? 'Enrolled' : 'No students'; ?></small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stats-card" onclick="showSection('assignments')">
                                <i class="fas fa-tasks"></i>
                                <h3><?php echo $pending_tasks; ?></h3>
                                <p>Pending Tasks</p>
                                <small class="text-warning"><i class="fas fa-clock me-1"></i>Manage assignments</small>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stats-card" onclick="showSection('schedule')">
                                <i class="fas fa-calendar-day"></i>
                                <h3><?php echo $todays_classes; ?></h3>
                                <p>Your Classes</p>
                                <small class="text-info"><i class="fas fa-bell me-1"></i>All active classes</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Faculty Information Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="dashboard-card">
                                <div class="card-header">
                                    <i class="fas fa-user-tie"></i> Faculty Information
                                </div>
                                <div class="card-body">
                                    <div class="profile-info">
                                        <div class="info-item">
                                            <i class="fas fa-id-card"></i>
                                            <div>
                                                <strong>Faculty ID:</strong><br>
                                                <span><?php echo htmlspecialchars($faculty_id); ?></span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-user"></i>
                                            <div>
                                                <strong>Full Name:</strong><br>
                                                <span><?php echo htmlspecialchars($faculty_info['name']); ?></span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-building"></i>
                                            <div>
                                                <strong>Department:</strong><br>
                                                <span><?php echo htmlspecialchars($faculty_info['department']); ?></span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-briefcase"></i>
                                            <div>
                                                <strong>Position:</strong><br>
                                                <span><?php echo htmlspecialchars($faculty_info['position']); ?></span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-envelope"></i>
                                            <div>
                                                <strong>Email:</strong><br>
                                                <span><?php echo htmlspecialchars($faculty_info['email']); ?></span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-check-circle"></i>
                                            <div>
                                                <strong>Status:</strong><br>
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="dashboard-card">
                                <div class="card-header">
                                    <i class="fas fa-bell"></i> Recent Activity
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($activities) > 0): ?>
                                        <?php foreach($activities as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-<?php echo htmlspecialchars($activity['icon'] ?? 'bell'); ?>"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-bell-slash fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">No recent activities</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Today's Schedule -->
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="dashboard-card">
                                <div class="card-header">
                                    <i class="fas fa-bolt"></i> Quick Actions
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3 col-6 mb-3">
                                            <button class="btn btn-maroon w-100 py-3" onclick="showSection('classes')">
                                                <i class="fas fa-plus fa-2x mb-2"></i><br>
                                                New Class
                                            </button>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <button class="btn btn-maroon w-100 py-3" onclick="showSection('resources')">
                                                <i class="fas fa-upload fa-2x mb-2"></i><br>
                                                Upload Material
                                            </button>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <button class="btn btn-maroon w-100 py-3" onclick="showSection('assignments')">
                                                <i class="fas fa-tasks fa-2x mb-2"></i><br>
                                                Grade Assignments
                                            </button>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <button class="btn btn-maroon w-100 py-3" onclick="showSection('grades')">
                                                <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                                                View Reports
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <div class="dashboard-card">
                                <div class="card-header">
                                    <i class="fas fa-calendar-day"></i> Your Classes
                                </div>
                                <div class="card-body">
                                    <?php if (count($classes) > 0): ?>
                                        <?php foreach(array_slice($classes, 0, 3) as $class): ?>
                                        <div class="schedule-item mb-3 p-3 border-start border-4 border-success" onclick="showSection('classes')" style="cursor: pointer;">
                                            <strong><?php echo htmlspecialchars($class['course_code']); ?> - <?php echo htmlspecialchars($class['course_name']); ?></strong>
                                            <p class="mb-1"><?php echo htmlspecialchars($class['schedule']); ?></p>
                                            <small class="text-muted"><?php echo htmlspecialchars($class['room']); ?> • <?php echo htmlspecialchars($class['enrolled_students']); ?> students</small>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="fas fa-book fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No classes assigned</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MY CLASSES SECTION -->
                <div id="classes" class="dashboard-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-maroon"><i class="fas fa-book me-2"></i>My Classes</h2>
                        <button class="btn btn-maroon" onclick="addNewClass()"><i class="fas fa-plus me-2"></i>Add New Class</button>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-list"></i> Current Classes (<?php echo count($classes); ?>)
                        </div>
                        <div class="card-body">
                            <?php if (count($classes) > 0): ?>
                            <div class="table-responsive">
                                <table class="classes-table">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Schedule</th>
                                            <th>Students</th>
                                            <th>Room</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($classes as $class): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['course_code']); ?></td>
                                            <td><?php echo htmlspecialchars($class['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($class['schedule']); ?></td>
                                            <td><?php echo htmlspecialchars($class['enrolled_students']); ?></td>
                                            <td><?php echo htmlspecialchars($class['room']); ?></td>
                                            <td><span class="badge-class"><?php echo htmlspecialchars($class['status']); ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-maroon me-1" onclick="viewClass(<?php echo $class['class_id']; ?>)">View</button>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="editClass(<?php echo $class['class_id']; ?>)">Edit</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h4>No Classes Found</h4>
                                <p class="text-muted">You don't have any active classes yet.</p>
                                <button class="btn btn-maroon" onclick="addNewClass()">Add Your First Class</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- STUDENTS SECTION -->
                <div id="students" class="dashboard-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-maroon"><i class="fas fa-users me-2"></i>Students</h2>
                        <button class="btn btn-maroon"><i class="fas fa-download me-2"></i>Export List</button>
                    </div>

                    <div class="row">
                        <?php 
                        // Get students for this faculty
                        try {
                            $stmt = $pdo->prepare("
                                SELECT DISTINCT s.* 
                                FROM students s 
                                JOIN enrollments e ON s.student_id = e.student_id 
                                JOIN classes c ON e.class_id = c.class_id 
                                WHERE c.faculty_id = ? 
                                LIMIT 6
                            ");
                            $stmt->execute([$faculty_id]);
                            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch(PDOException $e) {
                            $students = [];
                        }
                        
                        if (count($students) > 0): 
                            foreach($students as $student): 
                        ?>
                        <div class="col-xl-4 col-lg-6 mb-4">
                            <div class="student-card">
                                <div class="student-avatar">
                                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                </div>
                                <h5><?php echo htmlspecialchars($student['name']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($student['student_id']); ?></p>
                                <p><?php echo htmlspecialchars($student['program']); ?></p>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-maroon">Profile</button>
                                    <button class="btn btn-sm btn-outline-maroon">Grades</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h4>No Students Found</h4>
                                <p class="text-muted">No students are enrolled in your classes yet.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- GRADES SECTION -->
                <div id="grades" class="dashboard-section">
                    <h2 class="text-maroon mb-4"><i class="fas fa-chart-line me-2"></i>Grades & Reports</h2>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar"></i> Grade Distribution
                        </div>
                        <div class="card-body">
                            <p>Grade reports and analytics will be displayed here.</p>
                            <!-- Add grade charts and tables here -->
                        </div>
                    </div>
                </div>

                <!-- SCHEDULE SECTION -->
                <div id="schedule" class="dashboard-section">
                    <h2 class="text-maroon mb-4"><i class="fas fa-calendar-alt me-2"></i>Class Schedule</h2>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-calendar-week"></i> Weekly Schedule
                        </div>
                        <div class="card-body">
                            <p>Full class schedule and calendar view will be displayed here.</p>
                            <!-- Add calendar component here -->
                        </div>
                    </div>
                </div>

                <!-- ASSIGNMENTS SECTION -->
                <div id="assignments" class="dashboard-section">
                    <h2 class="text-maroon mb-4"><i class="fas fa-tasks me-2"></i>Assignments</h2>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-clipboard-list"></i> Assignment Management
                        </div>
                        <div class="card-body">
                            <p>Assignment creation and grading interface will be displayed here.</p>
                            <!-- Add assignment management here -->
                        </div>
                    </div>
                </div>

                <!-- RESOURCES SECTION -->
                <div id="resources" class="dashboard-section">
                    <h2 class="text-maroon mb-4"><i class="fas fa-file-alt me-2"></i>Teaching Resources</h2>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Resources
                        </div>
                        <div class="card-body">
                            <p>Resource upload and management interface will be displayed here.</p>
                            <!-- Add resource management here -->
                        </div>
                    </div>
                </div>

                <!-- MESSAGES SECTION -->
                <div id="messages" class="dashboard-section">
                    <h2 class="text-maroon mb-4"><i class="fas fa-comments me-2"></i>Messages</h2>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-inbox"></i> Message Inbox
                        </div>
                        <div class="card-body">
                            <p>Student and faculty messaging interface will be displayed here.</p>
                            <!-- Add messaging interface here -->
                        </div>
                    </div>
                </div>

                <!-- PROFILE SECTION -->
                <div id="profile" class="dashboard-section">
                    <h2 class="text-maroon mb-4"><i class="fas fa-user me-2"></i>My Profile</h2>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-user-edit"></i> Profile Information
                        </div>
                        <div class="card-body">
                            <p>Profile management and editing interface will be displayed here.</p>
                            <!-- Add profile form here -->
                        </div>
                    </div>
                </div>

                <!-- SETTINGS SECTION -->
                <div id="settings" class="dashboard-section">
                    <h2 class="text-maroon mb-4"><i class="fas fa-cog me-2"></i>Settings</h2>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-sliders-h"></i> Account Settings
                        </div>
                        <div class="card-body">
                            <p>Account and system settings will be displayed here.</p>
                            <!-- Add settings form here -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to show specific section and hide others
        function showSection(sectionId) {
            // Show loading indicator
            document.getElementById('loading').style.display = 'block';
            
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section after a short delay for smooth transition
            setTimeout(() => {
                document.getElementById(sectionId).classList.add('active');
                document.getElementById('loading').style.display = 'none';
                
                // Update URL without page reload
                history.pushState(null, null, `#${sectionId}`);
                
                // Close mobile sidebar if open
                document.querySelector('.sidebar').classList.remove('active');
            }, 300);
            
            // Update active state in sidebar
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Find and activate the corresponding sidebar link
            const activeLink = Array.from(document.querySelectorAll('.sidebar .nav-link')).find(link => {
                return link.getAttribute('onclick') === `showSection('${sectionId}')`;
            });
            
            if (activeLink) {
                activeLink.classList.add('active');
            }
            
            // Scroll to top of main content
            document.querySelector('.main-content').scrollTop = 0;
        }

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
            const sectionId = window.location.hash.replace('#', '') || 'dashboard';
            showSection(sectionId);
        });

        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    document.querySelector('.sidebar').classList.remove('active');
                }
            });
        });

        // Close sidebar when clicking outside (mobile)
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Class management functions
        function addNewClass() {
            alert('Add New Class functionality will be implemented here.');
            // You can implement a modal or form for adding new classes
        }

        function viewClass(classId) {
            alert('View Class ID: ' + classId + ' - This will show class details.');
            // Implement class viewing functionality
        }

        function editClass(classId) {
            alert('Edit Class ID: ' + classId + ' - This will open class editing form.');
            // Implement class editing functionality
        }

        // Initialize dashboard on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show section based on URL hash or default to dashboard
            const initialSection = window.location.hash.replace('#', '') || 'dashboard';
            showSection(initialSection);
            
            // Add click handlers to all sidebar links
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    const sectionId = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                    showSection(sectionId);
                });
            });

            // Make all clickable elements have pointer cursor
            document.querySelectorAll('[onclick]').forEach(element => {
                element.style.cursor = 'pointer';
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        });
    </script>
</body>
</html>