<?php
// my_grades.php - FACULTY GRADES MANAGEMENT (CONSISTENT WITH DASHBOARD DESIGN)
// ==================== SESSION FIXES ====================
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);
session_set_cookie_params(7200);
session_start();
// ==================== END SESSION FIXES ====================

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Session check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'faculty') {
    header("Location: faculty_login.php");
    exit();
}

// ==================== DATABASE CLASS (SAME AS DASHBOARD) ====================
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

// ==================== DYNAMIC THEME & LANGUAGE SETUP ====================

// GET FACULTY SETTINGS (THEME & LANGUAGE)
function getFacultySettings($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        try {
            $query = "SELECT theme_color, language FROM faculty_settings WHERE faculty_id = :faculty_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':faculty_id', $faculty_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'theme_color' => $result['theme_color'] ?? 'maroon',
                    'language' => $result['language'] ?? 'en'
                ];
            }
        } catch(PDOException $e) {
            error_log("Settings error: " . $e->getMessage());
        }
    }
    return ['theme_color' => 'maroon', 'language' => 'en'];
}

// INITIALIZE THEME & LANGUAGE
$faculty_id = $_SESSION['username'] ?? null;
$faculty_settings = getFacultySettings($faculty_id);

// Set theme and language in session
if (!isset($_SESSION['theme_color'])) {
    $_SESSION['theme_color'] = $faculty_settings['theme_color'];
}
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = $faculty_settings['language'];
}

$current_theme = $_SESSION['theme_color'] ?? 'maroon';
$current_language = $_SESSION['language'] ?? 'en';

// THEME COLOR FUNCTION
function getThemeColor($theme, $type = 'primary') {
    $colors = [
        'maroon' => ['primary' => '#800000', 'secondary' => '#5a0000', 'light' => '#a30000'],
        'blue' => ['primary' => '#007bff', 'secondary' => '#0056b3', 'light' => '#3399ff'],
        'green' => ['primary' => '#28a745', 'secondary' => '#1e7e34', 'light' => '#34ce57'],
        'purple' => ['primary' => '#6f42c1', 'secondary' => '#5a36a8', 'light' => '#8768d6'],
        'dark' => ['primary' => '#343a40', 'secondary' => '#23272b', 'light' => '#495057']
    ];
    
    return $colors[$theme][$type] ?? $colors['maroon'][$type];
}

