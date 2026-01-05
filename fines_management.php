<?php
// financial_management.php - COMPLETE FINANCIAL MANAGEMENT MODULE
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

// Function to get all fines from database
function getAllFines() {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    f.fine_id as id,
                    f.student_id,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    f.fine_name,
                    f.amount,
                    f.event_date,
                    f.due_date,
                    f.academic_year,
                    f.created_at,
                    CASE 
                        WHEN f.due_date < CURDATE() THEN 'Overdue'
                        WHEN f.due_date = CURDATE() THEN 'Due Today'
                        ELSE 'Upcoming'
                    END as payment_status,
                    DATEDIFF(f.due_date, CURDATE()) as days_remaining
                  FROM fines f
                  LEFT JOIN students s ON f.student_id = s.student_id
                  ORDER BY f.due_date ASC, f.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get financial dashboard summary
function getFinancialSummary() {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    COUNT(*) as total_fines,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_fine,
                    COUNT(CASE WHEN due_date < CURDATE() THEN 1 END) as overdue_fines,
                    SUM(CASE WHEN due_date < CURDATE() THEN amount ELSE 0 END) as overdue_amount,
                    COUNT(DISTINCT student_id) as students_with_fines,
                    MIN(due_date) as earliest_due,
                    MAX(due_date) as latest_due
                  FROM fines";
        
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

// Function to add fine to database
function addFineToDatabase($fine_data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "INSERT INTO fines (student_id, fine_name, amount, event_date, due_date, academic_year, created_at) 
                  VALUES (:student_id, :fine_name, :amount, :event_date, :due_date, :academic_year, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $fine_data['student_id']);
        $stmt->bindParam(':fine_name', $fine_data['fine_name']);
        $stmt->bindParam(':amount', $fine_data['amount']);
        $stmt->bindParam(':event_date', $fine_data['event_date']);
        $stmt->bindParam(':due_date', $fine_data['due_date']);
        $stmt->bindParam(':academic_year', $fine_data['academic_year']);
        
        return $stmt->execute();
    }
    return false;
}

// Function to update fine
function updateFine($fine_id, $fine_data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "UPDATE fines SET 
                    student_id = :student_id,
                    fine_name = :fine_name,
                    amount = :amount,
                    event_date = :event_date,
                    due_date = :due_date,
                    academic_year = :academic_year
                  WHERE fine_id = :fine_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $fine_data['student_id']);
        $stmt->bindParam(':fine_name', $fine_data['fine_name']);
        $stmt->bindParam(':amount', $fine_data['amount']);
        $stmt->bindParam(':event_date', $fine_data['event_date']);
        $stmt->bindParam(':due_date', $fine_data['due_date']);
        $stmt->bindParam(':academic_year', $fine_data['academic_year']);
        $stmt->bindParam(':fine_id', $fine_id);
        
        return $stmt->execute();
    }
    return false;
}

// Function to delete fine
function deleteFine($fine_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "DELETE FROM fines WHERE fine_id = :fine_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fine_id', $fine_id);
        
        return $stmt->execute();
    }
    return false;
}

// Process Add Fine Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && !isset($_POST['edit_fine_id'])) {
    $success = addFineToDatabase([
        'student_id' => $_POST['student_id'],
        'fine_name' => $_POST['fine_name'],
        'amount' => $_POST['amount'],
        'event_date' => $_POST['event_date'],
        'due_date' => $_POST['due_date'],
        'academic_year' => $_POST['academic_year']
    ]);
    
    if ($success) {
        $message = "Fine added successfully!";
    } else {
        $error = "Failed to add fine. Please try again.";
    }
}

// Process Edit Fine Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_fine_id'])) {
    $success = updateFine($_POST['edit_fine_id'], [
        'student_id' => $_POST['edit_student_id'],
        'fine_name' => $_POST['edit_fine_name'],
        'amount' => $_POST['edit_amount'],
        'event_date' => $_POST['edit_event_date'],
        'due_date' => $_POST['edit_due_date'],
        'academic_year' => $_POST['edit_academic_year']
    ]);
    
    if ($success) {
        $message = "Fine updated successfully!";
    } else {
        $error = "Failed to update fine. Please try again.";
    }
}

