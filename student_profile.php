<?php
// student_profile.php - WITH EDIT FUNCTIONALITY FOR DYNAMIC FIELDS + PASSWORD CHANGE

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

// Function to verify password
function verifyPassword($db, $student_id, $current_password) {
    $query = "SELECT password FROM students WHERE student_id = :student_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && password_verify($current_password, $result['password'])) {
        return true;
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
$edit_mode = false;
$show_password_form = false;
$editable_fields_updated = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile picture upload
    if (isset($_FILES['profile_picture'])) {
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
    
    // Handle editable fields update
    if (isset($_POST['update_profile'])) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                // Define editable fields (dynamic fields only)
                $editable_fields = [
                    'middle_name', 'suffix',
                    'phone', 'email',
                    'street', 'barangay', 'municipality', 'province', 'zip_code',
                    'civil_status', 'religion', 'tribe', 'blood_type',
                    'guardian', 'guardian_number', 'guardian_address',
                    'elementary_school', 'elementary_graduated',
                    'secondary_school', 'secondary_graduated',
                    'senior_high_school', 'senior_high_graduated',
                    'last_school_attended'
                ];
                
                $update_fields = [];
                $update_values = [];
                
                foreach ($editable_fields as $field) {
                    if (isset($_POST[$field])) {
                        $update_fields[] = "$field = ?";
                        $update_values[] = trim($_POST[$field]);
                    }
                }
                
                if (!empty($update_fields)) {
                    $update_values[] = $student_id; // For WHERE clause
                    
                    $query = "UPDATE students SET " . implode(', ', $update_fields) . ", updated_at = NOW() WHERE student_id = ?";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute($update_values);
                    
                    if ($result) {
                        $success_message = "Profile updated successfully!";
                        $editable_fields_updated = true;
                        // Refresh student data
                        $student_data = getStudentData($student_id);
                        $edit_mode = false;
                    } else {
                        $error_message = "Failed to update profile information.";
                    }
                }
            }
        } catch(PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Please fill in all password fields.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error_message = "New password must be at least 8 characters long.";
        } else {
            try {
                $database = new Database();
                $db = $database->getConnection();
                
                if ($db) {
                    // Verify current password
                    if (verifyPassword($db, $student_id, $current_password)) {
                        // Hash new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        // Update password
                        $stmt = $db->prepare("UPDATE students SET password = ?, updated_at = NOW() WHERE student_id = ?");
                        $result = $stmt->execute([$hashed_password, $student_id]);
                        
                        if ($result) {
                            $success_message = "Password changed successfully!";
                            $show_password_form = false;
                        } else {
                            $error_message = "Failed to update password.";
                        }
                    } else {
                        $error_message = "Current password is incorrect.";
                    }
                }
            } catch(PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Handle cancel edit
    if (isset($_POST['cancel_edit'])) {
        $edit_mode = false;
        $show_password_form = false;
    }
    
    // Handle show password form
    if (isset($_POST['show_password_form'])) {
        $show_password_form = true;
    }
}

// Check if edit mode is requested
if (isset($_GET['edit']) && $_GET['edit'] == '1') {
    $edit_mode = true;
}

// Check if show password form is requested
if (isset($_GET['change_password']) && $_GET['change_password'] == '1') {
    $show_password_form = true;
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

// Function to display profile information (view mode)
function displayProfileInfo($student_data, $edit_mode = false) {
    $categories = [
        'Personal Information' => [
            'first_name' => ['label' => 'First Name', 'editable' => false],
            'middle_name' => ['label' => 'Middle Name', 'editable' => true],
            'last_name' => ['label' => 'Last Name', 'editable' => false],
            'suffix' => ['label' => 'Suffix', 'editable' => true],
            'birth_date' => ['label' => 'Birth Date', 'editable' => false, 'type' => 'date'],
            'birth_place' => ['label' => 'Birth Place', 'editable' => false],
            'gender' => ['label' => 'Gender', 'editable' => false],
            'civil_status' => ['label' => 'Civil Status', 'editable' => true, 'options' => ['Single', 'Married', 'Separated', 'Widowed']],
            'religion' => ['label' => 'Religion', 'editable' => true],
            'tribe' => ['label' => 'Tribe', 'editable' => true],
            'blood_type' => ['label' => 'Blood Type', 'editable' => true, 'options' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']],
        ],
        'Contact Information' => [
            'phone' => ['label' => 'Phone Number', 'editable' => true, 'type' => 'tel'],
            'email' => ['label' => 'Email Address', 'editable' => true, 'type' => 'email'],
        ],
        'Address Information' => [
            'street' => ['label' => 'Street Address', 'editable' => true],
            'barangay' => ['label' => 'Barangay', 'editable' => true],
            'municipality' => ['label' => 'City/Municipality', 'editable' => true],
            'province' => ['label' => 'Province', 'editable' => true],
            'zip_code' => ['label' => 'ZIP Code', 'editable' => true],
        ],
        'Guardian Information' => [
            'guardian' => ['label' => 'Guardian\'s Name', 'editable' => true],
            'guardian_number' => ['label' => 'Guardian\'s Contact', 'editable' => true],
            'guardian_address' => ['label' => 'Guardian\'s Address', 'editable' => true],
        ],
        'Educational Background' => [
            'elementary_school' => ['label' => 'Elementary School', 'editable' => true],
            'elementary_graduated' => ['label' => 'Elementary Year Graduated', 'editable' => true, 'type' => 'year'],
            'secondary_school' => ['label' => 'Secondary School', 'editable' => true],
            'secondary_graduated' => ['label' => 'Secondary Year Graduated', 'editable' => true, 'type' => 'year'],
            'senior_high_school' => ['label' => 'Senior High School', 'editable' => true],
            'senior_high_graduated' => ['label' => 'Senior High Year Graduated', 'editable' => true, 'type' => 'year'],
            'last_school_attended' => ['label' => 'Last School Attended', 'editable' => true],
        ],
        'Academic Information' => [
            'student_id' => ['label' => 'Student ID', 'editable' => false],
            'course' => ['label' => 'Course/Program', 'editable' => false],
            'year_level' => ['label' => 'Year Level', 'editable' => false],
            'section' => ['label' => 'Section', 'editable' => false],
            'status' => ['label' => 'Enrollment Status', 'editable' => false],
            'enrollment_date' => ['label' => 'Enrollment Date', 'editable' => false, 'type' => 'date'],
        ],
    ];
    
    $html = '';
    
    foreach ($categories as $category => $fields) {
        $hasData = false;
        $categoryHtml = '';
        
        foreach ($fields as $field => $fieldInfo) {
            $label = $fieldInfo['label'];
            $editable = $fieldInfo['editable'];
            $value = '';
            
            // Get field value with special formatting
            if (isset($fieldInfo['type'])) {
                switch ($fieldInfo['type']) {
                    case 'date':
                        $value = formatDate($student_data[$field] ?? '');
                        break;
                    case 'year':
                        $value = getStudentField($student_data, $field, 'Not specified');
                        break;
                    default:
                        $value = getStudentField($student_data, $field);
                }
            } else {
                // Special handling for year level
                if ($field === 'year_level') {
                    $value = getYearLevelText($student_data[$field] ?? '');
                } else {
                    $value = getStudentField($student_data, $field);
                }
            }
            
            // Check if this field has data
            if ($value !== 'Not set' && $value !== 'Not specified') {
                $hasData = true;
            }
            
            if ($edit_mode && $editable) {
                // Edit mode - show input field
                $input_html = '';
                if (isset($fieldInfo['options'])) {
                    // Select dropdown
                    $input_html = '<select name="' . $field . '" class="form-control form-control-sm">';
                    $input_html .= '<option value="">Select ' . $label . '</option>';
                    foreach ($fieldInfo['options'] as $option) {
                        $selected = ($student_data[$field] ?? '') == $option ? 'selected' : '';
                        $input_html .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . $option . '</option>';
                    }
                    $input_html .= '</select>';
                } else {
                    // Regular input
                    $input_type = isset($fieldInfo['type']) && in_array($fieldInfo['type'], ['tel', 'email']) ? $fieldInfo['type'] : 'text';
                    $input_html = '<input type="' . $input_type . '" name="' . $field . '" value="' . htmlspecialchars($student_data[$field] ?? '') . '" class="form-control form-control-sm">';
                }
                
                $categoryHtml .= '
                <div class="info-item edit-mode">
                    <div class="info-label">' . $label . '</div>
                    <div class="info-value">
                        ' . $input_html . '
                    </div>
                </div>';
            } else {
                // View mode - show static value
                $display_value = $value;
                if ($value === 'Not set' && $editable) {
                    $display_value = '<span class="text-muted">Click edit to add</span>';
                }
                
                $categoryHtml .= '
                <div class="info-item">
                    <div class="info-label">' . $label . '</div>
                    <div class="info-value">' . $display_value . '</div>
                </div>';
            }
        }
        
        // Only show the category if it has data or if it's Academic Information
        if ($hasData || $category === 'Academic Information' || $category === 'Personal Information') {
            $html .= '
            <div class="profile-section mb-4">
                <div class="section-header">
                    <h5 class="section-title">
                        <i class="fas ' . getCategoryIcon($category) . ' me-2"></i>' . $category . '
                    </h5>
                </div>
                <div class="section-body">
                    <div class="info-grid">
                        ' . $categoryHtml . '
                    </div>
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
    
    <!-- Bootstrap 5 & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* EXACT SAME STYLES AS DASHBOARD FOR SIDEBAR & HEADER */
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
        
        .mobile-user-info {
            display: none;
            background: #5a0000;
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
        }
        
        /* ===== NEW MAIN CONTENT DESIGN ===== */
        .profile-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-left: 5px solid #800000;
        }
        
        .profile-header-content {
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .profile-avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            flex-shrink: 0;
        }
        
        .profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            object-fit: cover;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #800000;
            font-size: 3rem;
            font-weight: bold;
        }
        
        .avatar-upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            background: #800000;
            border: 3px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .avatar-upload-btn:hover {
            background: #FFD700;
            color: #800000;
            transform: scale(1.1);
        }
        
        .profile-info {
            flex: 1;
            min-width: 300px;
        }
        
        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #800000;
            margin-bottom: 5px;
        }
        
        .profile-id {
            color: #666;
            font-size: 0.9rem;
            background: #f8f9fa;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }
        
        .meta-item i {
            color: #800000;
        }
        
        .profile-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* BUTTONS - NEW STYLE */
        .btn-msu {
            background: #800000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-msu:hover {
            background: #a30000;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128,0,0,0.3);
        }
        
        .btn-outline-msu {
            background: transparent;
            color: #800000;
            border: 2px solid #800000;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline-msu:hover {
            background: #800000;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background: #218838;
            color: white;
            transform: translateY(-2px);
        }
        
        /* PROFILE SECTIONS - NEW DESIGN */
        .profile-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        @media (min-width: 1200px) {
            .profile-content {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .profile-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .profile-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .section-header {
            background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
            padding: 20px 25px;
            color: white;
        }
        
        .section-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-body {
            padding: 25px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        @media (min-width: 768px) {
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #800000;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .info-item.edit-mode {
            background: #fff9e6;
        }
        
        .info-label {
            font-weight: 600;
            color: #800000;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #333;
            font-size: 1rem;
        }
        
        .edit-mode .form-control {
            border-color: #800000;
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
        }
        
        /* PASSWORD FORM */
        .password-form-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border-top: 5px solid #28a745;
        }
        
        .password-form-title {
            color: #800000;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #800000;
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .password-requirements {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #800000;
        }
        
        .password-requirements h6 {
            color: #800000;
            margin-bottom: 10px;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        /* SECURITY CARD */
        .security-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border-top: 5px solid #800000;
        }
        
        .security-title {
            color: #800000;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .security-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .security-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .security-info h6 {
            margin: 0 0 5px 0;
            color: #800000;
        }
        
        .security-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .profile-header {
                padding: 20px;
            }
            
            .profile-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-meta {
                justify-content: center;
            }
            
            .profile-actions {
                justify-content: center;
            }
            
            .section-body {
                padding: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .profile-avatar-container {
                width: 120px;
                height: 120px;
            }
            
            .profile-name {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-msu, .btn-outline-msu, .btn-success {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* EXTRA SMALL DEVICES */
        @media (max-width: 480px) {
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
            <a class="navbar-brand nav-brand" href="student_dashboard.php">
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
                        <li><a class="dropdown-item" href="student_dashboard.php"><i class="fas fa-tachometer-alt me-2 text-msu"></i>Dashboard</a></li>
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
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-tasks"></i> My Assignments
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
                <a href="student_profile.php" class="nav-link active">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="student_support.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
            </div>
        </div>

        <!-- Main Content - NEW DESIGN -->
        <div class="main-content">
            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-header-content">
                    <div class="profile-avatar-container">
                        <?php 
                        $profile_pic = $student_data['profile_picture'] ?? null;
                        $first_letter = getFirstLetter($student_info);
                        ?>
                        
                        <?php if ($profile_pic && file_exists($profile_pic)): ?>
                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
                                 alt="Profile Picture" 
                                 class="profile-avatar" 
                                 id="profileImage">
                        <?php else: ?>
                            <div class="profile-avatar" id="profileImage">
                                <?php echo $first_letter; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" id="profileForm" class="d-inline-block">
                            <input type="file" id="profilePictureInput" name="profile_picture" 
                                   accept="image/*" style="display: none;" 
                                   onchange="handleFileSelect(this)">
                            
                            <div class="avatar-upload-btn" onclick="document.getElementById('profilePictureInput').click()">
                                <i class="fas fa-camera"></i>
                            </div>
                        </form>
                    </div>
                    
                    <div class="profile-info">
                        <h1 class="profile-name"><?php echo getDisplayName($student_info); ?></h1>
                        <div class="profile-id">
                            <i class="fas fa-id-card me-1"></i>
                            Student ID: <?php echo htmlspecialchars($student_id); ?>
                        </div>
                        
                        <div class="profile-meta">
                            <div class="meta-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span><?php echo getStudentField($student_data, 'course'); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo getYearLevelText($student_data['year_level'] ?? ''); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo getStudentField($student_data, 'email', 'No email set'); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo getStudentField($student_data, 'phone', 'No phone set'); ?></span>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <?php if (!$edit_mode && !$show_password_form): ?>
                                <a href="?edit=1" class="btn btn-msu">
                                    <i class="fas fa-edit me-1"></i> Edit Profile
                                </a>
                                <a href="?change_password=1" class="btn btn-outline-msu">
                                    <i class="fas fa-key me-1"></i> Change Password
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <small class="form-text d-block mt-3 text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Profile picture: Max 2MB. Allowed: JPG, JPEG, PNG, GIF, WEBP
                        </small>
                        <div id="fileInfo" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Left Column: Profile Information -->
                <div class="left-column">
                    <?php if ($edit_mode): ?>
                        <form method="POST" action="" id="profileEditForm">
                    <?php endif; ?>
                    
                    <?php echo displayProfileInfo($student_data, $edit_mode); ?>
                    
                    <?php if ($edit_mode): ?>
                        <div class="action-buttons">
                            <button type="submit" name="update_profile" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                            <button type="submit" name="cancel_edit" class="btn btn-outline-msu">
                                <i class="fas fa-times me-1"></i> Cancel
                            </button>
                        </div>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Right Column: Security & Password -->
                <div class="right-column">
                    <!-- Security Card -->
                    <div class="security-card mb-4">
                        <h3 class="security-title">
                            <i class="fas fa-shield-alt"></i> Account Security
                        </h3>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h6>Password</h6>
                                <p>Last changed: <?php echo formatDate($student_data['updated_at'] ?? ''); ?></p>
                            </div>
                            <div>
                                <a href="?change_password=1" class="btn btn-outline-msu btn-sm">
                                    <i class="fas fa-key me-1"></i> Change
                                </a>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h6>Email Address</h6>
                                <p><?php echo getStudentField($student_data, 'email', 'Not set'); ?></p>
                            </div>
                            <div>
                                <?php if (!$edit_mode): ?>
                                    <a href="?edit=1#email" class="btn btn-outline-msu btn-sm">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h6>Phone Number</h6>
                                <p><?php echo getStudentField($student_data, 'phone', 'Not set'); ?></p>
                            </div>
                            <div>
                                <?php if (!$edit_mode): ?>
                                    <a href="?edit=1#phone" class="btn btn-outline-msu btn-sm">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Change Form -->
                    <?php if ($show_password_form): ?>
                        <div class="password-form-container">
                            <h3 class="password-form-title">
                                <i class="fas fa-key"></i> Change Password
                            </h3>
                            
                            <form method="POST" action="" id="passwordForm">
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required 
                                           placeholder="Enter your current password">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" required 
                                           placeholder="Enter new password" minlength="8">
                                    <div class="password-strength text-muted">
                                        <small>Password strength: <span id="passwordStrength">Weak</span></small>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required 
                                           placeholder="Confirm new password">
                                </div>
                                
                                <div class="password-requirements">
                                    <h6>Password Requirements:</h6>
                                    <ul>
                                        <li>At least 8 characters long</li>
                                        <li>Include uppercase and lowercase letters</li>
                                        <li>Include at least one number</li>
                                        <li>Include at least one special character</li>
                                    </ul>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" name="change_password" class="btn btn-success">
                                        <i class="fas fa-key me-1"></i> Update Password
                                    </button>
                                    <button type="submit" name="cancel_edit" class="btn btn-outline-msu">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
                                                      class="profile-avatar" 
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

        // Password strength checker
        <?php if ($show_password_form): ?>
        const newPasswordInput = document.getElementById('new_password');
        const passwordStrength = document.getElementById('passwordStrength');
        
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                
                // Character variety checks
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[a-z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                
                // Determine strength level
                let strengthText = 'Very Weak';
                let strengthColor = '#dc3545';
                
                if (strength >= 6) {
                    strengthText = 'Very Strong';
                    strengthColor = '#28a745';
                } else if (strength >= 4) {
                    strengthText = 'Strong';
                    strengthColor = '#17a2b8';
                } else if (strength >= 3) {
                    strengthText = 'Good';
                    strengthColor = '#ffc107';
                } else if (strength >= 2) {
                    strengthText = 'Weak';
                    strengthColor = '#fd7e14';
                }
                
                passwordStrength.textContent = strengthText;
                passwordStrength.style.color = strengthColor;
            });
        }
        
        // Password form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const currentPassword = document.querySelector('input[name="current_password"]').value;
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match.');
                return;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[name="change_password"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
                submitBtn.disabled = true;
            }
        });
        <?php endif; ?>

        // Profile edit form validation
        <?php if ($edit_mode): ?>
        document.getElementById('profileEditForm').addEventListener('submit', function(e) {
            const emailInput = document.querySelector('input[name="email"]');
            const phoneInput = document.querySelector('input[name="phone"]');
            
            if (emailInput && emailInput.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    e.preventDefault();
                    alert('Please enter a valid email address.');
                    emailInput.focus();
                    return;
                }
            }
            
            if (phoneInput && phoneInput.value) {
                const phoneRegex = /^[0-9+\-\s()]{10,}$/;
                if (!phoneRegex.test(phoneInput.value)) {
                    e.preventDefault();
                    alert('Please enter a valid phone number.');
                    phoneInput.focus();
                    return;
                }
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[name="update_profile"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
                submitBtn.disabled = true;
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>