// TRANSLATION FUNCTION
function translate($key) {
    $current_lang = $_SESSION['language'] ?? 'en';
    
    $translations = [
        'en' => [
            'faculty_profile' => 'Faculty Profile',
            'my_profile' => 'My Profile',
            'dashboard' => 'Dashboard',
            'my_classes' => 'My Classes',
            'grade_management' => 'Grade Management',
            'assignments' => 'Assignments',
            'analytics_reports' => 'Analytics & Reports',
            'settings' => 'Settings',
            'logout' => 'Logout',
            'personal_info' => 'Personal Information',
            'contact_info' => 'Contact Information',
            'address_info' => 'Address Information',
            'read_only' => 'Read Only',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'gender' => 'Gender',
            'birth_date' => 'Birth Date',
            'birth_place' => 'Birth Place',
            'blood_type' => 'Blood Type',
            'civil_status' => 'Civil Status',
            'religion' => 'Religion',
            'personal_email' => 'Personal Email',
            'school_email' => 'School Email',
            'mobile_number' => 'Mobile Number',
            'department' => 'Department',
            'position' => 'Position',
            'street' => 'Street',
            'barangay' => 'Barangay',
            'municipality' => 'City/Municipality',
            'province' => 'Province',
            'zip_code' => 'ZIP Code',
            'upload_profile_picture' => 'Upload Profile Picture',
            'update_profile_picture' => 'Update Profile Picture',
            'file_requirements' => 'File Requirements',
            'max_file_size' => 'Max file size: 2MB',
            'allowed_types' => 'Allowed types: JPG, JPEG, PNG, GIF, WEBP',
            'selected_file' => 'Selected File',
            'size' => 'Size',
            'type' => 'Type',
            'uploading' => 'Uploading...',
            'note_cannot_edit' => 'Note: Personal information cannot be edited. Only profile picture can be updated.',
            'select_class' => 'Select Class',
            'choose_class' => '-- Choose Class --',
            'view' => 'View',
            'print_gradesheet' => 'Print Gradesheet',
            'manage_student_grades' => 'Manage student grades using 1.00-5.00 scale',
            'class_average' => 'Class Average',
            'total_students' => 'Total Students',
            'graded_students' => 'Graded Students',
            'passed_students' => 'Passed (≤3.00)',
            'failed_students' => 'Failed (>3.00)',
            'grades_entry' => 'Grades Entry (1.00-5.00 Scale)',
            'save_all_changes' => 'Save All Changes',
            'reset_all' => 'Reset All',
            'no_students_enrolled' => 'No Students Enrolled',
            'no_students_message' => 'No students have enrolled in this class yet.',
            'grade_legend' => 'Grade Legend',
            'excellent_range' => '1.00-1.49 (Excellent)',
            'good_range' => '1.50-1.99 (Good)',
            'passed_range' => '2.00-2.49 (Passed)',
            'failed_range' => '2.50-5.00 (Failed)',
            'no_grade' => 'No Grade',
            'excellent' => 'Excellent',
            'good' => 'Good',
            'passed' => 'Passed',
            'failed' => 'Failed',
            'incomplete' => 'Incomplete',
            'student_id' => 'Student ID',
            'name' => 'Name',
            'course_year' => 'Course/Year',
            'prelim_30' => 'Prelim (30%)',
            'midterm_30' => 'Midterm (30%)',
            'final_40' => 'Final (40%)',
            'overall_grade' => 'Overall Grade',
            'remarks' => 'Remarks',
            'year' => 'Year'
        ],
        'fil' => [
            'faculty_profile' => 'Profile ng Faculty',
            'my_profile' => 'Aking Profile',
            'dashboard' => 'Dashboard',
            'my_classes' => 'Aking mga Klase',
            'grade_management' => 'Pamamahala ng Marka',
            'assignments' => 'Mga Gawain',
            'analytics_reports' => 'Analytics & Mga Ulat',
            'settings' => 'Mga Setting',
            'logout' => 'Logout',
            'personal_info' => 'Personal na Impormasyon',
            'contact_info' => 'Impormasyon sa Pakikipag-ugnayan',
            'address_info' => 'Impormasyon ng Address',
            'read_only' => 'Read Only',
            'first_name' => 'Pangalan',
            'last_name' => 'Apelyido',
            'gender' => 'Kasarian',
            'birth_date' => 'Petsa ng Kapanganakan',
            'birth_place' => 'Lugar ng Kapanganakan',
            'blood_type' => 'Uri ng Dugo',
            'civil_status' => 'Katayuang Sibil',
            'religion' => 'Relihiyon',
            'personal_email' => 'Personal na Email',
            'school_email' => 'School Email',
            'mobile_number' => 'Numero ng Cellphone',
            'department' => 'Departamento',
            'position' => 'Posisyon',
            'street' => 'Kalye',
            'barangay' => 'Barangay',
            'municipality' => 'Lungsod/Munisipyo',
            'province' => 'Lalawigan',
            'zip_code' => 'ZIP Code',
            'upload_profile_picture' => 'Mag-upload ng Profile Picture',
            'update_profile_picture' => 'I-update ang Profile Picture',
            'file_requirements' => 'Mga Requirement ng File',
            'max_file_size' => 'Max na laki ng file: 2MB',
            'allowed_types' => 'Mga allowed na tipo: JPG, JPEG, PNG, GIF, WEBP',
            'selected_file' => 'Napiling File',
            'size' => 'Laki',
            'type' => 'Tipo',
            'uploading' => 'Nag-u-upload...',
            'note_cannot_edit' => 'Paalala: Hindi maaaring i-edit ang personal na impormasyon. Profile picture lang ang pwedeng i-update.',
            'select_class' => 'Pumili ng Klase',
            'choose_class' => '-- Pumili ng Klase --',
            'view' => 'Tingnan',
            'print_gradesheet' => 'I-print ang Gradesheet',
            'manage_student_grades' => 'Pamahalaan ang marka ng mga estudyante gamit ang 1.00-5.00 scale',
            'class_average' => 'Average ng Klase',
            'total_students' => 'Kabuuang Estudyante',
            'graded_students' => 'May Markang Estudyante',
            'passed_students' => 'Pumasa (≤3.00)',
            'failed_students' => 'Bagsak (>3.00)',
            'grades_entry' => 'Pagpasok ng Marka (1.00-5.00 Scale)',
            'save_all_changes' => 'I-save ang Lahat ng Pagbabago',
            'reset_all' => 'I-reset ang Lahat',
            'no_students_enrolled' => 'Walang Naka-enrol na Estudyante',
            'no_students_message' => 'Wala pang estudyante ang nag-enroll sa klase na ito.',
            'grade_legend' => 'Legend ng Marka',
            'excellent_range' => '1.00-1.49 (Magaling)',
            'good_range' => '1.50-1.99 (Mabuti)',
            'passed_range' => '2.00-2.49 (Pasa)',
            'failed_range' => '2.50-5.00 (Bagsak)',
            'no_grade' => 'Walang Marka',
            'excellent' => 'Magaling',
            'good' => 'Mabuti',
            'passed' => 'Pasa',
            'failed' => 'Bagsak',
            'incomplete' => 'Hindi Kumpleto',
            'student_id' => 'ID ng Estudyante',
            'name' => 'Pangalan',
            'course_year' => 'Kurso/Taon',
            'prelim_30' => 'Prelim (30%)',
            'midterm_30' => 'Midterm (30%)',
            'final_40' => 'Final (40%)',
            'overall_grade' => 'Pangkalahatang Marka',
            'remarks' => 'Remarks',
            'year' => 'Taon'
        ]
    ];
    
    return $translations[$current_lang][$key] ?? $translations['en'][$key] ?? $key;
}

