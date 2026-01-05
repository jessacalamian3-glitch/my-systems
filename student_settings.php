<?php
// student_settings.php - STUDENT SETTINGS PAGE
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// SIMPLIFIED SESSION CHECK
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_student.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login_student.php");
    exit();
}

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

// Function to get student data
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

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['username'] ?? '';
    
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "UPDATE students SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone WHERE student_id = :student_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':student_id', $student_id);
            
            if ($stmt->execute()) {
                $message = "Profile updated successfully!";
                $message_type = "success";
                
                // Update session data
                $_SESSION['user_info']['name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_info']['email'] = $email;
            } else {
                $message = "Error updating profile. Please try again.";
                $message_type = "danger";
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($new_password !== $confirm_password) {
            $message = "New passwords do not match!";
            $message_type = "danger";
        } else {
            // Verify current password and update
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                // First verify current password
                $query = "SELECT password FROM students WHERE student_id = :student_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':student_id', $student_id);
                $stmt->execute();
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($student && password_verify($current_password, $student['password'])) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE students SET password = :password WHERE student_id = :student_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':student_id', $student_id);
                    
                    if ($stmt->execute()) {
                        $message = "Password changed successfully!";
                        $message_type = "success";
                    } else {
                        $message = "Error changing password. Please try again.";
                        $message_type = "danger";
                    }
                } else {
                    $message = "Current password is incorrect!";
                    $message_type = "danger";
                }
            }
        }
    }
}

// Fetch student data
$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);

