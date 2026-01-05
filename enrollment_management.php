<?php
// admin_enrollment.php - ADMIN ENROLLMENT MANAGEMENT - UPDATED TO MATCH DASHBOARD DESIGN
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// STRICT SESSION VALIDATION - ADMIN ONLY (matching admin_dashboard.php)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// STRICT ADMIN ACCESS CONTROL (matching admin_dashboard.php)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    if (isset($_SESSION['user_type'])) {
        switch ($_SESSION['user_type']) {
            case 'student':
                header("Location: student_dashboard.php");
                exit();
            case 'faculty':
                header("Location: faculty_dashboard.php");
                exit();
            default:
                header("Location: index.php");
                exit();
        }
    } else {
        header("Location: index.php");
        exit();
    }
}

// Database connection (matching admin_dashboard.php)
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

// Get admin info from session (matching admin_dashboard.php)
$admin_info = [
    'name' => $_SESSION['user_info']['name'] ?? 'System Administrator',
    'role' => $_SESSION['user_info']['role'] ?? 'Admin',
    'username' => $_SESSION['username'] ?? 'admin'
];

// Function to get all enrollments
function getAllEnrollments($pdo) {
    if ($pdo) {
        $query = "SELECT 
                    e.enrollment_id,
                    e.student_id,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    s.course,
                    s.year_level,
                    c.class_id,
                    sub.subject_code,
                    sub.subject_name,
                    c.section,
                    CONCAT(f.first_name, ' ', f.last_name) as teacher,
                    e.enrollment_date,
                    e.status,
                    e.grade,
                    e.remarks,
                    e.created_at,
                    e.updated_at
                  FROM enrollments e
                  JOIN students s ON e.student_id = s.student_id
                  JOIN classes c ON e.class_id = c.class_id
                  JOIN subjects sub ON c.subject_id = sub.subject_id
                  JOIN faculty f ON c.faculty_id = f.faculty_id
                  ORDER BY e.enrollment_date DESC, e.enrollment_id DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get all students
function getAllStudents($pdo) {
    if ($pdo) {
        $query = "SELECT student_id, first_name, last_name, course, year_level, email 
                  FROM students 
                  WHERE status = 'Active' 
                  ORDER BY last_name, first_name";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get all active classes
function getAllClasses($pdo) {
    if ($pdo) {
        $query = "SELECT 
                    c.class_id,
                    s.subject_code,
                    s.subject_name,
                    s.units,
                    c.section,
                    CONCAT(f.first_name, ' ', f.last_name) as teacher,
                    (SELECT COUNT(*) FROM enrollments WHERE class_id = c.class_id AND status = 'Active') as enrolled_count,
                    c.max_students
                  FROM classes c
                  JOIN subjects s ON c.subject_id = s.subject_id
                  JOIN faculty f ON c.faculty_id = f.faculty_id
                  WHERE c.status = 'active'
                  ORDER BY s.subject_code, c.section";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to add new enrollment
function addEnrollment($pdo, $student_id, $class_id, $status, $remarks) {
    if ($pdo) {
        // Check if student is already enrolled in this class
        $checkQuery = "SELECT * FROM enrollments 
                       WHERE student_id = :student_id 
                       AND class_id = :class_id 
                       AND status IN ('Active', 'Completed')";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->bindParam(':student_id', $student_id);
        $checkStmt->bindParam(':class_id', $class_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            return "Student is already enrolled in this class!";
        }
        
        // Check class capacity
        $capacityQuery = "SELECT 
                            c.max_students,
                            (SELECT COUNT(*) FROM enrollments 
                             WHERE class_id = c.class_id AND status = 'Active') as current_enrolled
                          FROM classes c
                          WHERE c.class_id = :class_id";
        $capacityStmt = $pdo->prepare($capacityQuery);
        $capacityStmt->bindParam(':class_id', $class_id);
        $capacityStmt->execute();
        $capacity = $capacityStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($capacity && $capacity['max_students'] > 0 && 
            $capacity['current_enrolled'] >= $capacity['max_students']) {
            return "Class is already at full capacity!";
        }
        
        // Insert new enrollment
        $query = "INSERT INTO enrollments 
                  (student_id, class_id, enrollment_date, status, remarks, created_at, updated_at)
                  VALUES (:student_id, :class_id, NOW(), :status, :remarks, NOW(), NOW())";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':remarks', $remarks);
        
        if ($stmt->execute()) {
            return "success";
        } else {
            return "Failed to add enrollment!";
        }
    }
    return "Database connection error!";
}

// Function to update enrollment
function updateEnrollment($pdo, $enrollment_id, $status, $grade, $remarks) {
    if ($pdo) {
        $query = "UPDATE enrollments 
                  SET status = :status, 
                      grade = :grade, 
                      remarks = :remarks,
                      updated_at = NOW()
                  WHERE enrollment_id = :enrollment_id";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':enrollment_id', $enrollment_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':remarks', $remarks);
        
        if ($stmt->execute()) {
            return "success";
        } else {
            return "Failed to update enrollment!";
        }
    }
    return "Database connection error!";
}

// Function to delete enrollment
function deleteEnrollment($pdo, $enrollment_id) {
    if ($pdo) {
        $query = "DELETE FROM enrollments WHERE enrollment_id = :enrollment_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':enrollment_id', $enrollment_id);
        
        if ($stmt->execute()) {
            return "success";
        } else {
            return "Failed to delete enrollment!";
        }
    }
    return "Database connection error!";
}

// Process form submissions
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $student_id = $_POST['student_id'] ?? '';
            $class_id = $_POST['class_id'] ?? '';
            $status = $_POST['status'] ?? 'Active';
            $remarks = $_POST['remarks'] ?? '';
            
            $result = addEnrollment($pdo, $student_id, $class_id, $status, $remarks);
            
            if ($result === "success") {
                $message = "Enrollment added successfully!";
                $message_type = "success";
            } else {
                $message = $result;
                $message_type = "danger";
            }
        }
        elseif ($action === 'update') {
            $enrollment_id = $_POST['enrollment_id'] ?? '';
            $status = $_POST['status'] ?? '';
            $grade = $_POST['grade'] ?? NULL;
            $remarks = $_POST['remarks'] ?? '';
            
            $result = updateEnrollment($pdo, $enrollment_id, $status, $grade, $remarks);
            
            if ($result === "success") {
                $message = "Enrollment updated successfully!";
                $message_type = "success";
            } else {
                $message = $result;
                $message_type = "danger";
            }
        }
        elseif ($action === 'delete') {
            $enrollment_id = $_POST['enrollment_id'] ?? '';
            
            $result = deleteEnrollment($pdo, $enrollment_id);
            
            if ($result === "success") {
                $message = "Enrollment deleted successfully!";
                $message_type = "success";
            } else {
                $message = $result;
                $message_type = "danger";
            }
        }
    }
}

// Get data
$enrollments = getAllEnrollments($pdo);
$students = getAllStudents($pdo);
$classes = getAllClasses($pdo);

// Statistics
$total_enrollments = count($enrollments);
$active_enrollments = 0;
$completed_enrollments = 0;
$today_count = 0;
$today = date('Y-m-d');

foreach ($enrollments as $e) {
    if ($e['status'] == 'Active') $active_enrollments++;
    if ($e['status'] == 'Completed') $completed_enrollments++;
    if (substr($e['enrollment_date'], 0, 10) == $today) {
        $today_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Enrollment Management - MSU Buug Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        /* COPY FROM ADMIN DASHBOARD */
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
        .stats-card { background:white; border-radius:10px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.08); border-top: 4px solid #800000; }
        
        /* ENROLLMENT SPECIFIC STYLES */
        .stats-number { font-size: 2rem; font-weight: bold; color: #800000; }
        .badge-active { background: #28a745; color: white; }
        .badge-completed { background: #6c757d; color: white; }
        .badge-dropped { background: #dc3545; color: white; }
        .badge-pending { background: #ffc107; color: #212529; }
        .table th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        
        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .sidebar { left: -280px; }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <!-- Navigation Bar (SAME AS ADMIN DASHBOARD) -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle d-lg-none btn btn-link text-white" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand nav-brand" href="admin_dashboard.php">
                <i class="fas fa-user-graduate me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Enrollment Management</span>
                <span class="d-inline d-sm-none">Enrollment</span>
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
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar (SAME AS ADMIN DASHBOARD) -->
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
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="user_management.php" class="nav-link"><i class="fas fa-users me-2"></i> User Management</a>
            <a href="course_management.php" class="nav-link"><i class="fas fa-book me-2"></i> Course Management</a>
            <a href="admin_enrollment.php" class="nav-link active"><i class="fas fa-clipboard-list me-2"></i> Enrollment</a>
            <a href="grades_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Grades</a>
            <a href="fees_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fees</a>
            <a href="fines_management.php" class="nav-link"><i class="fas fa-exclamation-triangle me-2"></i> Fines</a>
            <a href="reports_management.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link"><i class="fas fa-cogs me-2"></i> System Settings</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner (SAME STYLE AS DASHBOARD) -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">Enrollment Management <i class="fas fa-user-graduate ms-2"></i></h2>
                    <p class="mb-1">Manage student enrollments and class assignments</p>
                    <small>Total Records: <?php echo $total_enrollments; ?> enrollments</small>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
                        <i class="fas fa-plus-circle me-2"></i> Add New Enrollment
                    </button>
                </div>
            </div>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards (SAME STYLE AS DASHBOARD) -->
        <div class="row">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $total_enrollments; ?></h3>
                    <p class="small">Total Enrollments</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#28a745;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $active_enrollments; ?></h3>
                    <p class="small">Active Enrollments</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#6c757d;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $completed_enrollments; ?></h3>
                    <p class="small">Completed</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#007bff;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h3 class="stats-number"><?php echo $today_count; ?></h3>
                    <p class="small">Today's Enrollments</p>
                </div>
            </div>
        </div>

        <!-- Enrollment Table -->
        <div class="dashboard-card">
            <h5 class="text-msu mb-4"><i class="fas fa-table me-2"></i>All Enrollments</h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="enrollmentTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td><strong>#<?php echo $enrollment['enrollment_id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong><br>
                                <small class="text-muted">ID: <?php echo $enrollment['student_id']; ?></small><br>
                                <small class="text-muted"><?php echo $enrollment['course']; ?> - Yr <?php echo $enrollment['year_level']; ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($enrollment['subject_code']); ?></strong><br>
                                <small><?php echo htmlspecialchars($enrollment['subject_name']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($enrollment['section']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['teacher']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                            <td>
                                <?php 
                                $badge_class = 'badge-secondary';
                                switch($enrollment['status']) {
                                    case 'Active': $badge_class = 'badge-active'; break;
                                    case 'Completed': $badge_class = 'badge-completed'; break;
                                    case 'Dropped': $badge_class = 'badge-dropped'; break;
                                    case 'Pending': $badge_class = 'badge-pending'; break;
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo $enrollment['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($enrollment['grade'])): ?>
                                    <span class="badge bg-info"><?php echo $enrollment['grade']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary mb-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editEnrollmentModal"
                                        data-id="<?php echo $enrollment['enrollment_id']; ?>"
                                        data-status="<?php echo $enrollment['status']; ?>"
                                        data-grade="<?php echo $enrollment['grade']; ?>"
                                        data-remarks="<?php echo htmlspecialchars($enrollment['remarks']); ?>"
                                        onclick="setEditData(this)"
                                        title="Edit Enrollment">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['enrollment_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this enrollment?')"
                                            title="Delete Enrollment">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Enrollment Modal -->
    <div class="modal fade" id="addEnrollmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Enrollment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Student *</label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">Choose student...</option>
                                    <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['student_id']; ?>">
                                        <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' - ' . $student['student_id'] . ' (' . $student['course'] . ')'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">Choose class...</option>
                                    <?php foreach ($classes as $class): ?>
                                    <?php 
                                    $capacity_info = '';
                                    if ($class['max_students'] > 0) {
                                        $available = $class['max_students'] - $class['enrolled_count'];
                                        $capacity_info = " (Enrolled: {$class['enrolled_count']}/{$class['max_students']}, Available: {$available})";
                                    }
                                    ?>
                                    <option value="<?php echo $class['class_id']; ?>">
                                        <?php echo htmlspecialchars($class['subject_code'] . ' - ' . $class['subject_name'] . ' - Sec: ' . $class['section'] . ' - ' . $class['teacher'] . $capacity_info); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Dropped">Dropped</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-msu">Add Enrollment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Enrollment Modal -->
    <div class="modal fade" id="editEnrollmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Enrollment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="enrollment_id" id="edit_enrollment_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" id="edit_status" required>
                                <option value="Active">Active</option>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Dropped">Dropped</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Grade (if completed)</label>
                            <input type="number" step="0.01" min="1.00" max="5.00" 
                                   class="form-control" name="grade" id="edit_grade" 
                                   placeholder="e.g., 1.25, 2.50, 3.00">
                            <small class="text-muted">Leave blank if not graded yet</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="edit_remarks" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-msu">Update Enrollment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#enrollmentTable').DataTable({
                "pageLength": 25,
                "order": [[0, 'desc']],
                "responsive": true
            });
        });
        
        // Set edit modal data
        function setEditData(button) {
            const id = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            const grade = button.getAttribute('data-grade');
            const remarks = button.getAttribute('data-remarks');
            
            document.getElementById('edit_enrollment_id').value = id;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_grade').value = grade || '';
            document.getElementById('edit_remarks').value = remarks || '';
        }
        
        // Toggle sidebar for mobile (from dashboard)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>
</html>