// ==================== FACULTY DATA FUNCTIONS ====================
function getFacultyData($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT * FROM faculty WHERE faculty_id = :faculty_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

// Function to get faculty classes for dropdown (SAME AS YOUR CODE)
function getFacultyClasses($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    c.class_id,
                    s.subject_code,
                    c.section,
                    s.subject_name as descriptive_title,
                    (SELECT COUNT(DISTINCT e.student_id) 
                     FROM enrollments e 
                     WHERE e.class_id = c.class_id) as enrolled_students
                  FROM classes c
                  JOIN subjects s ON c.subject_id = s.subject_id
                  WHERE c.faculty_id = :faculty_id
                  ORDER BY s.subject_code, c.section";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to get class details (SAME AS YOUR CODE)
function getClassDetails($class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    c.class_id,
                    s.subject_code,
                    c.section,
                    s.subject_name,
                    s.units,
                    CONCAT(f.first_name, ' ', f.last_name) as instructor_name,
                    (SELECT COUNT(DISTINCT e.student_id) 
                     FROM enrollments e 
                     WHERE e.class_id = c.class_id) as total_students
                  FROM classes c
                  JOIN subjects s ON c.subject_id = s.subject_id
                  JOIN faculty f ON c.faculty_id = f.faculty_id
                  WHERE c.class_id = :class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

// Function to get students with grades for a class (SAME AS YOUR CODE)
function getClassGrades($class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    s.student_id,
                    s.last_name,
                    s.first_name,
                    s.middle_name,
                    s.course,
                    s.year_level,
                    g.prelim_grade,
                    g.midterm_grade,
                    g.final_grade,
                    g.overall_grade,
                    g.remarks,
                    g.grade_id
                  FROM enrollments e
                  JOIN students s ON e.student_id = s.student_id
                  LEFT JOIN grades g ON e.student_id = g.student_id AND g.class_id = :class_id
                  WHERE e.class_id = :class_id2
                  ORDER BY s.last_name, s.first_name";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':class_id2', $class_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// Function to save/update grade (1.00-5.00 scale) (SAME AS YOUR CODE)
function saveGrade($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        // Get grades (1.00-5.00 scale)
        $prelim = !empty($data['prelim_grade']) ? floatval($data['prelim_grade']) : null;
        $midterm = !empty($data['midterm_grade']) ? floatval($data['midterm_grade']) : null;
        $final = !empty($data['final_grade']) ? floatval($data['final_grade']) : null;
        
        $overall_grade = null;
        if ($prelim !== null && $midterm !== null && $final !== null) {
            // Compute overall grade (30% prelim, 30% midterm, 40% final)
            $overall_grade = ($prelim * 0.3) + ($midterm * 0.3) + ($final * 0.4);
            $overall_grade = round($overall_grade, 2);
            
            // Validate range (1.00 - 5.00)
            if ($overall_grade < 1.00) $overall_grade = 1.00;
            if ($overall_grade > 5.00) $overall_grade = 5.00;
        }
        
        // Determine remarks based on 1.00-5.00 scale
        // 1.00-3.00 = Passed, 3.01-5.00 = Failed
        $remarks = 'Incomplete';
        if ($overall_grade !== null) {
            $remarks = ($overall_grade <= 3.00) ? 'Passed' : 'Failed';
        }
        
        // Check if grade exists
        $checkQuery = "SELECT grade_id FROM grades 
                      WHERE student_id = :student_id AND class_id = :class_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':student_id', $data['student_id']);
        $checkStmt->bindParam(':class_id', $data['class_id']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing grade
            $query = "UPDATE grades SET 
                      prelim_grade = :prelim,
                      midterm_grade = :midterm,
                      final_grade = :final,
                      overall_grade = :overall,
                      remarks = :remarks,
                      grade_date = NOW()
                      WHERE student_id = :student_id AND class_id = :class_id";
        } else {
            // Get class and subject details for new record
            $classQuery = "SELECT 
                           s.subject_code, 
                           s.subject_name,
                           CONCAT(f.first_name, ' ', f.last_name) as instructor
                           FROM classes c
                           JOIN subjects s ON c.subject_id = s.subject_id
                           JOIN faculty f ON c.faculty_id = f.faculty_id
                           WHERE c.class_id = :class_id";
            $classStmt = $db->prepare($classQuery);
            $classStmt->bindParam(':class_id', $data['class_id']);
            $classStmt->execute();
            $classInfo = $classStmt->fetch(PDO::FETCH_ASSOC);
            
            // Insert new grade
            $query = "INSERT INTO grades 
                     (student_id, class_id, course_code, subject_name, instructor, 
                      prelim_grade, midterm_grade, final_grade, overall_grade, 
                      remarks, grade_date, created_at)
                      VALUES 
                     (:student_id, :class_id, :course_code, :subject_name, :instructor,
                      :prelim, :midterm, :final, :overall, :remarks, NOW(), NOW())";
        }
        
        $stmt = $db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':class_id', $data['class_id']);
        
        if ($checkStmt->rowCount() > 0) {
            $stmt->bindParam(':prelim', $prelim);
            $stmt->bindParam(':midterm', $midterm);
            $stmt->bindParam(':final', $final);
            $stmt->bindParam(':overall', $overall_grade);
            $stmt->bindParam(':remarks', $remarks);
        } else {
            $stmt->bindParam(':course_code', $classInfo['subject_code']);
            $stmt->bindParam(':subject_name', $classInfo['subject_name']);
            $stmt->bindParam(':instructor', $classInfo['instructor']);
            $stmt->bindParam(':prelim', $prelim);
            $stmt->bindParam(':midterm', $midterm);
            $stmt->bindParam(':final', $final);
            $stmt->bindParam(':overall', $overall_grade);
            $stmt->bindParam(':remarks', $remarks);
        }
        
        return $stmt->execute();
    }
    return false;
}

// Function to calculate class statistics (1.00-5.00 scale) (SAME AS YOUR CODE)
function getClassStatistics($class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    COUNT(*) as total_students,
                    SUM(CASE WHEN g.overall_grade IS NOT NULL THEN 1 ELSE 0 END) as graded_students,
                    SUM(CASE WHEN g.remarks = 'Passed' THEN 1 ELSE 0 END) as passed_students,
                    SUM(CASE WHEN g.remarks = 'Failed' THEN 1 ELSE 0 END) as failed_students,
                    SUM(CASE WHEN g.remarks = 'Incomplete' OR g.remarks IS NULL THEN 1 ELSE 0 END) as incomplete_students,
                    AVG(g.overall_grade) as class_average
                  FROM enrollments e
                  LEFT JOIN grades g ON e.student_id = g.student_id AND g.class_id = :class_id
                  WHERE e.class_id = :class_id2";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':class_id2', $class_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

// Function to get grade distribution (1.00-5.00 scale) (SAME AS YOUR CODE)
function getGradeDistribution($class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    COUNT(CASE WHEN overall_grade BETWEEN 1.00 AND 1.49 THEN 1 END) as range_1_00_1_49,
                    COUNT(CASE WHEN overall_grade BETWEEN 1.50 AND 1.99 THEN 1 END) as range_1_50_1_99,
                    COUNT(CASE WHEN overall_grade BETWEEN 2.00 AND 2.49 THEN 1 END) as range_2_00_2_49,
                    COUNT(CASE WHEN overall_grade BETWEEN 2.50 AND 3.00 THEN 1 END) as range_2_50_3_00,
                    COUNT(CASE WHEN overall_grade BETWEEN 3.01 AND 5.00 THEN 1 END) as range_3_01_5_00,
                    COUNT(CASE WHEN overall_grade IS NULL THEN 1 END) as no_grade
                  FROM enrollments e
                  LEFT JOIN grades g ON e.student_id = g.student_id AND g.class_id = :class_id
                  WHERE e.class_id = :class_id2";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':class_id2', $class_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

// Process form submissions (SAME AS YOUR CODE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_grade'])) {
        $grade_data = [
            'student_id' => $_POST['student_id'],
            'class_id' => $_POST['class_id'],
            'prelim_grade' => $_POST['prelim_grade'] ?? null,
            'midterm_grade' => $_POST['midterm_grade'] ?? null,
            'final_grade' => $_POST['final_grade'] ?? null
        ];
        
        if (saveGrade($grade_data)) {
            $_SESSION['success_message'] = ($current_language == 'fil') ? "Matagumpay na na-save ang marka!" : "Grade saved successfully!";
        } else {
            $_SESSION['error_message'] = ($current_language == 'fil') ? "Error sa pagsave ng marka." : "Error saving grade.";
        }
        
        // Redirect to same page with class_id
        header("Location: my_grades.php?class_id=" . $_POST['class_id']);
        exit();
    }
    
    if (isset($_POST['save_all_grades'])) {
        $saved_count = 0;
        $class_id = $_POST['class_id'];
        
        if (isset($_POST['grades']) && is_array($_POST['grades'])) {
            foreach ($_POST['grades'] as $student_id => $grades) {
                $grade_data = [
                    'student_id' => $student_id,
                    'class_id' => $class_id,
                    'prelim_grade' => $grades['prelim'] ?? null,
                    'midterm_grade' => $grades['midterm'] ?? null,
                    'final_grade' => $grades['final'] ?? null
                ];
                
                if (saveGrade($grade_data)) {
                    $saved_count++;
                }
            }
            
            $_SESSION['success_message'] = ($current_language == 'fil') 
                ? "Na-save ang marka para sa $saved_count na estudyante!" 
                : "Saved grades for $saved_count students!";
            header("Location: my_grades.php?class_id=" . $class_id);
            exit();
        }
    }
}

// ==================== SET SESSION USER INFO ====================
$faculty_id = $_SESSION['username'] ?? null;
$faculty_data = getFacultyData($faculty_id);

if ($faculty_data) {
    $_SESSION['user_info'] = [
        'name' => ($faculty_data['first_name'] ?? '') . ' ' . ($faculty_data['last_name'] ?? ''),
        'email' => $faculty_data['email'] ?? '',
        'department' => $faculty_data['department'] ?? '',
        'position' => $faculty_data['position'] ?? '',
        'profile_picture' => $faculty_data['profile_picture'] ?? null,
        'first_name' => $faculty_data['first_name'] ?? '',
        'last_name' => $faculty_data['last_name'] ?? ''
    ];
} else {
    $_SESSION['user_info'] = [
        'name' => 'Faculty Member',
        'email' => 'faculty@msubuug.edu.ph',
        'department' => 'College of Information Technology',
        'position' => 'Professor',
        'first_name' => '',
        'last_name' => ''
    ];
}

$faculty_info = $_SESSION['user_info'];
$classes = getFacultyClasses($faculty_id);

// Get selected class
$selected_class_id = $_GET['class_id'] ?? null;
$class_details = null;
$class_grades = [];
$class_stats = [];
$grade_distribution = [];

if ($selected_class_id) {
    $class_details = getClassDetails($selected_class_id);
    $class_grades = getClassGrades($selected_class_id);
    $class_stats = getClassStatistics($selected_class_id);
    $grade_distribution = getGradeDistribution($selected_class_id);
}

// Check messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Helper functions (SAME AS FACULTY_PROFILE)
function getDisplayName($info) {
    if (!$info) return 'Faculty';
    $name = trim(($info['first_name'] ?? '') . ' ' . ($info['last_name'] ?? ''));
    return !empty($name) ? htmlspecialchars($name) : 'Faculty';
}

function getFirstLetter($info) {
    if (!$info) return 'F';
    $name = $info['first_name'] ?? '';
    return !empty($name) ? strtoupper(substr($name, 0, 1)) : 'F';
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('grade_management'); ?> - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ==================== SAME STYLES AS FACULTY_PROFILE ==================== */
        :root {
            --maroon: <?php echo getThemeColor($current_theme, 'primary'); ?>;
            --maroon-dark: <?php echo getThemeColor($current_theme, 'secondary'); ?>;
            --maroon-light: <?php echo getThemeColor($current_theme, 'light'); ?>;
            --gold: #FFD700;
            --light-bg: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 12px 0;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            background: linear-gradient(45deg, #FFD700, #FFED4E);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--maroon-dark) 0%, var(--maroon) 100%);
            min-height: 100vh;
            position: fixed;
            width: 280px;
            top: 70px;
            left: 0;
            bottom: 0;
            z-index: 1020;
            overflow-y: auto;
            box-shadow: 5px 0 25px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
            width: calc(100% - 280px);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 18px 25px;
            border-left: 4px solid transparent;
            margin: 8px 15px;
            border-radius: 12px;
            transition: all 0.4s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(255,215,0,0.15) 0%, rgba(255,255,255,0.1) 100%);
            color: var(--gold);
            border-left-color: var(--gold);
            transform: translateX(8px) scale(1.02);
            box-shadow: 0 5px 20px rgba(255,215,0,0.2);
        }
        
        .sidebar .nav-link i {
            font-size: 1.2rem;
            width: 30px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover i {
            transform: scale(1.2);
        }
        
        /* DASHBOARD CARD STYLES (SAME AS FACULTY_PROFILE) */
        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.4s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 25px;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        /* USER AVATAR (SAME AS FACULTY_PROFILE) */
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3rem;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, var(--gold), #ffed4e);
            color: var(--maroon);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.1);
            border-color: var(--gold);
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
            font-weight: bold;
        }
        
        /* GRADES TABLE SPECIFIC STYLES */
        .grades-table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .grades-table thead {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
        }
        
        .grades-table th {
            color: white;
            padding: 20px 15px;
            font-weight: 600;
            text-align: left;
            border: none;
            font-size: 0.95rem;
        }
        
        .grades-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: middle;
        }
        
        .grades-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .grades-table tbody tr:hover {
            background: rgba(128, 0, 0, 0.03);
            transform: translateY(-2px);
        }
        
        .grade-input {
            width: 80px;
            padding: 8px 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .grade-input:focus {
            border-color: var(--maroon);
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
            outline: none;
            background: #fffaf5;
        }
        
        /* Remove spinner buttons for number input */
        .grade-input::-webkit-outer-spin-button,
        .grade-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .grade-input[type=number] {
            -moz-appearance: textfield;
        }
        
        .grade-display {
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 8px;
            text-align: center;
            min-width: 80px;
            display: inline-block;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .grade-excellent {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .grade-good {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
            border: 2px solid #bee5eb;
        }
        
        .grade-passed {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        .grade-failed {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .grade-incomplete {
            background: linear-gradient(135deg, #e2e3e5, #d6d8db);
            color: #383d41;
            border: 2px solid #d6d8db;
        }
        
        .badge-remarks {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        
        .badge-passed {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .badge-failed {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
        }
        
        .badge-incomplete {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
        }
        
        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
            border-top: 4px solid var(--maroon);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--maroon);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        /* QUICK EDIT BUTTONS */
        .quick-edit-btn {
            font-size: 0.75rem;
            padding: 3px 8px;
            margin: 2px;
            cursor: pointer;
            background: rgba(128, 0, 0, 0.1);
            border: 1px solid rgba(128, 0, 0, 0.2);
            border-radius: 4px;
            color: var(--maroon);
            transition: all 0.2s ease;
        }
        
        .quick-edit-btn:hover {
            background: var(--maroon);
            color: white;
            transform: scale(1.05);
        }
        
        /* CLASS INFO HEADER */
        .class-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(128, 0, 0, 0.2);
        }
        
        .class-header h4 {
            color: var(--gold);
            margin-bottom: 10px;
        }
        
        /* LEGEND */
        .legend-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }
        
        .legend-item {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
            font-size: 0.85rem;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-right: 8px;
            border: 2px solid rgba(255,255,255,0.5);
        }
        
        /* ALERTS (SAME AS FACULTY_PROFILE) */
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #28a745;
            color: #155724;
            border-radius: 12px;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 2px solid #dc3545;
            color: #721c24;
            border-radius: 12px;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.3rem;
        }
        
        /* RESPONSIVE DESIGN (SAME AS FACULTY_PROFILE) */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.4s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .grades-table {
                font-size: 0.9rem;
            }
            
            .grade-input {
                width: 70px;
                padding: 6px 8px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .grades-table {
                display: block;
                overflow-x: auto;
            }
            
            .grade-input {
                width: 65px;
                font-size: 0.85rem;
            }
            
            .class-header {
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .legend-item {
                display: block;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar (SAME AS FACULTY_PROFILE) -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="faculty_dashboard.php">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                MSU BUUG - Faculty Portal
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="me-3 text-white d-none d-md-block text-end">
                    <div><strong><?php echo htmlspecialchars($faculty_info['name']); ?></strong></div>
                    <small><?php echo htmlspecialchars($faculty_info['position']); ?></small>
                </div>
                <div class="dropdown">
                    <a class="text-white text-decoration-none d-flex align-items-center" href="#" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- PROFILE PICTURE IN NAVIGATION -->
                        <div class="user-avatar me-2">
                            <?php 
                            $profile_pic_path = $faculty_data['profile_picture'] ?? null;
                            $first_letter = getFirstLetter($faculty_info);
                            ?>
                            
                            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" 
                                      alt="Profile">
                            <?php else: ?>
                                <div class="letter-avatar d-flex align-items-center justify-content-center w-100 h-100">
                                    <?php echo $first_letter; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-chevron-down fa-xs ms-1"></i>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="faculty_profile.php"><i class="fas fa-user-circle me-2 text-maroon"></i><?php echo translate('my_profile'); ?></a></li>
   
                        <li><a class="dropdown-item" href="faculty_settings.php" class="fas fa-cog me-2 text-maroon"></i><?php echo translate('settings'); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="logout_faculty.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i><?php echo translate('logout'); ?>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar (SAME AS FACULTY_PROFILE) -->
    <div class="sidebar" id="sidebar">
        <div class="mobile-user-info d-lg-none p-4 border-bottom">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3">
                    <?php 
                    $profile_pic_path = $faculty_data['profile_picture'] ?? null;
                    if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile">
                    <?php else: ?>
                        <div class="letter-avatar d-flex align-items-center justify-content-center w-100 h-100">
                            <?php echo $first_letter; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <strong class="text-white"><?php echo htmlspecialchars($faculty_info['name']); ?></strong><br>
                    <small class="text-gold"><?php echo htmlspecialchars($faculty_info['position']); ?></small>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column pt-4">
            <a href="faculty_dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt me-3"></i> <?php echo translate('dashboard'); ?>
            </a>
            <a href="my_classes.php" class="nav-link">
                <i class="fas fa-book me-3"></i> <?php echo translate('my_classes'); ?>
            </a>
            <a href="my_grades.php" class="nav-link active">
                <i class="fas fa-chart-line me-3"></i> <?php echo translate('grade_management'); ?>
            </a>
            <a href="my_assignments.php" class="nav-link">
                <i class="fas fa-tasks me-3"></i> <?php echo translate('assignments'); ?>
            </a>
            <a href="faculty_analytics.php" class="nav-link">
                <i class="fas fa-chart-bar me-3"></i> <?php echo translate('analytics_reports'); ?>
            </a>
            <a href="notifications.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> <?php echo translate('settings'); ?>
                </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Success/Error Messages -->
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

        <!-- PAGE HEADER -->
        <div class="dashboard-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-edit me-2"></i><?php echo translate('grade_management'); ?>
                    <small class="d-block mt-1" style="opacity: 0.9;"><?php echo translate('manage_student_grades'); ?></small>
                </div>
                <div class="no-print">
                    <button class="btn btn-sm" onclick="window.print()" style="
                        background: var(--gold);
                        color: var(--maroon);
                        border: none;
                        padding: 8px 20px;
                        border-radius: 8px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-print me-1"></i> <?php echo translate('print_gradesheet'); ?>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- CLASS SELECTOR -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-bold text-maroon"><?php echo translate('select_class'); ?>:</label>
                        <form method="GET" action="my_grades.php" class="d-flex gap-2">
                            <select name="class_id" class="form-select" onchange="this.form.submit()" required style="
                                border: 2px solid #e0e0e0;
                                border-radius: 10px;
                                padding: 12px 15px;
                                font-weight: 500;
                            ">
                                <option value=""><?php echo translate('choose_class'); ?></option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>" 
                                        <?php echo ($selected_class_id == $class['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['subject_code'] . ' - Section ' . $class['section'] . ' (' . $class['descriptive_title'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn" style="
                                background: var(--maroon);
                                color: white;
                                border: none;
                                padding: 0 25px;
                                border-radius: 10px;
                                font-weight: 600;
                                transition: all 0.3s ease;
                            ">
                                <i class="fas fa-eye me-1"></i> <?php echo translate('view'); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <?php if ($selected_class_id && $class_details): ?>
                <!-- CLASS INFORMATION -->
                <div class="class-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="fas fa-chalkboard-teacher me-2"></i>
                                <?php echo htmlspecialchars($class_details['subject_code']); ?> - Section <?php echo htmlspecialchars($class_details['section']); ?>
                            </h4>
                            <p class="mb-1"><?php echo htmlspecialchars($class_details['subject_name']); ?></p>
                            <p class="mb-0 opacity-90">
                                <i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($class_details['instructor_name']); ?> • 
                                <i class="fas fa-users me-1"></i> <?php echo $class_details['total_students']; ?> <?php echo translate('total_students'); ?> • 
                                <i class="fas fa-book me-1"></i> <?php echo $class_details['units']; ?> Units
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column align-items-end">
                                <div class="fs-1 fw-bold text-gold">
                                    <?php 
                                    if ($class_stats['class_average']) {
                                        $average = floatval($class_stats['class_average']);
                                        echo number_format($average, 2);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </div>
                                <div class="small"><?php echo translate('class_average'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STATISTICS -->
                <div class="stats-grid mb-4">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $class_stats['total_students'] ?? 0; ?></div>
                        <div class="stat-label"><?php echo translate('total_students'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $class_stats['graded_students'] ?? 0; ?></div>
                        <div class="stat-label"><?php echo translate('graded_students'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" style="color: #28a745;"><?php echo $class_stats['passed_students'] ?? 0; ?></div>
                        <div class="stat-label"><?php echo translate('passed_students'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" style="color: #dc3545;"><?php echo $class_stats['failed_students'] ?? 0; ?></div>
                        <div class="stat-label"><?php echo translate('failed_students'); ?></div>
                    </div>
                </div>

                <!-- GRADES TABLE -->
                <div class="grades-table-container">
                    <div class="p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-maroon">
                                <i class="fas fa-list-ol me-2"></i><?php echo translate('grades_entry'); ?>
                            </h5>
                            <div class="no-print">
                                <form id="saveAllForm" method="POST" style="display: inline;">
                                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                    <input type="hidden" name="save_all_grades" value="1">
                                    <button type="submit" class="btn btn-success btn-sm px-4 py-2" style="
                                        border-radius: 8px;
                                        font-weight: 600;
                                    ">
                                        <i class="fas fa-save me-1"></i> <?php echo translate('save_all_changes'); ?>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-maroon btn-sm ms-2 px-4 py-2" onclick="resetAllGrades()" style="
                                    border-radius: 8px;
                                    font-weight: 600;
                                ">
                                    <i class="fas fa-undo me-1"></i> <?php echo translate('reset_all'); ?>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted"><?php echo translate('manage_student_grades'); ?></small>
                    </div>
                    
                    <div class="table-responsive">
                        <form id="gradesForm">
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th width="10%"><?php echo translate('student_id'); ?></th>
                                    <th width="20%"><?php echo translate('name'); ?></th>
                                    <th width="15%"><?php echo translate('course_year'); ?></th>
                                    <th width="12%"><?php echo translate('prelim_30'); ?></th>
                                    <th width="12%"><?php echo translate('midterm_30'); ?></th>
                                    <th width="12%"><?php echo translate('final_40'); ?></th>
                                    <th width="12%"><?php echo translate('overall_grade'); ?></th>
                                    <th width="7%"><?php echo translate('remarks'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($class_grades) > 0): ?>
                                    <?php foreach ($class_grades as $index => $student): 
                                        $prelim = $student['prelim_grade'] ?? '';
                                        $midterm = $student['midterm_grade'] ?? '';
                                        $final = $student['final_grade'] ?? '';
                                        $overall = $student['overall_grade'] ?? '';
                                        $remarks = $student['remarks'] ?? translate('incomplete');
                                        
                                        // Determine CSS class for remarks
                                        $remarks_class = 'badge-incomplete';
                                        if ($remarks == translate('passed')) $remarks_class = 'badge-passed';
                                        if ($remarks == translate('failed')) $remarks_class = 'badge-failed';
                                        
                                        // Determine CSS class for overall grade display
                                        $overall_class = 'grade-incomplete';
                                        if ($overall !== '' && $overall !== null) {
                                            $overall = floatval($overall);
                                            if ($overall <= 1.49) {
                                                $overall_class = 'grade-excellent';
                                            } elseif ($overall <= 1.99) {
                                                $overall_class = 'grade-good';
                                            } elseif ($overall <= 3.00) {
                                                $overall_class = 'grade-passed';
                                            } else {
                                                $overall_class = 'grade-failed';
                                            }
                                        }
                                    ?>
                                    <tr data-student-id="<?php echo $student['student_id']; ?>">
                                        <td><strong class="text-maroon"><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></strong>
                                            <?php if (!empty($student['middle_name'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($student['middle_name']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($student['course']); ?><br>
                                            <span class="badge bg-secondary"><?php echo $student['year_level']; ?> <?php echo translate('year'); ?></span>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="grade-input prelim-grade" 
                                                   name="grades[<?php echo $student['student_id']; ?>][prelim]" 
                                                   value="<?php 
                                                        if ($prelim !== '' && $prelim !== null) {
                                                            echo number_format(floatval($prelim), 2);
                                                        }
                                                   ?>" 
                                                   placeholder="1.00"
                                                   data-student="<?php echo $student['student_id']; ?>"
                                                   onblur="formatGradeInput(this); calculateGrade('<?php echo $student['student_id']; ?>')"
                                                   onkeypress="return isNumericInput(event)"
                                                   maxlength="4">
                                            <div class="mt-1">
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '1.00')">1.00</small>
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '2.00')">2.00</small>
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '3.00')">3.00</small>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="grade-input midterm-grade" 
                                                   name="grades[<?php echo $student['student_id']; ?>][midterm]" 
                                                   value="<?php 
                                                        if ($midterm !== '' && $midterm !== null) {
                                                            echo number_format(floatval($midterm), 2);
                                                        }
                                                   ?>" 
                                                   placeholder="1.00"
                                                   data-student="<?php echo $student['student_id']; ?>"
                                                   onblur="formatGradeInput(this); calculateGrade('<?php echo $student['student_id']; ?>')"
                                                   onkeypress="return isNumericInput(event)"
                                                   maxlength="4">
                                            <div class="mt-1">
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '1.00')">1.00</small>
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '2.00')">2.00</small>
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '3.00')">3.00</small>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="grade-input final-grade" 
                                                   name="grades[<?php echo $student['student_id']; ?>][final]" 
                                                   value="<?php 
                                                        if ($final !== '' && $final !== null) {
                                                            echo number_format(floatval($final), 2);
                                                        }
                                                   ?>" 
                                                   placeholder="1.00"
                                                   data-student="<?php echo $student['student_id']; ?>"
                                                   onblur="formatGradeInput(this); calculateGrade('<?php echo $student['student_id']; ?>')"
                                                   onkeypress="return isNumericInput(event)"
                                                   maxlength="4">
                                            <div class="mt-1">
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '1.00')">1.00</small>
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '2.00')">2.00</small>
                                                <small class="quick-edit-btn" onclick="quickSetGrade(this, '3.00')">3.00</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div id="overall_<?php echo $student['student_id']; ?>" 
                                                 class="grade-display <?php echo $overall_class; ?>">
                                                <?php 
                                                if ($overall !== '' && $overall !== null) {
                                                    echo number_format($overall, 2);
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div id="remarks_<?php echo $student['student_id']; ?>" 
                                                 class="badge-remarks <?php echo $remarks_class; ?>">
                                                <?php echo $remarks; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                                            <h4 class="text-muted"><?php echo translate('no_students_enrolled'); ?></h4>
                                            <p class="text-muted"><?php echo translate('no_students_message'); ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        </form>
                    </div>
                    
                    <?php if (count($class_grades) > 0): ?>
                    <div class="p-4 border-top bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="legend-container">
                                    <strong class="text-maroon mb-2 d-block"><?php echo translate('grade_legend'); ?>:</strong>
                                    <div>
                                        <span class="legend-item"><span class="legend-color" style="background-color: #d4edda;"></span> <?php echo translate('excellent_range'); ?></span>
                                        <span class="legend-item"><span class="legend-color" style="background-color: #d1ecf1;"></span> <?php echo translate('good_range'); ?></span>
                                        <span class="legend-item"><span class="legend-color" style="background-color: #fff3cd;"></span> <?php echo translate('passed_range'); ?></span>
                                        <span class="legend-item"><span class="legend-color" style="background-color: #f8d7da;"></span> <?php echo translate('failed_range'); ?></span>
                                        <span class="legend-item"><span class="legend-color" style="background-color: #e2e3e5;"></span> <?php echo translate('no_grade'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="no-print">
                                    <form id="saveAllFormBottom" method="POST">
                                        <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                        <input type="hidden" name="save_all_grades" value="1">
                                        <button type="submit" class="btn btn-success px-4 py-2" style="
                                            border-radius: 8px;
                                            font-weight: 600;
                                        ">
                                            <i class="fas fa-save me-1"></i> <?php echo translate('save_all_changes'); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Mobile sidebar toggle (SAME AS FACULTY_PROFILE)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 992 && 
                sidebar.classList.contains('active') &&
                !sidebar.contains(event.target) &&
                !mobileToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Function to allow only numeric input with decimal point
        function isNumericInput(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            var value = evt.target.value;
            
            // Allow numbers (0-9)
            if (charCode >= 48 && charCode <= 57) {
                return true;
            }
            
            // Allow decimal point (.) but only once
            if (charCode === 46) {
                return value.indexOf('.') === -1;
            }
            
            // Allow backspace, delete, tab, escape, enter
            if (charCode === 8 || charCode === 46 || charCode === 9 || charCode === 27 || charCode === 13) {
                return true;
            }
            
            // Prevent all other keys
            return false;
        }

        // Format grade input on blur
        function formatGradeInput(input) {
            let value = input.value.trim();
            
            if (value === '') {
                return;
            }
            
            // Remove any non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            
            // Ensure only one decimal point
            let parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Parse as float
            let num = parseFloat(value);
            
            if (isNaN(num)) {
                input.value = '';
                return;
            }
            
            // Check range (1.00 - 5.00)
            if (num < 1.00) {
                num = 1.00;
            } else if (num > 5.00) {
                num = 5.00;
            }
            
            // Format to two decimal places
            input.value = num.toFixed(2);
        }

        // Quick set grade buttons
        function quickSetGrade(button, grade) {
            const input = button.parentElement.parentElement.querySelector('.grade-input');
            input.value = grade;
            formatGradeInput(input);
            
            const studentId = input.getAttribute('data-student');
            calculateGrade(studentId);
        }

        // Calculate overall grade for a student (1.00-5.00 scale)
        function calculateGrade(studentId) {
            const prelimInput = document.querySelector(`input[name="grades[${studentId}][prelim]"]`);
            const midtermInput = document.querySelector(`input[name="grades[${studentId}][midterm]"]`);
            const finalInput = document.querySelector(`input[name="grades[${studentId}][final]"]`);
            const overallDiv = document.getElementById(`overall_${studentId}`);
            const remarksDiv = document.getElementById(`remarks_${studentId}`);
            
            const prelim = prelimInput.value !== '' ? parseFloat(prelimInput.value) : null;
            const midterm = midtermInput.value !== '' ? parseFloat(midtermInput.value) : null;
            const final = finalInput.value !== '' ? parseFloat(finalInput.value) : null;
            
            let overall = null;
            let remarks = '<?php echo translate("incomplete"); ?>';
            let overallClass = 'grade-incomplete';
            let remarksClass = 'badge-incomplete';
            
            // Check if all grades are provided
            if (prelim !== null && midterm !== null && final !== null) {
                // Compute overall grade (30% prelim, 30% midterm, 40% final)
                overall = (prelim * 0.3) + (midterm * 0.3) + (final * 0.4);
                overall = Math.round(overall * 100) / 100; // Round to 2 decimal places
                
                // Ensure grade is within 1.00-5.00 range
                if (overall < 1.00) overall = 1.00;
                if (overall > 5.00) overall = 5.00;
                
                // Determine remarks based on 1.00-5.00 scale
                if (overall <= 3.00) {
                    remarks = '<?php echo translate("passed"); ?>';
                    remarksClass = 'badge-passed';
                } else {
                    remarks = '<?php echo translate("failed"); ?>';
                    remarksClass = 'badge-failed';
                }
                
                // Determine color for overall grade display
                if (overall <= 1.49) {
                    overallClass = 'grade-excellent';
                } else if (overall <= 1.99) {
                    overallClass = 'grade-good';
                } else if (overall <= 3.00) {
                    overallClass = 'grade-passed';
                } else {
                    overallClass = 'grade-failed';
                }
            }
            
            // Update display
            overallDiv.textContent = overall !== null ? overall.toFixed(2) : 'N/A';
            overallDiv.className = `grade-display ${overallClass}`;
            
            remarksDiv.textContent = remarks;
            remarksDiv.className = `badge-remarks ${remarksClass}`;
            
            // Auto-save after calculation
            autoSaveGrade(studentId);
        }

        // Auto-save grade via AJAX
        function autoSaveGrade(studentId) {
            const prelimInput = document.querySelector(`input[name="grades[${studentId}][prelim]"]`);
            const midtermInput = document.querySelector(`input[name="grades[${studentId}][midterm]"]`);
            const finalInput = document.querySelector(`input[name="grades[${studentId}][final]"]`);
            
            const formData = new FormData();
            formData.append('save_grade', '1');
            formData.append('student_id', studentId);
            formData.append('class_id', '<?php echo $selected_class_id; ?>');
            formData.append('prelim_grade', prelimInput.value);
            formData.append('midterm_grade', midtermInput.value);
            formData.append('final_grade', finalInput.value);
            
            // Show saving status
            const saveBtn = document.querySelector('#saveAllForm button');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> <?php echo translate("uploading"); ?>';
            }
            
            fetch('my_grades.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-check me-1"></i> <?php echo translate("save_all_changes"); ?>';
                    saveBtn.classList.add('btn-success');
                    
                    // Revert after 3 seconds
                    setTimeout(() => {
                        saveBtn.classList.remove('btn-success');
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> <?php echo $current_language == 'fil' ? 'Error sa Pagsave' : 'Error Saving'; ?>';
                    saveBtn.classList.add('btn-danger');
                    
                    setTimeout(() => {
                        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> <?php echo translate("save_all_changes"); ?>';
                        saveBtn.classList.remove('btn-danger');
                    }, 3000);
                }
            });
        }

        // Reset all grades in the form
        function resetAllGrades() {
            if (confirm('<?php echo $current_language == 'fil' ? 'Sigurado ka bang gusto mong i-reset ang lahat ng marka? Mabubura ang lahat ng hindi pa na-save na pagbabago.' : 'Are you sure you want to reset all grades? This will clear all unsaved changes.'; ?>')) {
                const gradeInputs = document.querySelectorAll('.grade-input');
                gradeInputs.forEach(input => {
                    input.value = '';
                });
                
                // Recalculate all grades
                <?php if (count($class_grades) > 0): ?>
                    <?php foreach ($class_grades as $student): ?>
                        calculateGrade('<?php echo $student['student_id']; ?>');
                    <?php endforeach; ?>
                <?php endif; ?>
            }
        }

        // Add event listeners for auto-calculation
        document.addEventListener('DOMContentLoaded', function() {
            const gradeInputs = document.querySelectorAll('.grade-input');
            gradeInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    const studentId = this.getAttribute('data-student');
                    calculateGrade(studentId);
                });
                
                // Allow Enter key to save and move to next field
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const studentId = this.getAttribute('data-student');
                        formatGradeInput(this);
                        calculateGrade(studentId);
                        
                        // Move to next input in same row
                        const inputs = Array.from(document.querySelectorAll(`input[data-student="${studentId}"]`));
                        const currentIndex = inputs.indexOf(this);
                        if (currentIndex < inputs.length - 1) {
                            inputs[currentIndex + 1].focus();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>