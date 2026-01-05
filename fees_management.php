<?php
// fees_management.php - COMPLETE FEES MANAGEMENT MODULE
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_admin.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login_admin.php");
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

// Function to get all fees from database
function getAllFees() {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    f.fee_id,
                    f.student_id,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    f.fee_type,
                    f.description,
                    f.amount,
                    f.due_date,
                    f.status,
                    f.academic_year,
                    f.semester,
                    f.department,
                    f.fee_type_id,
                    f.created_at,
                    CASE 
                        WHEN f.due_date < CURDATE() AND f.status != 'Paid' THEN 'Overdue'
                        WHEN f.due_date = CURDATE() AND f.status != 'Paid' THEN 'Due Today'
                        WHEN f.status = 'Paid' THEN 'Paid'
                        ELSE 'Upcoming'
                    END as payment_status,
                    DATEDIFF(f.due_date, CURDATE()) as days_remaining
                  FROM fees f
                  LEFT JOIN students s ON f.student_id = s.student_id
                  ORDER BY f.due_date ASC, f.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get financial dashboard summary
function getFeesSummary() {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    COUNT(*) as total_fees,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_fee,
                    COUNT(CASE WHEN due_date < CURDATE() AND status != 'Paid' THEN 1 END) as overdue_fees,
                    SUM(CASE WHEN due_date < CURDATE() AND status != 'Paid' THEN amount ELSE 0 END) as overdue_amount,
                    COUNT(DISTINCT student_id) as students_with_fees,
                    MIN(due_date) as earliest_due,
                    MAX(due_date) as latest_due,
                    COUNT(CASE WHEN status = 'Paid' THEN 1 END) as paid_fees,
                    SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as paid_amount
                  FROM fees";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get all students from database (for dropdown)
function getAllStudents() {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT student_id, first_name, last_name FROM students ORDER BY first_name, last_name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get fee types for dropdown (SCC and Department only)
function getFeeTypes() {
    return [
        ['id' => 'TF-001', 'name' => 'SCC Fee', 'type' => 'SCC'],
        ['id' => 'TF-002', 'name' => 'Department Fee', 'type' => 'Department']
    ];
}

// Function to add fee to database
function addFeeToDatabase($fee_data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "INSERT INTO fees (student_id, fee_type, description, amount, due_date, status, academic_year, semester, department, fee_type_id, created_at) 
                  VALUES (:student_id, :fee_type, :description, :amount, :due_date, :status, :academic_year, :semester, :department, :fee_type_id, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $fee_data['student_id']);
        $stmt->bindParam(':fee_type', $fee_data['fee_type']);
        $stmt->bindParam(':description', $fee_data['description']);
        $stmt->bindParam(':amount', $fee_data['amount']);
        $stmt->bindParam(':due_date', $fee_data['due_date']);
        $stmt->bindParam(':status', $fee_data['status']);
        $stmt->bindParam(':academic_year', $fee_data['academic_year']);
        $stmt->bindParam(':semester', $fee_data['semester']);
        $stmt->bindParam(':department', $fee_data['department']);
        $stmt->bindParam(':fee_type_id', $fee_data['fee_type_id']);
        
        return $stmt->execute();
    }
    return false;
}

// Function to update fee
function updateFee($fee_id, $fee_data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "UPDATE fees SET 
                    student_id = :student_id,
                    fee_type = :fee_type,
                    description = :description,
                    amount = :amount,
                    due_date = :due_date,
                    status = :status,
                    academic_year = :academic_year,
                    semester = :semester,
                    department = :department,
                    fee_type_id = :fee_type_id
                  WHERE fee_id = :fee_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $fee_data['student_id']);
        $stmt->bindParam(':fee_type', $fee_data['fee_type']);
        $stmt->bindParam(':description', $fee_data['description']);
        $stmt->bindParam(':amount', $fee_data['amount']);
        $stmt->bindParam(':due_date', $fee_data['due_date']);
        $stmt->bindParam(':status', $fee_data['status']);
        $stmt->bindParam(':academic_year', $fee_data['academic_year']);
        $stmt->bindParam(':semester', $fee_data['semester']);
        $stmt->bindParam(':department', $fee_data['department']);
        $stmt->bindParam(':fee_type_id', $fee_data['fee_type_id']);
        $stmt->bindParam(':fee_id', $fee_id);
        
        return $stmt->execute();
    }
    return false;
}

