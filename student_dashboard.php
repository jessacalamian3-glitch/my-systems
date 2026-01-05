<?php
// student_profile.php - COMPLETE VERSION WITH ALL DATABASE FIELDS

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

// Check if user is logged in - SAME AS DASHBOARD
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['username'] ?? 'N/A';
$student_data = getStudentData($student_id);
$success_message = '';
$error_message = '';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    try {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/students/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['profile_picture']['size'] <= 2097152) {
                    // Delete old profile picture if exists
                    $old_picture = $student_data['profile_picture'] ?? null;
                    if ($old_picture && file_exists($old_picture)) {
                        unlink($old_picture);
                    }
                    
                    // Generate new filename
                    $file_name = 'student_' . $student_id . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                        // Update database
                        $database = new Database();
                        $db = $database->getConnection();
                        
                        if ($db) {
                            $stmt = $db->prepare("UPDATE students SET profile_picture = ? WHERE student_id = ?");
                            $result = $stmt->execute([$file_path, $student_id]);
                            
                            if ($result) {
                                $success_message = "Profile picture updated successfully!";
                                // Refresh student data
                                $student_data = getStudentData($student_id);
                            } else {
                                $error_message = "Failed to update profile picture in database.";
                            }
                        }
                    } else {
                        $error_message = "Failed to move uploaded file.";
                    }
                } else {
                    $error_message = "File is too large. Maximum size is 2MB.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
            }
        } else {
            $error_message = "Please select a valid image file.";
        }
        
    } catch(PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

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

// Helper functions
function getDisplayName($info) {
    if (!$info) return 'Student';
    return htmlspecialchars($info['name'] ?? 'Student');
}

function getFirstLetter($info) {
    if (!$info) return 'S';
    $name = $info['name'] ?? '';
    return !empty($name) ? strtoupper(substr($name, 0, 1)) : 'S';
}

function getValue($data, $key, $default = 'Not set') {
    if (!$data || !isset($data[$key]) || empty($data[$key])) return $default;
    return htmlspecialchars($data[$key]);
}

function getStudentField($student_data, $field, $default = 'Not set') {
    if (!$student_data || !isset($student_data[$field]) || $student_data[$field] === '' || $student_data[$field] === null) {
        return $default;
    }
    return htmlspecialchars($student_data[$field]);
}

function formatDate($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return 'Not set';
    return date('F j, Y', strtotime($date));
}

function getYearLevelText($level) {
    if (empty($level)) return 'Not set';
    $levels = ['1' => '1st Year', '2' => '2nd Year', '3' => '3rd Year', '4' => '4th Year'];
    return $levels[$level] ?? $level . ' Year';
}

// Function to display all database fields
function displayAllFields($student_data) {
    // Define all possible fields from the database
    $fields = [
        // Personal Information
        'first_name' => 'First Name',
        'middle_name' => 'Middle Name',
        'last_name' => 'Last Name',
        'suffix' => 'Suffix',
        'birth_date' => 'Birth Date',
        'birth_place' => 'Birth Place',
        'gender' => 'Gender',
        'civil_status' => 'Civil Status',
        'religion' => 'Religion',
        'tribe' => 'Tribe',
        'blood_type' => 'Blood Type',
        
        // Contact Information
        'phone' => 'Phone Number',
        'email' => 'Email Address',
        
        // Address Information
        'street' => 'Street Address',
        'barangay' => 'Barangay',
        'municipality' => 'City/Municipality',
        'province' => 'Province',
        'zip_code' => 'ZIP Code',
        
        // Guardian Information
        'guardian' => 'Guardian\'s Name',
        'guardian_number' => 'Guardian\'s Contact',
        'guardian_address' => 'Guardian\'s Address',
        
        // Educational Background
        'elementary_school' => 'Elementary School',
        'elementary_graduated' => 'Elementary Year Graduated',
        'secondary_school' => 'Secondary School',
        'secondary_graduated' => 'Secondary Year Graduated',
        'senior_high_school' => 'Senior High School',
        'senior_high_graduated' => 'Senior High Year Graduated',
        'last_school_attended' => 'Last School Attended',
        
        // Academic Information
        'student_id' => 'Student ID',
        'course' => 'Course/Program',
        'year_level' => 'Year Level',
        'section' => 'Section',
        'status' => 'Enrollment Status',
        'enrollment_date' => 'Enrollment Date',
        
        // System Information
        'created_at' => 'Account Created',
        'updated_at' => 'Last Updated'
    ];
    
    // Group fields by category
    $categories = [
        'Personal Information' => [
            'first_name', 'middle_name', 'last_name', 'suffix', 'birth_date', 
            'birth_place', 'gender', 'civil_status', 'religion', 'tribe', 'blood_type'
        ],
        'Contact Information' => [
            'phone', 'email'
        ],
        'Address Information' => [
            'street', 'barangay', 'municipality', 'province', 'zip_code'
        ],
        'Guardian Information' => [
            'guardian', 'guardian_number', 'guardian_address'
        ],
        'Educational Background' => [
            'elementary_school', 'elementary_graduated', 'secondary_school', 
            'secondary_graduated', 'senior_high_school', 'senior_high_graduated',
            'last_school_attended'
        ],
        'Academic Information' => [
            'student_id', 'course', 'year_level', 'section', 'status', 'enrollment_date'
        ],
        'System Information' => [
            'created_at', 'updated_at'
        ]
    ];
    
    $html = '';
    
    foreach ($categories as $category => $fieldList) {
        $hasData = false;
        $categoryHtml = '';
        
        foreach ($fieldList as $field) {
            if (isset($fields[$field])) {
                $label = $fields[$field];
                $value = '';
                
                // Special handling for date fields
                if (strpos($field, '_date') !== false || strpos($field, '_graduated') !== false || 
                    strpos($field, '_at') !== false) {
                    $value = formatDate($student_data[$field] ?? '');
                } 
                // Special handling for year level
                else if ($field === 'year_level') {
                    $value = getYearLevelText($student_data[$field] ?? '');
                }
                // Regular fields
                else {
                    $value = getStudentField($student_data, $field);
                }
                
                // Check if this field has data
                if ($value !== 'Not set') {
                    $hasData = true;
                }
                
                $categoryHtml .= '
                <div class="info-item">
                    <div class="info-label">' . $label . '</div>
                    <div class="info-value">' . $value . '</div>
                </div>';
            }
        }
        
        // Only show the category if it has data or if it's Academic Information
        if ($hasData || $category === 'Academic Information' || $category === 'Personal Information') {
            $html .= '
            <div class="dashboard-card mb-4">
                <h4 class="section-title">
                    <i class="fas ' . getCategoryIcon($category) . ' me-2"></i>' . $category . '
                </h4>
                <div class="info-grid">
                    ' . $categoryHtml . '
                </div>
            </div>';
        }
    }
    
    return $html;
}

function getCategoryIcon($category) {
    $icons = [
        'Personal Information' => 'fa-user-circle',
        'Contact Information' => 'fa-address-book',
        'Address Information' => 'fa-home',
        'Guardian Information' => 'fa-users',
        'Educational Background' => 'fa-graduation-cap',
        'Academic Information' => 'fa-university',
        'System Information' => 'fa-database'
    ];
    
    return $icons[$category] ?? 'fa-info-circle';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* EXACT SAME STYLES AS DASHBOARD */
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
        
        /* CARDS - SAME AS DASHBOARD BUT ALL WHITE FOR PROFILE */
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
        
        /* PROFILE HEADER CARD - ALL WHITE */
        .profile-header-card {
            background: white;
            color: #333;
            border-radius: 10px;
            padding: 30px 20px;
            margin-bottom: 25px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #800000;
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
        
        /* PROFILE PICTURE CONTAINER - PERFECT CIRCLE */
        .profile-picture-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 5px solid #800000;
            padding: 0;
            transition: all 0.3s ease;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .profile-picture-container:hover {
            transform: scale(1.05);
            border-color: #FFD700;
            box-shadow: 0 8px 25px rgba(128,0,0,0.3);
        }
        
        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .profile-upload-btn {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #800000;
            border: 3px solid white;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .profile-upload-btn:hover {
            transform: scale(1.1);
            background: #FFD700;
            color: #800000;
            border-color: #800000;
        }
        
        .section-title {
            border-left: 4px solid #800000;
            padding-left: 15px;
            margin: 25px 0 20px 0;
            color: #800000;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #800000;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            border-left-color: #FFD700;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: #800000;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #333;
            font-size: 0.95rem;
        }
        
        .mobile-user-info {
            display: none;
            background: #5a0000;
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
            
            .profile-header-card {
                padding: 40px 30px;
            }
            
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
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
            
            .profile-header-card {
                padding: 50px 40px;
            }
        }

        /* ===== EXTRA SMALL DEVICES (phones, 480px and down) ===== */
        @media (max-width: 480px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .profile-header-card {
                padding: 20px 15px;
                margin-bottom: 20px;
            }
            
            .dashboard-card {
                padding: 1rem;
                margin-bottom: 15px;
            }
            
            .profile-picture-container {
                width: 150px;
                height: 150px;
            }
            
            .profile-upload-btn {
                width: 40px;
                height: 40px;
                font-size: 1rem;
                bottom: 10px;
                right: 10px;
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
                        <li><a class="dropdown-item active" href="student_profile.php"><i class="fas fa-user me-2 text-msu"></i>My Profile</a></li>
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
            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Header Card - ALL WHITE BACKGROUND -->
            <div class="profile-header-card">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <!-- Profile Picture Container - PERFECT CIRCLE -->
                        <div class="profile-picture-container">
                            <?php 
                            $profile_pic = $student_data['profile_picture'] ?? null;
                            $first_letter = getFirstLetter($student_info);
                            ?>
                            
                            <?php if ($profile_pic && file_exists($profile_pic)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
                                     alt="Profile Picture" 
                                     class="profile-picture" 
                                     id="profileImage">
                            <?php else: ?>
                                <div class="profile-picture d-flex align-items-center justify-content-center bg-light text-maroon fw-bold fs-1" 
                                     style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);" 
                                     id="profileImage">
                                    <?php echo $first_letter; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data" id="profileForm" class="d-inline-block">
                                <input type="file" id="profilePictureInput" name="profile_picture" 
                                       accept="image/*" style="display: none;" 
                                       onchange="handleFileSelect(this)">
                                
                                <button type="button" class="profile-upload-btn" onclick="document.getElementById('profilePictureInput').click()">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <h2 class="h4 mb-2 text-msu"><?php echo getDisplayName($student_info); ?></h2>
                        <p class="mb-1 text-muted">
                            <i class="fas fa-graduation-cap me-2 text-msu"></i>
                            <?php echo getStudentField($student_data, 'course'); ?>
                        </p>
                        <p class="mb-3 text-muted">
                            <i class="fas fa-calendar-alt me-2 text-msu"></i>
                            <?php echo getYearLevelText($student_data['year_level'] ?? ''); ?>
                        </p>
                        <p class="mb-3 text-muted">
                            <i class="fas fa-id-card me-2 text-msu"></i>
                            Student ID: <?php echo htmlspecialchars($student_id); ?>
                        </p>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-msu-sm" onclick="document.getElementById('profilePictureInput').click()">
                                <i class="fas fa-camera me-2"></i>Update Profile Picture
                            </button>
                        </div>
                        
                        <small class="form-text d-block mt-2 text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Max file size: 2MB. Allowed types: JPG, JPEG, PNG, GIF, WEBP
                        </small>
                        <div id="fileInfo" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Display ALL database fields -->
            <?php echo displayAllFields($student_data); ?>
        </div>
    </div>

    <!-- JavaScript - SAME AS DASHBOARD -->
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

        // Set active nav link based on current page
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

        // Profile picture upload function
        function handleFileSelect(input) {
            const fileInfo = document.getElementById('fileInfo');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Display file information
                fileInfo.innerHTML = `
                    <div class="alert alert-info">
                        <strong>Selected File:</strong> ${file.name}<br>
                        <strong>Size:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
                        <strong>Type:</strong> ${file.type}
                        <div class="spinner-border spinner-border-sm text-maroon mt-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Uploading...</span>
                    </div>
                `;
                
                // Validate file size
                if (file.size > 2097152) {
                    fileInfo.innerHTML = `
                        <div class="alert alert-danger">
                            File is too large! Maximum size is 2MB.
                        </div>
                    `;
                    input.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    fileInfo.innerHTML = `
                        <div class="alert alert-danger">
                            Invalid file type! Only JPG, JPEG, PNG, GIF, and WEBP are allowed.
                        </div>
                    `;
                    input.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const profileImage = document.getElementById('profileImage');
                    if (profileImage && profileImage.tagName === 'IMG') {
                        profileImage.src = e.target.result;
                    } else if (profileImage && profileImage.tagName === 'DIV') {
                        // Replace div with img
                        profileImage.outerHTML = `<img src="${e.target.result}" 
                                                      alt="Profile Picture" 
                                                      class="profile-picture" 
                                                      id="profileImage">`;
                    }
                    
                    // Auto-submit the form
                    setTimeout(function() {
                        document.getElementById('profileForm').submit();
                    }, 1000);
                };
                reader.readAsDataURL(file);
            } else {
                fileInfo.innerHTML = '';
            }
        }
    </script>
</body>
</html>