// Process Delete Request
if (isset($_GET['delete_fine'])) {
    $success = deleteFine($_GET['delete_fine']);
    
    if ($success) {
        $message = "Fine deleted successfully!";
    } else {
        $error = "Failed to delete fine. Please try again.";
    }
    
    // Redirect to remove delete parameter from URL
    header("Location: financial_management.php");
    exit();
}

// Get real data from database
$fines = getAllFines();
$summary = getFinancialSummary();
$students = getAllStudents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Financial Management - MSU Buug</title>
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
        
        /* Financial Management Specific Styles */
        .table-msu th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        .badge-overdue { background: #dc3545; }
        .badge-due-today { background: #ffc107; color: #000; }
        .badge-upcoming { background: #28a745; }
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
        .status-upcoming { background: #28a745; }

        /* Date styling */
        .date-overdue { color: #dc3545; font-weight: bold; }
        .date-due-today { color: #ffc107; font-weight: bold; }
        .date-upcoming { color: #28a745; }

        .search-filter-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 1.5rem; }

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
                <i class="fas fa-money-bill-wave me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Financial Management</span>
                <span class="d-inline d-sm-none">Financial Management</span>
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
            <a href="fees_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Fees</a>
            <a href="fines_management.php" class="nav-link active"><i class="fas fa-money-bill-wave me-2"></i> Fines</a>
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
                    <h2 class="h4">Financial Management System</h2>
                    <p class="mb-1">Manage Student Fines</p>
                    <small>Total Fines: <?php echo $summary['total_fines'] ?? 0; ?> | Total Amount: ₱<?php 
                        echo number_format($summary['total_amount'] ?? 0, 2); 
                    ?> | Overdue: <?php echo $summary['overdue_fines'] ?? 0; ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-money-bill-wave"></i>
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
                        <input type="text" class="form-control" placeholder="Search fines..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Overdue">Overdue</option>
                        <option value="Due Today">Due Today</option>
                        <option value="Upcoming">Upcoming</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" id="fineNameFilter" placeholder="Filter by fine name">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" id="yearFilter" placeholder="Academic Year">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-msu w-100" data-bs-toggle="modal" data-bs-target="#addFineModal">
                            <i class="fas fa-plus me-1"></i> Add Fines
                        </button>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#bulkFineModal">
                            <i class="fas fa-upload"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Management Table -->
        <div class="dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-msu mb-0">All Fine Records</h5>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="refreshTable()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportFines()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="finesTable">
                        <thead class="table-msu">
                            <tr>
                                <th>Fine ID</th>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Fine Type</th>
                                <th>Fine Name</th>
                                <th>Amount</th>
                                <th>Event Date</th>
                                <th>Due Date</th>
                                <th>Days Remaining</th>
                                <th>Status</th>
                                <th>Academic Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fines)): ?>
                                <tr>
                                    <td colspan="12" class="text-center py-4">
                                        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No fines found</h5>
                                        <p class="text-muted">Add fines using the "Add Fines" button above.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fines as $fine): 
                                    // Extract fine type and name
                                    $fine_text = $fine['fine_name'];
                                    $fine_type = '';
                                    $fine_name = '';
                                    
                                    // Try to detect if it's SSC or Department
                                    if (stripos($fine_text, 'ssc') !== false || stripos($fine_text, 'student council') !== false) {
                                        $fine_type = 'SSC';
                                        $fine_name = str_ireplace(['ssc', 'student council'], '', $fine_text);
                                    } elseif (stripos($fine_text, 'department') !== false || stripos($fine_text, 'dept') !== false) {
                                        $fine_type = 'Department';
                                        $fine_name = str_ireplace(['department', 'dept'], '', $fine_text);
                                    } else {
                                        $fine_type = 'Other';
                                        $fine_name = $fine_text;
                                    }
                                ?>
                                <tr data-fine-id="<?php echo $fine['id']; ?>">
                                    <td><strong>#<?php echo $fine['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($fine['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($fine['student_name']); ?></td>
                                    <td>
                                        <?php if ($fine_type == 'SSC'): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($fine_type); ?></span>
                                        <?php elseif ($fine_type == 'Department'): ?>
                                            <span class="badge bg-success"><?php echo htmlspecialchars($fine_type); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($fine_type); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(trim($fine_name)); ?></td>
                                    <td>
                                        <span class="amount-due">₱<?php echo number_format($fine['amount'], 2); ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($fine['event_date'])); ?></td>
                                    <td class="<?php echo getDateClass($fine['payment_status']); ?>">
                                        <?php echo date('M d, Y', strtotime($fine['due_date'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($fine['days_remaining'] > 0): ?>
                                            <span class="text-success"><?php echo $fine['days_remaining']; ?> days</span>
                                        <?php elseif ($fine['days_remaining'] == 0): ?>
                                            <span class="text-warning">Today</span>
                                        <?php else: ?>
                                            <span class="text-danger"><?php echo abs($fine['days_remaining']); ?> days late</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $fine['payment_status'])); ?>">
                                            <span class="status-indicator status-<?php echo strtolower(str_replace(' ', '-', $fine['payment_status'])); ?>"></span>
                                            <?php echo htmlspecialchars($fine['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($fine['academic_year']); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-msu-sm" onclick="editFine(<?php echo $fine['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-info btn-sm" onclick="viewFine(<?php echo $fine['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteFine(<?php echo $fine['id']; ?>)">
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

    <!-- ADD FINE MODAL -->
    <div class="modal fade" id="addFineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addFineForm" method="POST">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Add Student Fine</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student ID</label>
                                <input type="number" class="form-control" name="student_id" placeholder="Enter Student ID" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fine Type</label>
                                <input type="text" class="form-control" name="fine_type" placeholder="e.g., SSC, Department, Library, etc.">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Fine Name</label>
                                <input type="text" class="form-control" name="fine_name" placeholder="Enter Fine Name/Description" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" class="form-control" name="amount" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" class="form-control" name="event_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" class="form-control" name="academic_year" placeholder="e.g., 2023-2024" required>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <small><i class="fas fa-exclamation-triangle me-1"></i> 
                                <strong>Note:</strong> This system tracks fines only. Cash payments should be recorded manually.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Save Fine</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT FINE MODAL -->
    <div class="modal fade" id="editFineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editFineForm" method="POST">
                    <input type="hidden" name="edit_fine_id" id="editFineId">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Edit Student Fine</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fine ID</label>
                                <input type="text" class="form-control" id="editFineIdDisplay" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student ID</label>
                                <input type="text" class="form-control" id="editStudentId" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student Name</label>
                                <input type="text" class="form-control" id="editStudentName" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fine Type</label>
                                <input type="text" class="form-control" name="edit_fine_type" id="editFineType" placeholder="e.g., SSC, Department, Library, etc.">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Fine Name</label>
                                <input type="text" class="form-control" name="edit_fine_name" id="editFineName" placeholder="Enter Fine Name/Description" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" class="form-control" name="edit_amount" id="editAmount" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" class="form-control" name="edit_event_date" id="editEventDate" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="edit_due_date" id="editDueDate" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" class="form-control" name="edit_academic_year" id="editAcademicYear" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" id="editStatus" readonly>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle me-1"></i> 
                                Status is automatically calculated based on due date.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Update Fine</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BULK FINE MODAL -->
    <div class="modal fade" id="bulkFineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon">
                    <h5 class="modal-title text-white">Bulk Fine Upload</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Upload a CSV file with student fine data.</p>
                    <div class="mb-3">
                        <label class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" accept=".csv">
                    </div>
                    <div class="form-text">
                        <small>CSV format: StudentID, FineName, Amount, EventDate, DueDate, AcademicYear</small>
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
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterFines();
        });

        document.getElementById('statusFilter').addEventListener('change', filterFines);
        document.getElementById('fineNameFilter').addEventListener('input', filterFines);
        document.getElementById('yearFilter').addEventListener('input', filterFines);

        function filterFines() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const fineNameFilter = document.getElementById('fineNameFilter').value.toLowerCase();
            const yearFilter = document.getElementById('yearFilter').value.toLowerCase();
            
            const rows = document.querySelectorAll('#finesTable tbody tr');
            
            rows.forEach(row => {
                const fineId = row.cells[0].textContent.toLowerCase();
                const studentId = row.cells[1].textContent.toLowerCase();
                const studentName = row.cells[2].textContent.toLowerCase();
                const fineType = row.cells[3].textContent.toLowerCase();
                const fineName = row.cells[4].textContent.toLowerCase();
                const amount = row.cells[5].textContent.toLowerCase();
                const academicYear = row.cells[10].textContent.toLowerCase();
                const status = row.cells[9].querySelector('.badge').textContent.toLowerCase();
                
                const matchesSearch = fineId.includes(searchText) || 
                                     studentId.includes(searchText) || 
                                     studentName.includes(searchText) || 
                                     fineType.includes(searchText) ||
                                     fineName.includes(searchText) ||
                                     amount.includes(searchText);
                const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());
                const matchesFineName = !fineNameFilter || fineName.includes(fineNameFilter);
                const matchesYear = !yearFilter || academicYear.includes(yearFilter);
                
                row.style.display = matchesSearch && matchesStatus && matchesFineName && matchesYear ? '' : 'none';
            });
        }

        // Fine management functions
        function editFine(fineId) {
            // Get fine data from the table row
            const row = document.querySelector(`tr[data-fine-id="${fineId}"]`);
            if (row) {
                const cells = row.cells;
                
                document.getElementById('editFineId').value = fineId;
                document.getElementById('editFineIdDisplay').value = cells[0].textContent.trim();
                document.getElementById('editStudentId').value = cells[1].textContent.trim();
                document.getElementById('editStudentName').value = cells[2].textContent.trim();
                document.getElementById('editFineType').value = cells[3].querySelector('.badge').textContent.trim();
                document.getElementById('editFineName').value = cells[4].textContent.trim();
                document.getElementById('editAmount').value = parseFloat(cells[5].textContent.replace('₱', '').trim());
                document.getElementById('editEventDate').value = formatDateForInput(cells[6].textContent.trim());
                document.getElementById('editDueDate').value = formatDateForInput(cells[7].textContent.trim());
                document.getElementById('editAcademicYear').value = cells[10].querySelector('.badge').textContent.trim();
                document.getElementById('editStatus').value = cells[9].querySelector('.badge').textContent.trim();
                
                const editModal = new bootstrap.Modal(document.getElementById('editFineModal'));
                editModal.show();
            }
        }

        function viewFine(fineId) {
            const row = document.querySelector(`tr[data-fine-id="${fineId}"]`);
            if (row) {
                const cells = row.cells;
                
                const fineDetails = `
Fine Details:

Fine ID: ${cells[0].textContent.trim()}
Student: ${cells[2].textContent.trim()} (${cells[1].textContent.trim()})
Fine Type: ${cells[3].querySelector('.badge').textContent.trim()}
Fine Name: ${cells[4].textContent.trim()}
Amount: ${cells[5].textContent.trim()}
Event Date: ${cells[6].textContent.trim()}
Due Date: ${cells[7].textContent.trim()}
Days Remaining: ${cells[8].textContent.trim()}
Status: ${cells[9].querySelector('.badge').textContent.trim()}
Academic Year: ${cells[10].querySelector('.badge').textContent.trim()}
                `;
                alert(fineDetails);
            }
        }

        function deleteFine(fineId) {
            if (confirm('Are you sure you want to delete this fine record? This action cannot be undone.')) {
                window.location.href = 'financial_management.php?delete_fine=' + fineId;
            }
        }

        function refreshTable() {
            location.reload();
        }

        function exportFines() {
            const rows = document.querySelectorAll('#finesTable tbody tr:not([style*="none"])');
            let csv = 'Fine ID,Student ID,Student Name,Fine Type,Fine Name,Amount,Event Date,Due Date,Status,Academic Year\n';
            
            rows.forEach(row => {
                const cells = row.cells;
                const rowData = [
                    cells[0].textContent.trim().replace('#', ''),
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim(),
                    cells[3].querySelector('.badge').textContent.trim(),
                    cells[4].textContent.trim(),
                    cells[5].textContent.trim().replace('₱', ''),
                    cells[6].textContent.trim(),
                    cells[7].textContent.trim(),
                    cells[9].querySelector('.badge').textContent.trim(),
                    cells[10].querySelector('.badge').textContent.trim()
                ];
                csv += rowData.map(cell => `"${cell}"`).join(',') + '\n';
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', 'fines_export_' + new Date().toISOString().slice(0, 10) + '.csv');
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

        // Set minimum date for due date based on event date
        document.addEventListener('DOMContentLoaded', function() {
            const eventDateInput = document.querySelector('input[name="event_date"]');
            const dueDateInput = document.querySelector('input[name="due_date"]');
            
            if (eventDateInput && dueDateInput) {
                eventDateInput.addEventListener('change', function() {
                    dueDateInput.min = this.value;
                    if (dueDateInput.value < this.value) {
                        dueDateInput.value = this.value;
                    }
                });
            }
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
        default: return '';
    }
}
?>