// Function to delete fee
function deleteFee($fee_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "DELETE FROM fees WHERE fee_id = :fee_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fee_id', $fee_id);
        
        return $stmt->execute();
    }
    return false;
}

// Function to get single fee details
function getFeeDetails($fee_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT f.*, CONCAT(s.first_name, ' ', s.last_name) as student_name 
                  FROM fees f
                  LEFT JOIN students s ON f.student_id = s.student_id
                  WHERE f.fee_id = :fee_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fee_id', $fee_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

// Process Add Fee Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && !isset($_POST['edit_fee_id'])) {
    $success = addFeeToDatabase([
        'student_id' => $_POST['student_id'],
        'fee_type' => $_POST['fee_type'],
        'description' => $_POST['description'],
        'amount' => $_POST['amount'],
        'due_date' => $_POST['due_date'],
        'status' => $_POST['status'],
        'academic_year' => $_POST['academic_year'],
        'semester' => $_POST['semester'],
        'department' => $_POST['department'],
        'fee_type_id' => $_POST['fee_type_id']
    ]);
    
    if ($success) {
        $message = "Fee added successfully!";
        echo '<script>setTimeout(function(){ window.location.href = "fees_management.php"; }, 1500);</script>';
    } else {
        $error = "Failed to add fee. Please try again.";
    }
}

// Process Edit Fee Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_fee_id'])) {
    $success = updateFee($_POST['edit_fee_id'], [
        'student_id' => $_POST['edit_student_id'],
        'fee_type' => $_POST['edit_fee_type'],
        'description' => $_POST['edit_description'],
        'amount' => $_POST['edit_amount'],
        'due_date' => $_POST['edit_due_date'],
        'status' => $_POST['edit_status'],
        'academic_year' => $_POST['edit_academic_year'],
        'semester' => $_POST['edit_semester'],
        'department' => $_POST['edit_department'],
        'fee_type_id' => $_POST['edit_fee_type_id']
    ]);
    
    if ($success) {
        $message = "Fee updated successfully!";
        echo '<script>setTimeout(function(){ window.location.href = "fees_management.php"; }, 1500);</script>';
    } else {
        $error = "Failed to update fee. Please try again.";
    }
}

// Process Delete Request
if (isset($_GET['delete_fee'])) {
    $success = deleteFee($_GET['delete_fee']);
    
    if ($success) {
        $message = "Fee deleted successfully!";
    } else {
        $error = "Failed to delete fee. Please try again.";
    }
    
    // Redirect to remove delete parameter from URL
    header("Location: fees_management.php?message=" . urlencode($message ?? ''));
    exit();
}

