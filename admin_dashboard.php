<?php
// admin_dashboard.php - UPDATED WITH CORRECT TABLE NAME
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
    // Redirect unauthorized users to their respective dashboards
    if (isset($_SESSION['user_type'])) {
        switch ($_SESSION['user_type']) {
            case 'student':
                header("Location: student_dashboard.php");
                exit();
            case 'faculty':
                header("Location: faculty_dashboard.php");
                exit();
            default:
                header("Location: index.pfp.php");
                exit();
        }
    } else {
        header("Location: index.pfp.php");
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

// CORRECTED: Get admin info from admin_users table using session username
$admin_info = [
    'name' => 'System Administrator',
    'email' => 'admin@msubuug.edu.ph',
    'username' => $_SESSION['username'] ?? 'admin',
    'role' => 'System Administrator',
    'last_login' => date('Y-m-d H:i:s')
];

// Fetch fresh admin data from admin_users table
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE admin_id = ?");
    $stmt->execute([$_SESSION['username']]);
    $db_admin = $stmt->fetch();
    
    if ($db_admin) {
        $admin_info['name'] = $db_admin['name'] ?? 'System Administrator';
        $admin_info['email'] = $db_admin['email'] ?? 'admin@msubuug.edu.ph';
        $admin_info['username'] = $db_admin['username'] ?? $_SESSION['username'];
        $admin_info['role'] = $db_admin['role'] ?? 'System Administrator';
        
        // Update session with correct admin info
        $_SESSION['user_info'] = [
            'name' => $admin_info['name'],
            'role' => $admin_info['role']
        ];
    }
} catch (Exception $e) {
    // Continue with default info if DB fetch fails
    error_log("Admin data fetch error: " . $e->getMessage());
}

// --- Fetch Dashboard Stats ---
$total_students = 0;
$total_faculty = 0;
$total_courses = 0;
$pending_requests = 0;

try {
    $total_students_stmt = $pdo->query("SELECT COUNT(*) AS total_students FROM students");
    $total_students = $total_students_stmt->fetch()['total_students'];
} catch (Exception $e) {
    $total_students = 0;
}

try {
    $total_faculty_stmt = $pdo->query("SELECT COUNT(*) AS total_faculty FROM faculty");
    $total_faculty = $total_faculty_stmt->fetch()['total_faculty'];
} catch (Exception $e) {
    $total_faculty = 0;
}

try {
    $total_courses_stmt = $pdo->query("SELECT COUNT(*) AS total_courses FROM courses");
    $total_courses = $total_courses_stmt->fetch()['total_courses'];
} catch (Exception $e) {
    $total_courses = 0;
}

try {
    $pending_requests_stmt = $pdo->query("SELECT COUNT(*) AS pending_requests FROM enrollment_requests WHERE status='pending'");
    $pending_requests = $pending_requests_stmt->fetch()['pending_requests'];
} catch (Exception $e) {
    $pending_requests = 0;
}

// --- Fetch Recent Activity ---
$recent_students = [];
$recent_courses = [];
$recent_payments = [];

try {
    $recent_students_stmt = $pdo->query("SELECT first_name, last_name, course, created_at FROM students ORDER BY created_at DESC LIMIT 5");
    $recent_students = $recent_students_stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist
}

try {
    $recent_courses_stmt = $pdo->query("SELECT course_code, course_name, created_at FROM courses ORDER BY created_at DESC LIMIT 5");
    $recent_courses = $recent_courses_stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist
}