if ($student_data) {
    $_SESSION['user_info'] = [
        'name' => $student_data['first_name'] . ' ' . $student_data['last_name'],
        'course' => $student_data['course'],
        'year_level' => $student_data['year_level'],
        'email' => $student_data['email']
    ];
} else {
    // Fallback to session data if no database record
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - MSU Buug Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* MSU MAROON COLORS - DIRECT DECLARATION */
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
        
        /* ===== MOBILE FIRST STYLES ===== */
        
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
        }
        
        /* CARDS */
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 4px solid #800000;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
            padding: 1.5rem;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        /* WELCOME BANNER */
        .welcome-banner {
            background: linear-gradient(135deg, #800000, #5a0000);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 2px solid #FFD700;
        }
        
        /* BUTTONS */
        .btn-msu {
            background: #800000;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .btn-msu:hover {
            background: #a30000;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128,0,0,0.3);
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

        .btn-success-sm {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .btn-success-sm:hover {
            background: #218838;
            color: white;
            transform: translateY(-1px);
        }
        
        /* STATS CARDS */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border-top: 4px solid #800000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #800000;
            margin: 10px 0;
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
        
        /* USER INFO */
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #800000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            border: 2px solid #FFD700;
        }
        
        /* TABLES */
        .table-msu thead {
            background: #800000;
            color: white;
        }
        
        .badge-msu {
            background: #800000;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        
        .text-msu {
            color: #800000 !important;
        }
        
        /* PROGRESS BARS */
        .progress {
            height: 8px;
            border-radius: 10px;
            background: #e9ecef;
        }
        
        .progress-bar {
            background: #800000;
            border-radius: 10px;
        }
        
        /* COURSE CARDS */
        .course-card {
            border-left: 4px solid #800000;
            transition: all 0.3s ease;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .course-card:hover {
            border-left-color: #FFD700;
        }

        /* STATUS BADGES */
        .badge-enrolled {
            background: #28a745;
            color: white;
        }
        
        .badge-available {
            background: #ffc107;
            color: black;
        }

        /* MOBILE OPTIMIZATIONS */
        .mobile-user-info {
            display: none;
            background: #5a0000;
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* SETTINGS SPECIFIC STYLES */
        .settings-section {
            margin-bottom: 2rem;
        }
        
        .settings-header {
            border-bottom: 2px solid #800000;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
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
            
            .welcome-banner {
                padding: 30px;
            }
            
            .stats-number {
                font-size: 2.5rem;
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
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
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
                        <div class="user-avatar me-2">
                            <?php 
                                echo isset($student_info['name']) ? strtoupper(substr(explode(' ', $student_info['name'])[0], 0, 1)) : 'S';
                            ?>
                        </div>
                        <div class="d-none d-md-block text-white">
                            <strong><?php echo htmlspecialchars($student_info['name'] ?? 'Student'); ?></strong><br>
                            <small>Student</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="student_profile.php"><i class="fas fa-user me-2 text-msu"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="student_settings.php"><i class="fas fa-cog me-2 text-msu"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="student_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Layout -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Mobile User Info -->
            <div class="mobile-user-info d-lg-none">
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">
                        <?php 
                            echo isset($student_info['name']) ? strtoupper(substr(explode(' ', $student_info['name'])[0], 0, 1)) : 'S';
                        ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($student_info['name'] ?? 'Student'); ?></strong><br>
                        <small>Student ID: <?php echo htmlspecialchars($student_id); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="d-flex flex-column pt-3">
                <a href="student-dashboard.php" class="nav-link">
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
                <a href="student_resources.php" class="nav-link">
                    <i class="fas fa-file-alt"></i> Resources
                </a>
                <a href="student_support.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
                <a href="student_settings.php" class="nav-link active">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="h4">Account Settings <i class="fas fa-cog"></i></h2>
                        <p class="mb-1">Manage your profile and account preferences</p>
                        <small>Student ID: <?php echo htmlspecialchars($student_id); ?></small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="icon-circle">
                            <i class="fas fa-user-cog"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Alert -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Profile Settings -->
                    <div class="dashboard-card settings-section">
                        <div class="settings-header">
                            <h4 class="text-msu mb-0"><i class="fas fa-user-edit me-2"></i>Profile Settings</h4>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($student_data['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($student_data['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($student_data['email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($student_data['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Student ID</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student_id); ?>" readonly>
                                <small class="text-muted">Student ID cannot be changed</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Course & Year Level</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student_data['course'] ?? 'Not Set'); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student_data['year_level'] ?? 'Not Set'); ?>" readonly>
                                    </div>
                                </div>
                                <small class="text-muted">Course and year level changes require administrative approval</small>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-msu">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
                    </div>

                    <!-- Password Settings -->
                    <div class="dashboard-card settings-section">
                        <div class="settings-header">
                            <h4 class="text-msu mb-0"><i class="fas fa-lock me-2"></i>Change Password</h4>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle me-2"></i>
                                    Password must be at least 8 characters long and include uppercase, lowercase, and numbers.
                                </small>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-msu">
                                <i class="fas fa-key me-2"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Account Info -->
                    <div class="dashboard-card">
                        <div class="settings-header">
                            <h4 class="text-msu mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h4>
                        </div>
                        
                        <div class="text-center mb-4">
                            <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?php echo strtoupper(substr(explode(' ', $student_info['name'])[0], 0, 1)); ?>
                            </div>
                            <h5 class="text-msu"><?php echo htmlspecialchars($student_info['name']); ?></h5>
                            <p class="text-muted">Student</p>
                        </div>
                        
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Student ID:</span>
                                <strong class="text-msu"><?php echo htmlspecialchars($student_id); ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Course:</span>
                                <strong><?php echo htmlspecialchars($student_data['course'] ?? 'Not Set'); ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Year Level:</span>
                                <strong><?php echo htmlspecialchars($student_data['year_level'] ?? 'Not Set'); ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Status:</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="dashboard-card">
                        <div class="settings-header">
                            <h4 class="text-msu mb-0"><i class="fas fa-link me-2"></i>Quick Links</h4>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="student_profile.php" class="btn btn-outline-msu">
                                <i class="fas fa-user me-2"></i>View Profile
                            </a>
                            <a href="student-dashboard.php" class="btn btn-outline-msu">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a href="student_support.php" class="btn btn-outline-msu">
                                <i class="fas fa-question-circle me-2"></i>Help & Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            
            // Prevent body scroll when sidebar is open
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

        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords do not match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            newPassword.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
        });
    </script>
</body>
</html>