// Process Payment Status Update
if (isset($_GET['mark_paid'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "UPDATE fees SET status = 'Paid' WHERE fee_id = :fee_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fee_id', $_GET['mark_paid']);
        $stmt->execute();
        
        $message = "Fee marked as paid!";
        header("Location: fees_management.php?message=" . urlencode($message));
        exit();
    }
}

// Get real data from database
$fees = getAllFees();
$summary = getFeesSummary();
$students = getAllStudents();
$fee_types = getFeeTypes();

// Check for messages in URL
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Fees Management - MSU Buug</title>
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
        
        /* Fees Management Specific Styles */
        .table-msu th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        .badge-overdue { background: #dc3545; }
        .badge-due-today { background: #ffc107; color: #000; }
        .badge-upcoming { background: #6c757d; }
        .badge-paid { background: #28a745; }
        .badge-scc { background: #800000; }
        .badge-department { background: #6610f2; }
        .badge-laboratory { background: #fd7e14; }
        .badge-library { background: #20c997; }
        .badge-medical { background: #e83e8c; }
        .badge-athletic { background: #17a2b8; }
        .badge-tuition { background: #343a40; }
        .badge-miscellaneous { background: #6c757d; }
        .amount-due { color: #dc3545; font-weight: bold; }
        .amount-paid { color: #28a745; font-weight: bold; }
        
        /* Status indicators */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-overdue { background: #dc3545; }
        .status-due-today { background: #ffc107; }
        .status-upcoming { background: #6c757d; }
        .status-paid { background: #28a745; }

        /* Date styling */
        .date-overdue { color: #dc3545; font-weight: bold; }
        .date-due-today { color: #ffc107; font-weight: bold; }
        .date-upcoming { color: #6c757d; }
        .date-paid { color: #28a745; }

        .search-filter-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 1.5rem; }
        
        /* Stats cards */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-top: 3px solid #800000;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #800000;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 991.98px) {
            .sidebar { left: -280px; }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; width: 100%; }
        }
        
        /* Loading overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #800000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle d-lg-none btn btn-link text-white" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand nav-brand" href="admin_dashboard.php">
                <i class="fas fa-money-bill-wave me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Fees Management</span>
                <span class="d-inline d-sm-none">Fees Management</span>
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
            <a href="user_management.php" class="nav-link"><i class="fas fa-users me-2"></i> User Management</a>
            <a href="course_management.php" class="nav-link"><i class="fas fa-book me-2"></i> Course Management</a>
            <a href="enrollment_management.php" class="nav-link"><i class="fas fa-clipboard-list me-2"></i> Enrollment</a>
            <a href="grades_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Grades</a>
            <a href="fees_management.php" class="nav-link active"><i class="fas fa-money-bill me-2"></i> Fees</a>
            <a href="fines_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fines</a>
            <a href="reports_management.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link"><i class="fas fa-cogs me-2"></i> System Settings</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">Fees Management System</h2>
                    <p class="mb-1">Manage Student Fees and Payments</p>
                    <small>Total Fees: <?php echo $summary['total_fees'] ?? 0; ?> | Total Amount: ₱<?php 
                        echo number_format($summary['total_amount'] ?? 0, 2); 
                    ?> | Overdue: <?php echo $summary['overdue_fees'] ?? 0; ?> | Paid: <?php echo $summary['paid_fees'] ?? 0; ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-money-bill"></i>
                    </div>
                </div>
            </div>
        </div>

       

        <!-- Controls Section -->
        <div class="search-filter-card">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search fees..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Overdue">Overdue</option>
                        <option value="Due Today">Due Today</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Paid">Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="semesterFilter">
                        <option value="">All Semesters</option>
                        <option value="1st">1st Semester</option>
                        <option value="2nd">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" id="yearFilter" placeholder="Academic Year">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-msu w-100" data-bs-toggle="modal" data-bs-target="#addFeeModal">
                            <i class="fas fa-plus me-1"></i> Add Fee
                        </button>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#bulkFeeModal">
                            <i class="fas fa-upload"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Fees Management Table -->
        <div class="dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-msu mb-0">All Fee Records</h5>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="refreshTable()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportFees()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="feesTable">
                        <thead class="table-msu">
                            <tr>
                                <th>Fee ID</th>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Fee Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fees)): ?>
                                <tr>
                                    <td colspan="12" class="text-center py-4">
                                        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No fees found</h5>
                                        <p class="text-muted">Add fees using the "Add Fee" button above.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fees as $fee): ?>
                                <tr data-fee-id="<?php echo $fee['fee_id']; ?>" data-fee-type-id="<?php echo $fee['fee_type_id']; ?>">
                                    <td><strong>#<?php echo $fee['fee_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($fee['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($fee['student_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $fee_type_class = 'badge-secondary';
                                        switch($fee['fee_type']) {
                                            case 'SCC': $fee_type_class = 'badge-scc'; break;
                                            case 'Department': $fee_type_class = 'badge-department'; break;
                                            default: $fee_type_class = 'badge-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $fee_type_class; ?>"><?php echo htmlspecialchars($fee['fee_type']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($fee['description']); ?></td>
                                    <td>
                                        <?php if ($fee['status'] == 'Paid'): ?>
                                            <span class="amount-paid">₱<?php echo number_format($fee['amount'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="amount-due">₱<?php echo number_format($fee['amount'], 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="<?php echo getDateClass($fee['payment_status']); ?>">
                                        <?php echo date('M d, Y', strtotime($fee['due_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $fee['payment_status'])); ?>">
                                            <span class="status-indicator status-<?php echo strtolower(str_replace(' ', '-', $fee['payment_status'])); ?>"></span>
                                            <?php echo htmlspecialchars($fee['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($fee['academic_year']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark"><?php echo htmlspecialchars($fee['semester']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($fee['department']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($fee['status'] != 'Paid'): ?>
                                                <button class="btn btn-success btn-sm" onclick="markAsPaid(<?php echo $fee['fee_id']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-msu-sm" onclick="editFee(<?php echo $fee['fee_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-info btn-sm" onclick="viewFee(<?php echo $fee['fee_id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteFee(<?php echo $fee['fee_id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODALS ===================== -->

    <!-- ADD FEE MODAL -->
    <div class="modal fade" id="addFeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addFeeForm" method="POST" onsubmit="showLoading()">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Add Student Fee</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student ID</label>
                                <input type="text" class="form-control" name="student_id" placeholder="Enter Student ID" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fee Type</label>
                                <select class="form-select" name="fee_type" id="feeTypeSelect" required onchange="updateFeeTypeId()">
                                    <option value="">Select Fee Type</option>
                                    <option value="SCC" data-id="TF-001">SCC Fee</option>
                                    <option value="Department" data-id="TF-002">Department Fee</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="description" id="descriptionField" rows="2" placeholder="Enter fee description" required></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" class="form-control" name="amount" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="Pending">Pending</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Partial">Partial Payment</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" class="form-control" name="academic_year" placeholder="e.g., 2023-2024" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" required>
                                    <option value="1st">1st Semester</option>
                                    <option value="2nd">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" name="department" placeholder="e.g., College of Education" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fee Type ID</label>
                                <input type="text" class="form-control" name="fee_type_id" id="feeTypeIdField" placeholder="Will auto-fill based on fee type" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Save Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT FEE MODAL -->
    <div class="modal fade" id="editFeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editFeeForm" method="POST" onsubmit="showLoading()">
                    <input type="hidden" name="edit_fee_id" id="editFeeId">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Edit Student Fee</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fee ID</label>
                                <input type="text" class="form-control" id="editFeeIdDisplay" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student ID</label>
                                <input type="text" class="form-control" name="edit_student_id" id="editStudentId" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student Name</label>
                                <input type="text" class="form-control" id="editStudentName" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fee Type</label>
                                <select class="form-select" name="edit_fee_type" id="editFeeType" required onchange="updateEditFeeTypeId()">
                                    <option value="">Select Fee Type</option>
                                    <option value="SCC" data-id="TF-001">SCC Fee</option>
                                    <option value="Department" data-id="TF-002">Department Fee</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="edit_description" id="editDescription" rows="2" required></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" class="form-control" name="edit_amount" id="editAmount" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="edit_due_date" id="editDueDate" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="edit_status" id="editStatus" required>
                                    <option value="Pending">Pending</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Partial">Partial Payment</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" class="form-control" name="edit_academic_year" id="editAcademicYear" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="edit_semester" id="editSemester" required>
                                    <option value="1st">1st Semester</option>
                                    <option value="2nd">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" name="edit_department" id="editDepartment" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fee Type ID</label>
                                <input type="text" class="form-control" name="edit_fee_type_id" id="editFeeTypeId" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Update Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BULK FEE MODAL -->
    <div class="modal fade" id="bulkFeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon">
                    <h5 class="modal-title text-white">Bulk Fee Upload</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Upload a CSV file with student fee data.</p>
                    <div class="mb-3">
                        <label class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" accept=".csv">
                    </div>
                    <div class="form-text">
                        <small>CSV format: StudentID, FeeType, Description, Amount, DueDate, Status, AcademicYear, Semester, Department, FeeTypeID</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-msu" type="button">Upload & Process</button>
                </div>
            </div>
        </div>
    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        // Auto-update fee type ID based on selection
        function updateFeeTypeId() {
            const select = document.getElementById('feeTypeSelect');
            const selectedOption = select.options[select.selectedIndex];
            const feeTypeId = selectedOption.getAttribute('data-id');
            document.getElementById('feeTypeIdField').value = feeTypeId || '';
        }
        
        function updateEditFeeTypeId() {
            const select = document.getElementById('editFeeType');
            const selectedOption = select.options[select.selectedIndex];
            const feeTypeId = selectedOption.getAttribute('data-id');
            document.getElementById('editFeeTypeId').value = feeTypeId || '';
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterFees();
        });

        document.getElementById('statusFilter').addEventListener('change', filterFees);
        document.getElementById('semesterFilter').addEventListener('change', filterFees);
        document.getElementById('yearFilter').addEventListener('input', filterFees);

        function filterFees() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const semesterFilter = document.getElementById('semesterFilter').value;
            const yearFilter = document.getElementById('yearFilter').value.toLowerCase();
            
            const rows = document.querySelectorAll('#feesTable tbody tr');
            
            rows.forEach(row => {
                const feeId = row.cells[0].textContent.toLowerCase();
                const studentId = row.cells[1].textContent.toLowerCase();
                const studentName = row.cells[2].textContent.toLowerCase();
                const feeType = row.cells[3].textContent.toLowerCase();
                const description = row.cells[4].textContent.toLowerCase();
                const amount = row.cells[5].textContent.toLowerCase();
                const academicYear = row.cells[8].textContent.toLowerCase();
                const semester = row.cells[9].textContent.toLowerCase();
                const status = row.cells[7].querySelector('.badge').textContent.toLowerCase();
                
                const matchesSearch = feeId.includes(searchText) || 
                                     studentId.includes(searchText) || 
                                     studentName.includes(searchText) || 
                                     feeType.includes(searchText) ||
                                     description.includes(searchText) ||
                                     amount.includes(searchText);
                const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());
                const matchesSemester = !semesterFilter || semester.includes(semesterFilter.toLowerCase());
                const matchesYear = !yearFilter || academicYear.includes(yearFilter);
                
                row.style.display = matchesSearch && matchesStatus && matchesSemester && matchesYear ? '' : 'none';
            });
        }

        // Fee management functions
        function editFee(feeId) {
            // Get fee data from the table row
            const row = document.querySelector(`tr[data-fee-id="${feeId}"]`);
            if (row) {
                const cells = row.cells;
                const feeTypeId = row.getAttribute('data-fee-type-id');
                
                document.getElementById('editFeeId').value = feeId;
                document.getElementById('editFeeIdDisplay').value = cells[0].textContent.trim();
                document.getElementById('editStudentId').value = cells[1].textContent.trim();
                document.getElementById('editStudentName').value = cells[2].textContent.trim();
                
                // Set fee type selection
                const feeTypeBadge = cells[3].querySelector('.badge').textContent.trim();
                const editFeeTypeSelect = document.getElementById('editFeeType');
                for (let i = 0; i < editFeeTypeSelect.options.length; i++) {
                    if (editFeeTypeSelect.options[i].value === feeTypeBadge) {
                        editFeeTypeSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // Trigger fee type ID update
                updateEditFeeTypeId();
                
                document.getElementById('editDescription').value = cells[4].textContent.trim();
                document.getElementById('editAmount').value = parseFloat(cells[5].textContent.replace('₱', '').trim());
                document.getElementById('editDueDate').value = formatDateForInput(cells[6].textContent.trim());
                
                // Set status selection
                const statusBadge = cells[7].querySelector('.badge').textContent.trim();
                const editStatusSelect = document.getElementById('editStatus');
                for (let i = 0; i < editStatusSelect.options.length; i++) {
                    if (editStatusSelect.options[i].textContent === statusBadge) {
                        editStatusSelect.selectedIndex = i;
                        break;
                    }
                }
                
                document.getElementById('editAcademicYear').value = cells[8].querySelector('.badge').textContent.trim();
                document.getElementById('editSemester').value = cells[9].querySelector('.badge').textContent.trim();
                document.getElementById('editDepartment').value = cells[10].textContent.trim();
                document.getElementById('editFeeTypeId').value = feeTypeId;
                
                const editModal = new bootstrap.Modal(document.getElementById('editFeeModal'));
                editModal.show();
            }
        }

        function viewFee(feeId) {
            const row = document.querySelector(`tr[data-fee-id="${feeId}"]`);
            if (row) {
                const cells = row.cells;
                
                const feeDetails = `
Fee Details:

Fee ID: ${cells[0].textContent.trim()}
Student: ${cells[2].textContent.trim()} (${cells[1].textContent.trim()})
Fee Type: ${cells[3].querySelector('.badge').textContent.trim()}
Description: ${cells[4].textContent.trim()}
Amount: ${cells[5].textContent.trim()}
Due Date: ${cells[6].textContent.trim()}
Status: ${cells[7].querySelector('.badge').textContent.trim()}
Academic Year: ${cells[8].querySelector('.badge').textContent.trim()}
Semester: ${cells[9].querySelector('.badge').textContent.trim()}
Department: ${cells[10].textContent.trim()}
                `;
                alert(feeDetails);
            }
        }

        function markAsPaid(feeId) {
            if (confirm('Are you sure you want to mark this fee as paid?')) {
                showLoading();
                window.location.href = 'fees_management.php?mark_paid=' + feeId;
            }
        }

        function deleteFee(feeId) {
            if (confirm('Are you sure you want to delete this fee record? This action cannot be undone.')) {
                showLoading();
                window.location.href = 'fees_management.php?delete_fee=' + feeId;
            }
        }

        function refreshTable() {
            showLoading();
            location.reload();
        }

        function exportFees() {
            const rows = document.querySelectorAll('#feesTable tbody tr:not([style*="none"])');
            let csv = 'Fee ID,Student ID,Student Name,Fee Type,Description,Amount,Due Date,Status,Academic Year,Semester,Department,Fee Type ID\n';
            
            rows.forEach(row => {
                const cells = row.cells;
                const feeTypeId = row.getAttribute('data-fee-type-id');
                const rowData = [
                    cells[0].textContent.trim().replace('#', ''),
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim(),
                    cells[3].querySelector('.badge').textContent.trim(),
                    cells[4].textContent.trim(),
                    cells[5].textContent.trim().replace('₱', ''),
                    cells[6].textContent.trim(),
                    cells[7].querySelector('.badge').textContent.trim(),
                    cells[8].querySelector('.badge').textContent.trim(),
                    cells[9].querySelector('.badge').textContent.trim(),
                    cells[10].textContent.trim(),
                    feeTypeId || ''
                ];
                csv += rowData.map(cell => `"${cell}"`).join(',') + '\n';
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', 'fees_export_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        // Helper function to format date for input field
        function formatDateForInput(dateString) {
            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Hide loading when page is fully loaded
        window.addEventListener('load', function() {
            hideLoading();
        });
    </script>
</body>
</html>

<?php
// Helper functions for styling
function getDateClass($status) {
    switch ($status) {
        case 'Overdue': return 'date-overdue';
        case 'Due Today': return 'date-due-today';
        case 'Upcoming': return 'date-upcoming';
        case 'Paid': return 'date-paid';
        default: return '';
    }
}
?>