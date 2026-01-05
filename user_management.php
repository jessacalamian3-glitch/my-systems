<?php
// user_management.php - COMPLETE WORKING USER MANAGEMENT MODULE (real DB, no mocks)
// Requirements: PHP 7.4+, MySQL, Bootstrap 5.3
// Configure DB credentials below before use.

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// ----------------- CONFIG -----------------
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';         // set your MySQL password
$db_name = 'msubuug_db'; // set your database name
// ------------------------------------------

// ----------------- SIMPLE CSRF -----------------
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
// ------------------------------------------------

// ----------------- DB CONNECTION -----------------
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die('Database connection error: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
// --------------------------------------------------

// ----------------- SESSION CHECK -----------------
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_form.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login_form.php");
    exit();
}

if (!isset($_SESSION['user_info'])) {
    $_SESSION['user_info'] = [
        'name' => 'System Administrator',
        'email' => 'admin@example.com',
        'department' => 'IT Department',
        'position' => 'System Admin',
        'username' => 'admin',
        'created_at' => '2024-01-01',
        'last_login' => date('Y-m-d H:i:s')
    ];
}
$admin_info = $_SESSION['user_info'];
// --------------------------------------------------

// ----------------- HELPER: flash messages -----------------
function flash_set($key, $msg) {
    $_SESSION['flash'][$key] = $msg;
}
function flash_get($key) {
    if (isset($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}
// ----------------------------------------------------------

// ----------------- HANDLE POST ACTIONS -----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Very small CSRF check
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $posted_token)) {
        flash_set('error', 'Invalid CSRF token.');
        header("Location: user_management.php");
        exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        // Gather & validate
        $role = $_POST['role'] ?? 'student';
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Get the correct user ID based on role
        $user_id_val = '';
        if ($role === 'student') {
            $user_id_val = trim($_POST['student_id'] ?? '');
        } elseif ($role === 'faculty') {
            $user_id_val = trim($_POST['faculty_id'] ?? '');
        } else {
            $user_id_val = trim($_POST['admin_id'] ?? '');
        }
        
        $department = trim($_POST['department'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year_level = trim($_POST['year_level'] ?? '1st Year'); // New field

        // Enhanced validation
        $errors = [];
        
        if (empty($user_id_val)) {
            $errors[] = "User ID is required.";
        }
        if (empty($fullname)) {
            $errors[] = "Full name is required.";
        }
        if (empty($email)) {
            $errors[] = "Email is required.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        if (!empty($errors)) {
            flash_set('error', implode('<br>', $errors));
            header("Location: user_management.php");
            exit();
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // INSERT INTO CORRECT TABLE BASED ON ROLE
        try {
            switch($role) {
                case 'student':
                    // ENHANCED duplicate check for students
                    $student_id_clean = trim($user_id_val);
                    $email_clean = trim(strtolower($email));
                    
                    error_log("=== STUDENT CREATION ATTEMPT ===");
                    error_log("Student ID: '$student_id_clean'");
                    error_log("Email: '$email_clean'");
                    
                    // Check for exact duplicates
                    $check_stmt = $mysqli->prepare("SELECT student_id, email FROM students WHERE student_id = ? OR LOWER(email) = ?");
                    $check_stmt->bind_param('ss', $student_id_clean, $email_clean);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    $existing_student_id = false;
                    $existing_email = false;
                    
                    while ($row = $check_result->fetch_assoc()) {
                        if ($row['student_id'] === $student_id_clean) {
                            $existing_student_id = true;
                        }
                        if (strtolower($row['email']) === $email_clean) {
                            $existing_email = true;
                        }
                    }
                    $check_stmt->close();
                    
                    if ($existing_student_id || $existing_email) {
                        if ($existing_student_id) {
                            $errors[] = "Student ID '$student_id_clean' already exists!";
                            error_log("Duplicate Student ID found: '$student_id_clean'");
                        }
                        if ($existing_email) {
                            $errors[] = "Email '$email_clean' already exists!";
                            error_log("Duplicate Email found: '$email_clean'");
                        }
                        break;
                    }
                    
                    error_log("No duplicates found, proceeding with student insertion...");

                    // Insert into students table
                    $names = explode(' ', $fullname, 2);
                    $first_name = $names[0] ?? '';
                    $last_name = $names[1] ?? '';

                    $stmt = $mysqli->prepare("INSERT INTO students (student_id, password, first_name, last_name, email, course, year_level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('ssssssss', $student_id_clean, $password_hash, $first_name, $last_name, $email_clean, $course, $year_level, $status);
                    break;

                case 'faculty':
                    // ENHANCED duplicate check for faculty
                    $faculty_id_clean = trim($user_id_val);
                    $email_clean = trim(strtolower($email));
                    
                    error_log("=== FACULTY CREATION ATTEMPT ===");
                    error_log("Faculty ID: '$faculty_id_clean'");
                    error_log("Email: '$email_clean'");
                    
                    // Check for exact duplicates
                    $check_stmt = $mysqli->prepare("SELECT faculty_id, email FROM faculty WHERE faculty_id = ? OR LOWER(email) = ?");
                    $check_stmt->bind_param('ss', $faculty_id_clean, $email_clean);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    $existing_faculty_id = false;
                    $existing_email = false;
                    
                    while ($row = $check_result->fetch_assoc()) {
                        if ($row['faculty_id'] === $faculty_id_clean) {
                            $existing_faculty_id = true;
                        }
                        if (strtolower($row['email']) === $email_clean) {
                            $existing_email = true;
                        }
                    }
                    $check_stmt->close();
                    
                    if ($existing_faculty_id || $existing_email) {
                        if ($existing_faculty_id) {
                            $errors[] = "Faculty ID '$faculty_id_clean' already exists!";
                            error_log("Duplicate Faculty ID found: '$faculty_id_clean'");
                        }
                        if ($existing_email) {
                            $errors[] = "Email '$email_clean' already exists!";
                            error_log("Duplicate Email found: '$email_clean'");
                        }
                        break;
                    }
                    
                    error_log("No duplicates found, proceeding with faculty insertion...");

                    // Insert into faculty table - FIXED VERSION
                    $names = explode(' ', $fullname, 2);
                    $first_name = $names[0] ?? '';
                    $last_name = $names[1] ?? '';
                    $position = 'Professor'; // default
                    $current_time = date('Y-m-d H:i:s');

                    $stmt = $mysqli->prepare("INSERT INTO faculty (
                        faculty_id, 
                        first_name, 
                        last_name, 
                        email, 
                        department, 
                        position, 
                        password, 
                        status,
                        date_registered,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->bind_param(
                        'ssssssssss', 
                        $faculty_id_clean, 
                        $first_name, 
                        $last_name, 
                        $email_clean, 
                        $department, 
                        $position, 
                        $password_hash, 
                        $status,
                        $current_time,
                        $current_time
                    );
                    break;

                case 'admin':
                    // ENHANCED duplicate check for admin
                    $admin_id_clean = trim($user_id_val);
                    $email_clean = trim(strtolower($email));
                    
                    error_log("=== ADMIN CREATION ATTEMPT ===");
                    error_log("Admin ID: '$admin_id_clean'");
                    error_log("Email: '$email_clean'");
                    
                    // Check for exact duplicates
                    $check_stmt = $mysqli->prepare("SELECT admin_id, email FROM admin_users WHERE admin_id = ? OR LOWER(email) = ?");
                    $check_stmt->bind_param('ss', $admin_id_clean, $email_clean);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    $existing_admin_id = false;
                    $existing_email = false;
                    
                    while ($row = $check_result->fetch_assoc()) {
                        if ($row['admin_id'] === $admin_id_clean) {
                            $existing_admin_id = true;
                        }
                        if (strtolower($row['email']) === $email_clean) {
                            $existing_email = true;
                        }
                    }
                    $check_stmt->close();
                    
                    if ($existing_admin_id || $existing_email) {
                        if ($existing_admin_id) {
                            $errors[] = "Admin ID '$admin_id_clean' already exists!";
                            error_log("Duplicate Admin ID found: '$admin_id_clean'");
                        }
                        if ($existing_email) {
                            $errors[] = "Email '$email_clean' already exists!";
                            error_log("Duplicate Email found: '$email_clean'");
                        }
                        break;
                    }
                    
                    error_log("No duplicates found, proceeding with admin insertion...");

                    // Insert into admin_users table
                    $admin_role = 'admin'; // default role

                    $stmt = $mysqli->prepare("INSERT INTO admin_users (admin_id, password, name, email, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('ssssss', $admin_id_clean, $password_hash, $fullname, $email_clean, $admin_role, $status);
                    break;
            }

            if (!empty($errors)) {
                flash_set('error', implode('<br>', $errors));
                header("Location: user_management.php");
                exit();
            }

            if (isset($stmt) && $stmt->execute()) {
                flash_set('success', ucfirst($role) . ' created successfully!');
                error_log(ucfirst($role) . " created successfully: $user_id_val");
            } else {
                $error_msg = isset($stmt) ? $stmt->error : 'Unknown error';
                flash_set('error', 'Error creating ' . $role . ': ' . $error_msg);
                error_log("Error creating $role: " . $error_msg);
            }
            if (isset($stmt)) {
                $stmt->close();
            }

        } catch (Exception $e) {
            flash_set('error', 'Error: ' . $e->getMessage());
            error_log("Exception in user creation: " . $e->getMessage());
        }

        header("Location: user_management.php");
        exit();
    }
}
// --------------------------------------------------

// ----------------- FETCH USERS FROM ALL TABLES -----------------
$all_users = [];

// Fetch from students table
$res = $mysqli->query("SELECT id, student_id as user_id, CONCAT(first_name, ' ', last_name) as name, email, 'student' as role, status, course, year_level, '' as department, created_at, IFNULL(DATE_FORMAT(last_login, '%Y-%m-%d %H:%i:%s'),'') AS last_login FROM students ORDER BY created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $all_users[] = $row;
    }
    $res->free();
}

// Fetch from faculty table - UPDATED
$res = $mysqli->query("
    SELECT 
        id, 
        faculty_id as user_id, 
        CONCAT(first_name, ' ', last_name) as name, 
        email, 
        'faculty' as role, 
        status, 
        '' as course, 
        '' as year_level,
        department, 
        date_registered as created_at, 
        IFNULL(DATE_FORMAT(last_login, '%Y-%m-%d %H:%i:%s'),'') AS last_login 
    FROM faculty 
    ORDER BY date_registered DESC
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $all_users[] = $row;
    }
    $res->free();
}

// Fetch from admin_users table
$res = $mysqli->query("SELECT admin_id as user_id, name, email, role, status, '' as course, '' as year_level, '' as department, created_at, '' as last_login FROM admin_users ORDER BY created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        // Add dummy id for admin users for compatibility
        $row['id'] = 'admin_' . $row['user_id'];
        $all_users[] = $row;
    }
    $res->free();
}

// Sort by created_at descending instead of id
usort($all_users, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$users = $all_users;
// -------------------------------------------------

// Grab flash messages
$flash_success = flash_get('success');
$flash_error = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Management - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
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
        .user-avatar { width: 80px; height: 80px; background: #800000; border-radius: 50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; border:3px solid #FFD700; font-size: 28px; }
        .table-msu th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        .badge-active { background: #28a745; }
        .badge-inactive { background: #6c757d; }
        .badge-student { background: #800000; color: white; }
        .badge-faculty { background: #007bff; color: white; }
        .badge-admin { background: #dc3545; color: white; }
        .search-filter-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 1.5rem; }
        .password-strength { font-size: 0.875rem; margin-top: 0.25rem; }
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
                <i class="fas fa-users me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - User Management</span>
                <span class="d-inline d-sm-none">User Management</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="me-3 text-white d-none d-md-block text-end">
                    <div><strong><?php echo htmlspecialchars($admin_info['name']); ?></strong></div>
                    <small>System Administrator</small>
                </div>
                <div class="dropdown">
                    <a class="text-white text-decoration-none d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2" style="width:44px; height:44px; font-size:18px;">
                            <?php echo strtoupper(substr(explode(' ', $admin_info['name'])[0], 0, 1)); ?>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2 text-msu"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2 text-msu"></i>Settings</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item text-danger" href="admin_login.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
                    <small>System Administrator</small>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column pt-3">
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="user_management.php" class="nav-link active"><i class="fas fa-users me-2"></i> User Management</a>
            <a href="course_management.php" class="nav-link"><i class="fas fa-book me-2"></i> Course Management</a>
            <a href="enrollment_management.php" class="nav-link"><i class="fas fa-clipboard-list me-2"></i> Enrollment</a>
            <a href="grades_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Grades</a>
                        <a href="fees_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fees</a>
            <a href="fines_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> fines</a>
            <a href="reports_management.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link"><i class="fas fa-cogs me-2"></i> System Settings</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Flash messages -->
        <?php if (isset($flash_success) && $flash_success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($flash_success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($flash_error) && $flash_error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($flash_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">User Management System</h2>
                    <p class="mb-1">Manage all user accounts, roles, and permissions</p>
                    <small>Total Users: <?php echo count($users); ?> | Active: <?php
                        $activeCount = 0;
                        foreach ($users as $u) if ($u['status'] === 'active') $activeCount++;
                        echo $activeCount;
                    ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-graduate"></i></div>
                    <h3 class="stats-number"><?php
                        $sCount = 0; foreach ($users as $u) if ($u['role'] === 'student') $sCount++;
                        echo $sCount;
                    ?></h3>
                    <p class="small">Students</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3 class="stats-number"><?php
                        $fCount = 0; foreach ($users as $u) if ($u['role'] === 'faculty') $fCount++;
                        echo $fCount;
                    ?></h3>
                    <p class="small">Faculty</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-check"></i></div>
                    <h3 class="stats-number"><?php echo $activeCount; ?></h3>
                    <p class="small">Active Users</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-clock"></i></div>
                    <h3 class="stats-number"><?php echo count($users) - $activeCount; ?></h3>
                    <p class="small">Inactive Users</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter-card">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search users..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="roleFilter">
                        <option value="">All Roles</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-msu w-100" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-1"></i> Add User
                    </button>
                </div>
            </div>
        </div>

        <!-- User Management Table -->
        <div class="dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-msu mb-0">All Users</h5>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="location.reload();">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead class="table-msu">
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department/Course</th>
                                <th>Year Level</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): 
                                // prepare JSON data for JS usage (escape)
                                $json = json_encode($user, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
                                $badgeRole = $user['role'] === 'student' ? 'badge-student' : ($user['role'] === 'faculty' ? 'badge-faculty' : 'badge-admin');
                                $badgeStatus = $user['status'] === 'active' ? 'badge-active' : 'badge-inactive';
                            ?>
                           <tr data-user='<?php echo htmlspecialchars($json, ENT_QUOTES, 'UTF-8'); ?>' data-user-id="<?php echo isset($user['id']) ? $user['id'] : $user['user_id']; ?>">
                                <td><strong><?php echo htmlspecialchars($user['user_id']); ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-2" style="width:32px; height:32px; font-size:12px;">
                                            <?php echo strtoupper(substr(explode(' ', $user['name'])[0], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $badgeRole; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['role'] === 'student' ? $user['course'] : $user['department']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['year_level'] ?? '-'); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeStatus; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['last_login']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <span class="text-muted">View Only</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($users) === 0): ?>
                                <tr><td colspan="9" class="text-center">No users found. <a href="#" data-bs-toggle="modal" data-bs-target="#addUserModal">Add your first user</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD USER MODAL -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addUserForm" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="create_user">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Add New User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">User Role</label>
                            <select class="form-select" name="role" id="roleSelect" required>
                                <option value="student" selected>Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>

                        <!-- Student fields -->
                        <div id="studentFields">
                            <h6 class="text-msu mt-3">Student Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Student ID *</label>
                                    <input type="text" class="form-control" name="student_id" placeholder="e.g. 2024-00001" required>
                                    <div class="form-text">Required for students</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Course *</label>
                                    <input type="text" class="form-control" name="course" placeholder="e.g. BS Information Technology" required>
                                    <div class="form-text">Enter the course/program</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Year Level *</label>
                                    <input type="text" class="form-control" name="year_level" placeholder="e.g. 1st Year, 2nd Year" required>
                                    <div class="form-text">Enter year level</div>
                                </div>
                            </div>
                        </div>

                        <!-- Faculty fields -->
                        <div id="facultyFields" style="display:none;">
                            <h6 class="text-msu mt-3">Faculty Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Faculty ID *</label>
                                    <input type="text" class="form-control" name="faculty_id" placeholder="e.g. FAC-001" required>
                                    <div class="form-text">Required for faculty</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department *</label>
                                    <input type="text" class="form-control" name="department" placeholder="e.g. College of Information Technology" required>
                                    <div class="form-text">Enter the department</div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin fields -->
                        <div id="adminFields" style="display:none;">
                            <h6 class="text-msu mt-3">Administrator Information</h6>
                            <div class="mb-3">
                                <label class="form-label">Admin ID *</label>
                                <input type="text" class="form-control" name="admin_id" placeholder="e.g. ADM-001" required>
                                <div class="form-text">Required for administrators</div>
                            </div>
                        </div>

                        <!-- Shared fields -->
                        <h6 class="text-msu mt-3">Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="fullname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="text-msu mt-3">Account Security</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" id="passwordInput" required>
                                <div class="password-strength" id="passwordStrength"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirmPasswordInput" required>
                                <div class="form-text" id="passwordMatch"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Role select change in Add User modal
        const roleSelect = document.getElementById('roleSelect');
        const studentFields = document.getElementById('studentFields');
        const facultyFields = document.getElementById('facultyFields');
        const adminFields = document.getElementById('adminFields');

        function switchAddRole(role) {
            studentFields.style.display = role === 'student' ? 'block' : 'none';
            facultyFields.style.display = role === 'faculty' ? 'block' : 'none';
            adminFields.style.display = role === 'admin' ? 'block' : 'none';
            
            // Update required fields
            const studentIdInput = document.querySelector('input[name="student_id"]');
            const facultyIdInput = document.querySelector('input[name="faculty_id"]');
            const adminIdInput = document.querySelector('input[name="admin_id"]');
            const courseInput = document.querySelector('input[name="course"]');
            const departmentInput = document.querySelector('input[name="department"]');
            const yearLevelInput = document.querySelector('input[name="year_level"]');
            
            if (role === 'student') {
                studentIdInput.required = true;
                facultyIdInput.required = false;
                adminIdInput.required = false;
                courseInput.required = true;
                departmentInput.required = false;
                yearLevelInput.required = true;
            } else if (role === 'faculty') {
                studentIdInput.required = false;
                facultyIdInput.required = true;
                adminIdInput.required = false;
                courseInput.required = false;
                departmentInput.required = true;
                yearLevelInput.required = false;
            } else if (role === 'admin') {
                studentIdInput.required = false;
                facultyIdInput.required = false;
                adminIdInput.required = true;
                courseInput.required = false;
                departmentInput.required = false;
                yearLevelInput.required = false;
            }
        }

        roleSelect.addEventListener('change', function() {
            switchAddRole(this.value);
        });

        // Initialize role fields
        switchAddRole(roleSelect.value);

        // Password strength checker
        const passwordInput = document.getElementById('passwordInput');
        const passwordStrength = document.getElementById('passwordStrength');
        const confirmPasswordInput = document.getElementById('confirmPasswordInput');
        const passwordMatch = document.getElementById('passwordMatch');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 'Weak';
            let color = 'red';
            
            if (password.length >= 8) {
                strength = 'Medium';
                color = 'orange';
            }
            if (password.length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) {
                strength = 'Strong';
                color = 'green';
            }
            
            passwordStrength.innerHTML = `Password strength: <span style="color:${color}; font-weight:bold">${strength}</span>`;
        });

        // Password match checker
        confirmPasswordInput.addEventListener('input', function() {
            if (passwordInput.value !== this.value) {
                passwordMatch.innerHTML = '<span style="color:red">Passwords do not match</span>';
            } else {
                passwordMatch.innerHTML = '<span style="color:green">Passwords match</span>';
            }
        });

        // Search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const usersTable = document.getElementById('usersTable');
        const rows = usersTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        function filterUsers() {
            const searchTerm = searchInput.value.toLowerCase();
            const roleValue = roleFilter.value;
            const statusValue = statusFilter.value;

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const userData = JSON.parse(row.getAttribute('data-user'));
                const name = userData.name.toLowerCase();
                const email = userData.email.toLowerCase();
                const user_id = userData.user_id.toLowerCase();
                const role = userData.role;
                const status = userData.status;

                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || user_id.includes(searchTerm);
                const matchesRole = !roleValue || role === roleValue;
                const matchesStatus = !statusValue || status === statusValue;

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        searchInput.addEventListener('input', filterUsers);
        roleFilter.addEventListener('change', filterUsers);
        statusFilter.addEventListener('change', filterUsers);

        // Form validation
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            const password = document.getElementById('passwordInput').value;
            const confirmPassword = document.getElementById('confirmPasswordInput').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            return true;
        });

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 991.98 && 
                !sidebar.contains(e.target) && 
                !mobileMenuToggle.contains(e.target) && 
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        console.log('User Management System loaded successfully');
    </script>
</body>
</html>