try {
    $recent_payments_stmt = $pdo->query("
        SELECT s.first_name, s.last_name, p.amount, p.created_at 
        FROM payments p 
        JOIN students s ON p.student_id = s.student_id 
        ORDER BY p.created_at DESC LIMIT 5
    ");
    $recent_payments = $recent_payments_stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        .msu-maroon { color: #800000; }
        .bg-msu-maroon { background-color: #800000; color: #fff; }
        .btn-msu { background: #800000; color: white; border: none; padding: 10px 18px; border-radius: 8px; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .navbar { background: #800000 !important; padding: 1rem 0; position: fixed; top: 0; width: 100%; z-index: 1030; }
        .sidebar { background: #5a0000; min-height: 100vh; position: fixed; width: 280px; left: 0; top: 0; padding-top: 70px; }
        .sidebar .nav-link { color: white; padding: 15px 20px; border-left: 4px solid transparent; }
        .sidebar .nav-link:hover { background: #a30000; border-left-color: #FFD700; color: #FFD700; }
        .sidebar .nav-link.active { background: #a30000; border-left-color: #FFD700; color: #FFD700; font-weight:600; }
        .main-content { padding: 20px 15px; min-height: 100vh; margin-top: 70px; margin-left: 280px; width: calc(100% - 280px); }
        .dashboard-card { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-top: 4px solid #800000; margin-bottom: 20px; padding: 1.5rem; }
        .welcome-banner { background: linear-gradient(135deg, #800000, #5a0000); color: white; border-radius: 10px; padding: 20px; margin-bottom: 25px; border: 2px solid #FFD700; }
        .user-avatar { width: 80px; height: 80px; background: #800000; border-radius: 50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; border:3px solid #FFD700; font-size: 28px; }
        .stats-card { background:white; border-radius:10px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.08); }
        
        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .sidebar { left: -280px; }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; width: 100%; }
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
                <i class="fas fa-user-cog me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Admin Panel</span>
                <span class="d-inline d-sm-none">Admin Panel</span>
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
                        <li><a class="dropdown-item text-danger" href="logout_admin.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
            <a href="admin_dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="user_management.php" class="nav-link"><i class="fas fa-users me-2"></i> User Management</a>
            <a href="course_management.php" class="nav-link"><i class="fas fa-book me-2"></i> Course Management</a>
            <a href="enrollment_management.php" class="nav-link"><i class="fas fa-clipboard-list me-2"></i> Enrollment</a>
            <a href="grades_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Grades</a>
             <a href="fees_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> fees</a>
            <a href="fines_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fines</a>
            <a href="reports_management.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link"><i class="fas fa-cogs me-2"></i> System Settings</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">Welcome, <?php echo htmlspecialchars($admin_info['name']); ?>! 👋</h2>
                    <p class="mb-1"><?php echo htmlspecialchars($admin_info['role']); ?> • MSU Buug Campus</p>
                    <small>Last login: 
                        <?php 
                        echo date('F j, Y, g:i a', strtotime($admin_info['last_login']));
                        ?>
                    </small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-user-cog"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-users"></i></div>
                    <h3 class="stats-number"><?php echo $total_students; ?></h3>
                    <p class="small">Total Students</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3 class="stats-number"><?php echo $total_faculty; ?></h3>
                    <p class="small">Faculty Members</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-book"></i></div>
                    <h3 class="stats-number"><?php echo $total_courses; ?></h3>
                    <p class="small">Active Courses</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="stats-number"><?php echo $pending_requests; ?></h3>
                    <p class="small">Pending Requests</p>
                </div>
            </div>
        </div>

        <!-- Admin Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="dashboard-card">
                    <h5 class="text-msu mb-4"><i class="fas fa-rocket me-2"></i>Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="user_management.php" class="btn btn-msu w-100 py-3">
                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="course_management.php" class="btn btn-msu w-100 py-3">
                                <i class="fas fa-book fa-2x mb-2"></i><br>
                                Manage Courses
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="financial_management.php" class="btn btn-msu w-100 py-3">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i><br>
                                Financial Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="system_settings.php" class="btn btn-msu w-100 py-3">
                                <i class="fas fa-cogs fa-2x mb-2"></i><br>
                                System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <h5 class="text-msu mb-3"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    <div class="list-group">
                        <?php if (!empty($recent_students)): ?>
                            <?php foreach ($recent_students as $student): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-user-plus text-success me-2"></i>
                                        <strong>New student registered</strong>
                                        <div class="text-muted small"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' - ' . $student['course']); ?></div>
                                    </div>
                                    <span class="text-muted small"><?php echo date('g:i a, M j', strtotime($student['created_at'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($recent_courses)): ?>
                            <?php foreach ($recent_courses as $course): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-book text-primary me-2"></i>
                                        <strong>New course created</strong>
                                        <div class="text-muted small"><?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?></div>
                                    </div>
                                    <span class="text-muted small"><?php echo date('g:i a, M j', strtotime($course['created_at'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($recent_payments)): ?>
                            <?php foreach ($recent_payments as $payment): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-money-bill-wave text-warning me-2"></i>
                                        <strong>Payment processed</strong>
                                        <div class="text-muted small">PHP <?php echo number_format($payment['amount'],2); ?> - <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
                                    </div>
                                    <span class="text-muted small"><?php echo date('g:i a, M j', strtotime($payment['created_at'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (empty($recent_students) && empty($recent_courses) && empty($recent_payments)): ?>
                            <div class="list-group-item text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                No recent activity to display
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5 class="text-msu mb-3"><i class="fas fa-server me-2"></i>System Status</h5>
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Database</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>User Sessions</span>
                            <span class="badge bg-info">Active</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>System Load</span>
                            <span class="badge bg-success">Normal</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Security</span>
                            <span class="badge bg-success">Enabled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
    </script>
</body>
</html>