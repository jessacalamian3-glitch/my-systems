<?php
// my_students.php - FACULTY STUDENTS MANAGEMENT WITH LANGUAGE & THEME
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

// ==================== DATABASE CLASS ====================
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

// ==================== GET FACULTY SETTINGS (THEME & LANGUAGE) ====================
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

// ==================== CLASS FUNCTIONS ====================
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
                     WHERE e.class_id = c.class_id) as enrolled_students,
                    COALESCE(
                        (SELECT GROUP_CONCAT(
                            DISTINCT CONCAT(
                                UPPER(SUBSTRING(cs.day, 1, 2)),
                                TIME_FORMAT(cs.start_time, '%h:%i%p'),
                                '-',
                                TIME_FORMAT(cs.end_time, '%h:%i%p')
                            ) 
                            ORDER BY FIELD(cs.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
                            SEPARATOR ' | '
                        )
                        FROM class_schedule cs
                        WHERE cs.class_id = c.class_id),
                        'TBA'
                    ) as schedule,
                    COALESCE(
                        (SELECT GROUP_CONCAT(DISTINCT cs.room ORDER BY cs.day SEPARATOR ' | ')
                         FROM class_schedule cs
                         WHERE cs.class_id = c.class_id),
                        'TBA'
                    ) as rooms
                  FROM classes c
                  JOIN subjects s ON c.subject_id = s.subject_id
                  WHERE c.faculty_id = :faculty_id
                    AND c.status = 'active'
                  GROUP BY c.class_id, s.subject_code, c.section, s.subject_name
                  ORDER BY s.subject_code, c.section";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

function getClassRoster($class_id) {
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
                    e.grade,
                    e.status as enrollment_status,
                    e.remarks
                  FROM enrollments e
                  JOIN students s ON e.student_id = s.student_id
                  WHERE e.class_id = :class_id
                  ORDER BY s.last_name, s.first_name";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

// ==================== STUDENT PROFILE FUNCTIONS ====================
function getStudentProfile($student_id) {
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

function getStudentClassInfo($student_id, $class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    e.grade,
                    e.remarks,
                    e.status as enrollment_status,
                    e.enrollment_date,
                    e.updated_at
                  FROM enrollments e
                  WHERE e.student_id = :student_id 
                    AND e.class_id = :class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

function getClassInfo($class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    c.class_id,
                    s.subject_code,
                    s.subject_name,
                    c.section,
                    c.academic_year,
                    c.semester,
                    s.units
                  FROM classes c
                  JOIN subjects s ON c.subject_id = s.subject_id
                  WHERE c.class_id = :class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

// ==================== MAIN LOGIC ====================
$faculty_id = $_SESSION['username'] ?? null;

// Get faculty settings for theme and language
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

// Get faculty data
$faculty_data = getFacultyData($faculty_id);

if ($faculty_data) {
    $_SESSION['user_info'] = [
        'name' => ($faculty_data['first_name'] ?? '') . ' ' . ($faculty_data['last_name'] ?? ''),
        'email' => $faculty_data['email'] ?? '',
        'department' => $faculty_data['department'] ?? '',
        'position' => $faculty_data['position'] ?? '',
        'profile_picture' => $faculty_data['profile_picture'] ?? null,
        'faculty_id' => $faculty_data['faculty_id']
    ];
} else {
    $_SESSION['user_info'] = [
        'name' => 'Faculty Member',
        'email' => 'faculty@msubuug.edu.ph',
        'department' => 'College of Information Technology',
        'position' => 'Professor',
        'faculty_id' => $faculty_id
    ];
}

$faculty_info = $_SESSION['user_info'];
$classes = getFacultyClasses($faculty_id);

// Get parameters
$selected_class_id = $_GET['class_id'] ?? null;
$selected_student_id = $_GET['student_id'] ?? null;

// Initialize variables
$class_roster = [];
$selected_class = null;
$student_profile = null;
$student_class_info = null;
$class_info = null;

// If class_id is specified, get class roster
if ($selected_class_id) {
    $class_roster = getClassRoster($selected_class_id);
    
    // Find the selected class from classes array
    foreach ($classes as $class) {
        if ($class['class_id'] == $selected_class_id) {
            $selected_class = $class;
            break;
        }
    }
    
    // If student_id is also specified, get student profile
    if ($selected_student_id) {
        $student_profile = getStudentProfile($selected_student_id);
        $student_class_info = getStudentClassInfo($selected_student_id, $selected_class_id);
        $class_info = getClassInfo($selected_class_id);
    }
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==================== THEME COLOR FUNCTION ====================
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

// ==================== TRANSLATION FUNCTION (FOR STUDENT PROFILE ONLY) ====================
function translateStudentProfile($key) {
    $current_lang = $_SESSION['language'] ?? 'en';
    
    $translations = [
        'en' => [
            // Navigation
            'my_students' => 'My Students',
            'dashboard' => 'Dashboard',
            'my_classes' => 'My Classes',
            'grade_management' => 'Grade Management',
            'assignments' => 'Assignments',
            'analytics_reports' => 'Analytics & Reports',
            'settings' => 'Settings',
            'logout' => 'Logout',
            'my_profile' => 'My Profile',
            
            // Student Profile Specific
            'student_profile' => 'Student Profile',
            'personal_information' => 'Personal Information',
            'contact_information' => 'Contact Information',
            'academic_information' => 'Academic Information',
            'family_background' => 'Family Background',
            'emergency_contact' => 'Emergency Contact',
            'class_performance_details' => 'Class Performance Details',
            'full_name' => 'Full Name',
            'birth_date_place' => 'Birth Date & Place',
            'gender' => 'Gender',
            'civil_status_religion' => 'Civil Status & Religion',
            'tribe' => 'Tribe',
            'blood_type' => 'Blood Type',
            'email_address' => 'Email Address',
            'school_email' => 'School Email',
            'phone_number' => 'Phone Number',
            'address' => 'Address',
            'course_program' => 'Course/Program',
            'enrollment_status' => 'Enrollment Status',
            'date_registered' => 'Date Registered',
            'last_login' => 'Last Login',
            'fathers_name' => 'Father\'s Name',
            'mothers_name' => 'Mother\'s Name',
            'guardian' => 'Guardian',
            'guardian_contact' => 'Guardian Contact',
            'emergency_contact_person' => 'Emergency Contact Person',
            'emergency_phone_number' => 'Emergency Phone Number',
            'subject' => 'Subject',
            'section' => 'Section',
            'grade' => 'Grade',
            'remarks' => 'Remarks',
            'enrollment_date' => 'Enrollment Date',
            'back_to_students' => 'Back to Students',
            'all_classes' => 'All Classes'
        ],
        'fil' => [
            // Navigation
            'my_students' => 'Aking mga Mag-aaral',
            'dashboard' => 'Dashboard',
            'my_classes' => 'Aking mga Klase',
            'grade_management' => 'Pamamahala ng Marka',
            'assignments' => 'Mga Gawain',
            'analytics_reports' => 'Analytics & Mga Ulat',
            'settings' => 'Mga Setting',
            'logout' => 'Logout',
            'my_profile' => 'Aking Profile',
            
            // Student Profile Specific
            'student_profile' => 'Profile ng Mag-aaral',
            'personal_information' => 'Personal na Impormasyon',
            'contact_information' => 'Impormasyon sa Pakikipag-ugnayan',
            'academic_information' => 'Impormasyong Akademiko',
            'family_background' => 'Background ng Pamilya',
            'emergency_contact' => 'Emergency Contact',
            'class_performance_details' => 'Detalye ng Performance sa Klase',
            'full_name' => 'Buong Pangalan',
            'birth_date_place' => 'Petsa at Lugar ng Kapanganakan',
            'gender' => 'Kasarian',
            'civil_status_religion' => 'Katayuang Sibil at Relihiyon',
            'tribe' => 'Tribo',
            'blood_type' => 'Uri ng Dugo',
            'email_address' => 'Email Address',
            'school_email' => 'School Email',
            'phone_number' => 'Numero ng Telepono',
            'address' => 'Address',
            'course_program' => 'Kurso/Programa',
            'enrollment_status' => 'Katayuan ng Pag-enrol',
            'date_registered' => 'Petsa ng Pagrehistro',
            'last_login' => 'Huling Pag-login',
            'fathers_name' => 'Pangalan ng Ama',
            'mothers_name' => 'Pangalan ng Ina',
            'guardian' => 'Tagapangalaga',
            'guardian_contact' => 'Contact ng Tagapangalaga',
            'emergency_contact_person' => 'Emergency Contact Person',
            'emergency_phone_number' => 'Emergency Phone Number',
            'subject' => 'Subject',
            'section' => 'Seksyon',
            'grade' => 'Marka',
            'remarks' => 'Mga Puna',
            'enrollment_date' => 'Petsa ng Pag-enrol',
            'back_to_students' => 'Bumalik sa mga Mag-aaral',
            'all_classes' => 'Lahat ng Klase'
        ]
    ];
    
    return $translations[$current_lang][$key] ?? $translations['en'][$key] ?? $key;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
        
        /* DASHBOARD CARD STYLES - EXACT SAME AS FACULTY DASHBOARD */
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
        
        /* PROFILE PICTURE STYLES */
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
        
        /* CLEAN TABLE DESIGN - EXACT SAME AS my_classes.php */
        .clean-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }
        
        .clean-table thead {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
        }
        
        .clean-table th {
            color: white;
            padding: 18px 20px;
            font-weight: 600;
            text-align: left;
            border: none;
            font-size: 0.95rem;
            white-space: nowrap;
        }
        
        .clean-table td {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: top;
            color: #333;
            font-size: 0.95rem;
        }
        
        .clean-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .clean-table tbody tr:hover {
            background: rgba(128,0,0,0.03);
        }
        
        /* SUBJECT CODE COLUMN */
        .subject-code {
            color: var(--maroon);
            font-weight: 700;
            font-size: 1.1rem;
            white-space: nowrap;
        }
        
        .section-badge {
            display: inline-block;
            background: rgba(128,0,0,0.1);
            color: var(--maroon);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 8px;
        }
        
        /* SCHEDULE COLUMN */
        .schedule-display {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
        }
        
        /* ENROLLED COLUMN */
        .enrolled-badge {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        /* ACTION BUTTONS - WITH HOVER EFFECTS */
        .btn-view-roster {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-view-roster:hover {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128,0,0,0.3);
        }
        
        .btn-view-profile {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-view-profile:hover {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128,0,0,0.3);
        }
        
        /* STUDENT PROFILE SECTION - EXACT LIKE STUDENT_DASHBOARD */
        .student-profile-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .student-profile-header:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: var(--maroon);
        }
        
        .student-profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid var(--maroon);
            object-fit: cover;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin: 0 auto;
            overflow: hidden;
        }
        
        .student-profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        /* INFO SECTIONS - LIKE STUDENT_DASHBOARD */
        .info-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border-left: 5px solid var(--maroon);
        }
        
        .info-section-title {
            color: var(--maroon);
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(128,0,0,0.1);
            font-size: 1.1rem;
        }
        
        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            background: rgba(128,0,0,0.02);
            padding-left: 10px;
            border-left: 3px solid var(--maroon);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #333;
            font-size: 1rem;
        }
        
        /* GRADE BADGE */
        .grade-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .grade-excellent { background: #d4edda; color: #155724; }
        .grade-good { background: #d1ecf1; color: #0c5460; }
        .grade-average { background: #fff3cd; color: #856404; }
        .grade-poor { background: #f8d7da; color: #721c24; }
        
        /* BUTTON STYLES */
        .btn-msu {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(128,0,0,0.2);
        }
        
        .btn-msu:hover {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(128,0,0,0.3);
        }
        
        .btn-outline-msu {
            border: 2px solid var(--maroon);
            color: var(--maroon);
            background: transparent;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-msu:hover {
            background: var(--maroon);
            color: white;
            transform: translateY(-2px);
        }
        
        /* BREADCRUMB */
        .breadcrumb-nav {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }
        
        .breadcrumb-item.active {
            color: var(--maroon);
            font-weight: 600;
        }
        
        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #888;
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* MOBILE STYLES */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.3rem;
        }
        
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
            
            .clean-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .clean-table th,
            .clean-table td {
                min-width: 150px;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .card-header {
                padding: 20px;
                font-size: 1.1rem;
            }
            
            .clean-table th,
            .clean-table td {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="faculty_dashboard.php">
                <i class="fas fa-users me-2"></i>
                MSU BUUG - <?php echo translateStudentProfile('my_students'); ?>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="me-3 text-white d-none d-md-block text-end">
                    <div><strong><?php echo htmlspecialchars($faculty_info['name']); ?></strong></div>
                    <small><?php echo htmlspecialchars($faculty_info['position']); ?></small>
                </div>
                <div class="dropdown">
                    <a class="text-white text-decoration-none d-flex align-items-center" href="#" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- PROFILE PICTURE -->
                        <div class="user-avatar me-2">
                            <?php 
                            $profile_pic_path = $faculty_data['profile_picture'] ?? null;
                            $first_letter = !empty($faculty_info['name']) ? strtoupper(substr($faculty_info['name'], 0, 1)) : 'F';
                            ?>
                            
                            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile">
                            <?php else: ?>
                                <div class="letter-avatar">
                                    <?php echo $first_letter; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-chevron-down fa-xs ms-1"></i>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="faculty_profile.php"><i class="fas fa-user-circle me-2 text-maroon"></i><?php echo translateStudentProfile('my_profile'); ?></a></li>
                        <li><a class="dropdown-item" href="my_classes.php"><i class="fas fa-book me-2 text-maroon"></i><?php echo translateStudentProfile('my_classes'); ?></a></li>
                        <li><a class="dropdown-item active" href="my_students.php"><i class="fas fa-users me-2 text-maroon"></i><?php echo translateStudentProfile('my_students'); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="logout_faculty.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i><?php echo translateStudentProfile('logout'); ?>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="mobile-user-info d-lg-none p-4 border-bottom">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3">
                    <?php 
                    $profile_pic_path = $faculty_data['profile_picture'] ?? null;
                    $first_letter = !empty($faculty_info['name']) ? strtoupper(substr($faculty_info['name'], 0, 1)) : 'F';
                    
                    if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile">
                    <?php else: ?>
                        <?php echo $first_letter; ?>
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
                <i class="fas fa-tachometer-alt me-3"></i> <?php echo translateStudentProfile('dashboard'); ?>
            </a>
            <a href="my_classes.php" class="nav-link">
                <i class="fas fa-book me-3"></i> <?php echo translateStudentProfile('my_classes'); ?>
            </a>
          
            <a href="my_grades.php" class="nav-link">
                <i class="fas fa-chart-line me-3"></i> <?php echo translateStudentProfile('grade_management'); ?>
            </a>
            <a href="my_assignments.php" class="nav-link">
                <i class="fas fa-tasks me-3"></i> <?php echo translateStudentProfile('assignments'); ?>
            </a>
            <a href="faculty_analytics.php" class="nav-link">
                <i class="fas fa-chart-bar me-3"></i> <?php echo translateStudentProfile('analytics_reports'); ?>
            </a>
           
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="faculty_dashboard.php"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="my_students.php">My Students</a></li>
                <?php if ($selected_class_id && $selected_class): ?>
                    <li class="breadcrumb-item">
                        <a href="my_students.php?class_id=<?php echo $selected_class_id; ?>">
                            <?php echo htmlspecialchars($selected_class['subject_code'] . ' - Sec ' . $selected_class['section']); ?>
                        </a>
                    </li>
                    <?php if ($selected_student_id && $student_profile): ?>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($student_profile['last_name'] . ', ' . $student_profile['first_name']); ?></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="text-maroon mb-2">
                    <i class="fas fa-users me-2"></i>
                    <?php if ($selected_student_id && $student_profile): ?>
                        <?php echo translateStudentProfile('student_profile'); ?>
                    <?php elseif ($selected_class_id && $selected_class): ?>
                        Class Roster
                    <?php else: ?>
                        My Students
                    <?php endif; ?>
                </h2>
                <p class="text-muted mb-0">
                    <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($faculty_info['department']); ?> • 
                    <i class="fas fa-id-card ms-2 me-1"></i>Faculty ID: <?php echo htmlspecialchars($faculty_info['faculty_id']); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="badge bg-secondary px-3 py-2">
                    <i class="fas fa-calendar me-1"></i><?php echo date('F d, Y'); ?>
                </div>
            </div>
        </div>

        <?php if ($selected_student_id && $student_profile && $class_info): ?>
        <!-- ==================== STUDENT PROFILE VIEW (EXACT LIKE STUDENT_DASHBOARD) ==================== -->
        <!-- THIS VIEW ONLY HAS TRANSLATIONS -->
        
        <!-- Student Profile Header -->
        <div class="student-profile-header">
            <div class="row align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <!-- Profile Picture -->
                    <div class="student-profile-picture">
                        <?php 
                        $student_profile_pic = $student_profile['profile_picture'] ?? null;
                        $student_first_letter = !empty($student_profile['first_name']) ? strtoupper(substr($student_profile['first_name'], 0, 1)) : 'S';
                        ?>
                        
                        <?php if ($student_profile_pic && file_exists($student_profile_pic)): ?>
                            <img src="<?php echo htmlspecialchars($student_profile_pic); ?>" 
                                 alt="Student Profile Picture">
                        <?php else: ?>
                            <?php echo $student_first_letter; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <h3 class="text-maroon mb-2">
                        <?php echo htmlspecialchars($student_profile['last_name'] . ', ' . $student_profile['first_name'] . ' ' . ($student_profile['middle_name'] ?? '')); ?>
                    </h3>
                    <p class="mb-1">
                        <strong><?php echo translateStudentProfile('student_id'); ?>:</strong> <?php echo htmlspecialchars($student_profile['student_id']); ?>
                    </p>
                    <p class="mb-1">
                        <strong><?php echo translateStudentProfile('course_program'); ?>:</strong> <?php echo htmlspecialchars($student_profile['course']); ?>
                    </p>
                    <p class="mb-3">
                        <strong><?php echo translateStudentProfile('year_level'); ?>:</strong> <?php echo htmlspecialchars($student_profile['year_level']); ?> Year
                    </p>
                    
                    <!-- Class Performance -->
                    <?php if ($student_class_info && $student_class_info['grade'] !== null): ?>
                        <div class="d-flex align-items-center gap-3">
                            <?php 
                            $grade = $student_class_info['grade'] ?? null;
                            if ($grade !== null) {
                                $grade_class = 'grade-average';
                                if ($grade <= 1.5) $grade_class = 'grade-excellent';
                                elseif ($grade <= 2.0) $grade_class = 'grade-good';
                                elseif ($grade <= 3.0) $grade_class = 'grade-average';
                                else $grade_class = 'grade-poor';
                                
                                echo '<span class="grade-badge ' . $grade_class . '"><strong>' . translateStudentProfile('grade') . ': ' . number_format($grade, 2) . '</strong></span>';
                            }
                            ?>
                            
                            <?php 
                            $status = $student_class_info['enrollment_status'] ?? 'Pending';
                            $status_class = 'badge bg-secondary';
                            if ($status == 'Active') $status_class = 'badge bg-success';
                            elseif ($status == 'Dropped') $status_class = 'badge bg-danger';
                            elseif ($status == 'Completed') $status_class = 'badge bg-primary';
                            
                            echo '<span class="' . $status_class . '">' . htmlspecialchars($status) . '</span>';
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="my_students.php?class_id=<?php echo $selected_class_id; ?>" class="btn btn-outline-msu me-2">
                            <i class="fas fa-arrow-left me-1"></i> <?php echo translateStudentProfile('back_to_students'); ?>
                        </a>
                        <a href="my_students.php" class="btn btn-outline-msu">
                            <i class="fas fa-list me-1"></i> <?php echo translateStudentProfile('all_classes'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="info-section">
            <h5 class="info-section-title"><i class="fas fa-user-circle me-2"></i> <?php echo translateStudentProfile('personal_information'); ?></h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('full_name'); ?></div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($student_profile['last_name'] . ', ' . $student_profile['first_name'] . ' ' . ($student_profile['middle_name'] ?? '')); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('birth_date_place'); ?></div>
                        <div class="info-value">
                            <?php 
                            $birth_date = !empty($student_profile['birth_date']) ? date('F d, Y', strtotime($student_profile['birth_date'])) : 'Not specified';
                            $birth_place = $student_profile['birth_place'] ?? 'Not specified';
                            echo htmlspecialchars($birth_date . ' • ' . $birth_place);
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('gender'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['gender'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('civil_status_religion'); ?></div>
                        <div class="info-value">
                            <?php 
                            $civil_status = $student_profile['civil_status'] ?? 'Not specified';
                            $religion = $student_profile['religion'] ?? 'Not specified';
                            echo htmlspecialchars($civil_status . ' • ' . $religion);
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('tribe'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['tribe'] ?? 'Not specified'); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('blood_type'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['blood_type'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="info-section">
            <h5 class="info-section-title"><i class="fas fa-address-book me-2"></i> <?php echo translateStudentProfile('contact_information'); ?></h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('email_address'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['email']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('school_email'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['school_email'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('phone_number'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['phone'] ?? 'Not specified'); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('address'); ?></div>
                        <div class="info-value">
                            <?php 
                            $address_parts = [];
                            if (!empty($student_profile['street'])) $address_parts[] = $student_profile['street'];
                            if (!empty($student_profile['barangay'])) $address_parts[] = $student_profile['barangay'];
                            if (!empty($student_profile['municipality'])) $address_parts[] = $student_profile['municipality'];
                            if (!empty($student_profile['province'])) $address_parts[] = $student_profile['province'];
                            if (!empty($student_profile['zip_code'])) $address_parts[] = $student_profile['zip_code'];
                            
                            echo htmlspecialchars(implode(', ', $address_parts) ?: ($student_profile['address'] ?? 'Not specified'));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="info-section">
            <h5 class="info-section-title"><i class="fas fa-graduation-cap me-2"></i> <?php echo translateStudentProfile('academic_information'); ?></h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('student_id'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['student_id']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('course_program'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['course']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('year_level'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['year_level']); ?> Year</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('enrollment_status'); ?></div>
                        <div class="info-value">
                            <?php 
                            $status = $student_profile['status'] ?? 'active';
                            $status_class = 'badge bg-secondary';
                            if ($status == 'active') $status_class = 'badge bg-success';
                            elseif ($status == 'inactive') $status_class = 'badge bg-danger';
                            
                            echo '<span class="' . $status_class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('date_registered'); ?></div>
                        <div class="info-value">
                            <?php 
                            $date_registered = !empty($student_profile['date_registered']) ? date('F d, Y', strtotime($student_profile['date_registered'])) : 'Not specified';
                            echo htmlspecialchars($date_registered);
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('last_login'); ?></div>
                        <div class="info-value">
                            <?php 
                            $last_login = !empty($student_profile['last_login']) ? date('F d, Y h:i A', strtotime($student_profile['last_login'])) : 'Never';
                            echo htmlspecialchars($last_login);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Family Background -->
        <?php if ($student_profile['father_name'] || $student_profile['mother_name'] || $student_profile['guardian']): ?>
        <div class="info-section">
            <h5 class="info-section-title"><i class="fas fa-users me-2"></i> <?php echo translateStudentProfile('family_background'); ?></h5>
            
            <div class="row">
                <?php if ($student_profile['father_name']): ?>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('fathers_name'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['father_name']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($student_profile['mother_name']): ?>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('mothers_name'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['mother_name']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($student_profile['guardian']): ?>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('guardian'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['guardian']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($student_profile['guardian_number']): ?>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('guardian_contact'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['guardian_number']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Emergency Contact -->
        <?php if ($student_profile['emergency_contact'] || $student_profile['emergency_phone']): ?>
        <div class="info-section">
            <h5 class="info-section-title"><i class="fas fa-phone-alt me-2"></i> <?php echo translateStudentProfile('emergency_contact'); ?></h5>
            
            <div class="row">
                <?php if ($student_profile['emergency_contact']): ?>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('emergency_contact_person'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['emergency_contact']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($student_profile['emergency_phone']): ?>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('emergency_phone_number'); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($student_profile['emergency_phone']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Class Performance Details -->
        <?php if ($student_class_info && $class_info): ?>
        <div class="info-section">
            <h5 class="info-section-title"><i class="fas fa-chart-line me-2"></i> <?php echo translateStudentProfile('class_performance_details'); ?></h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('subject'); ?></div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($class_info['subject_code'] . ' - ' . $class_info['subject_name']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('section'); ?></div>
                        <div class="info-value">
                            <?php echo htmlspecialchars('Section ' . $class_info['section'] . ' • ' . $class_info['academic_year'] . ' - ' . $class_info['semester'] . ' Semester'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <?php if ($student_class_info['grade'] !== null): ?>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('grade'); ?></div>
                        <div class="info-value">
                            <?php 
                            $grade = $student_class_info['grade'] ?? null;
                            if ($grade !== null) {
                                $grade_class = 'grade-average';
                                if ($grade <= 1.5) $grade_class = 'grade-excellent';
                                elseif ($grade <= 2.0) $grade_class = 'grade-good';
                                elseif ($grade <= 3.0) $grade_class = 'grade-average';
                                else $grade_class = 'grade-poor';
                                
                                echo '<span class="grade-badge ' . $grade_class . '">' . number_format($grade, 2) . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <div class="info-label"><?php echo translateStudentProfile('enrollment_status'); ?></div>
                        <div class="info-value">
                            <?php 
                            $status = $student_class_info['enrollment_status'] ?? 'Pending';
                            $status_class = 'badge bg-secondary';
                            if ($status == 'Active') $status_class = 'badge bg-success';
                            elseif ($status == 'Dropped') $status_class = 'badge bg-danger';
                            elseif ($status == 'Completed') $status_class = 'badge bg-primary';
                            
                            echo '<span class="' . $status_class . '">' . htmlspecialchars($status) . '</span>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($student_class_info['remarks'])): ?>
            <div class="info-item">
                <div class="info-label"><?php echo translateStudentProfile('remarks'); ?></div>
                <div class="info-value"><?php echo htmlspecialchars($student_class_info['remarks']); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="info-item">
                <div class="info-label"><?php echo translateStudentProfile('enrollment_date'); ?></div>
                <div class="info-value">
                    <?php 
                    $enrollment_date = $student_class_info['enrollment_date'] ? date('F d, Y', strtotime($student_class_info['enrollment_date'])) : 'Not specified';
                    echo htmlspecialchars($enrollment_date);
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php elseif ($selected_class_id && $selected_class): ?>
        <!-- ==================== CLASS ROSTER VIEW ==================== -->
        <!-- THIS VIEW REMAINS UNCHANGED - NO TRANSLATIONS -->
        <div class="dashboard-card">
            <div class="card-header">
                <i class="fas fa-users me-2"></i>Class Roster
                <a href="my_students.php" class="btn btn-outline-msu btn-sm float-end">
                    <i class="fas fa-arrow-left me-2"></i>Back to All Classes
                </a>
            </div>
            
            <div class="card-body">
                <!-- Class Info Header -->
                <div class="roster-header">
                    <h4 class="mb-3">
                        <?php echo htmlspecialchars($selected_class['subject_code']); ?> - 
                        Section <?php echo htmlspecialchars($selected_class['section']); ?>
                    </h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Subject Title:</strong> <?php echo htmlspecialchars($selected_class['descriptive_title']); ?></p>
                            <p class="mb-0"><strong>Total Students:</strong> <?php echo $selected_class['enrolled_students']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Schedule:</strong> <?php echo htmlspecialchars($selected_class['schedule']); ?></p>
                            <p class="mb-0"><strong>Room:</strong> <?php echo htmlspecialchars($selected_class['rooms']); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Roster Table -->
                <?php if (count($class_roster) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover roster-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student ID</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Course</th>
                                    <th>Year Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php foreach ($class_roster as $student): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><code><?php echo htmlspecialchars($student['student_id']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($student['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['middle_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                    <td>
                                        <span class="year-badge">
                                            <?php echo htmlspecialchars($student['year_level']); ?> Year
                                        </span>
                                    </td>
                                    <td>
                                        <a href="my_students.php?class_id=<?php echo $selected_class_id; ?>&student_id=<?php echo $student['student_id']; ?>" 
                                           class="btn-view-profile">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-end">
                        <button onclick="printRoster()" class="btn btn-msu">
                            <i class="fas fa-print me-2"></i>Print Roster
                        </button>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h4>No Students Enrolled</h4>
                        <p>No students have enrolled in this class yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- ==================== ALL CLASSES VIEW ==================== -->
        <!-- THIS VIEW REMAINS UNCHANGED - NO TRANSLATIONS -->
        <div class="dashboard-card">
            <div class="card-header">
                <i class="fas fa-list-alt me-2"></i>My Classes
                <span class="float-end badge bg-warning text-dark">
                    <?php echo count($classes); ?> Class<?php echo count($classes) != 1 ? 'es' : ''; ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="clean-table">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Descriptive Title</th>
                                <th>Schedule</th>
                                <th>Room</th>
                                <th>Enrolled</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($classes) > 0): ?>
                                <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td>
                                        <div class="subject-code">
                                            <?php echo htmlspecialchars($class['subject_code']); ?>
                                            <span class="section-badge">Sec <?php echo htmlspecialchars($class['section']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="subject-title">
                                            <?php echo htmlspecialchars($class['descriptive_title']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="schedule-display">
                                            <?php echo htmlspecialchars($class['schedule']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="room-display">
                                            <?php echo htmlspecialchars($class['rooms']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="enrolled-badge">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $class['enrolled_students']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="my_students.php?class_id=<?php echo $class['class_id']; ?>" 
                                           class="btn-view-roster">
                                            <i class="fas fa-eye me-1"></i> View Students
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                            <h4>No Classes Assigned</h4>
                                            <p>You are not assigned to any classes this semester.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
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

        // Print roster function
        function printRoster() {
            const originalContent = document.body.innerHTML;
            const rosterContent = document.querySelector('.dashboard-card').innerHTML;
            
            document.body.innerHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Class Roster - MSU Buug</title>
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap');
                        body { 
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                            margin: 25px; 
                            font-size: 12px;
                            background: white;
                        }
                        .print-header {
                            text-align: center;
                            margin-bottom: 25px;
                            border-bottom: 3px solid #800000;
                            padding-bottom: 15px;
                        }
                        .print-header h2 {
                            color: #800000;
                            margin: 0;
                            font-size: 24px;
                            font-weight: 700;
                        }
                        .print-header h3 {
                            margin: 10px 0 5px 0;
                            font-size: 18px;
                            font-weight: 600;
                        }
                        .class-info {
                            background: #f8f9fa;
                            padding: 15px;
                            border-radius: 8px;
                            margin-bottom: 20px;
                            border-left: 4px solid #800000;
                        }
                        .class-info p {
                            margin: 5px 0;
                        }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                            margin-top: 15px;
                            font-size: 11px;
                        }
                        th { 
                            background: #5a0000; 
                            color: white; 
                            padding: 10px 8px; 
                            text-align: left; 
                            font-weight: 600;
                        }
                        td { 
                            padding: 8px; 
                            border-bottom: 1px solid #ddd;
                        }
                        tr:nth-child(even) {
                            background-color: #f9f9f9;
                        }
                        .year-badge {
                            background: #800000;
                            color: white;
                            padding: 3px 8px;
                            border-radius: 10px;
                            font-size: 10px;
                            font-weight: 600;
                        }
                        .print-footer {
                            margin-top: 30px;
                            text-align: center;
                            font-size: 10px;
                            color: #666;
                            border-top: 1px solid #ddd;
                            padding-top: 10px;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h2>MSU BUUG - Class Roster</h2>
                        <h3>Faculty: <?php echo htmlspecialchars($faculty_info['name']); ?></h3>
                        <p>Printed on: <?php echo date('F d, Y h:i A'); ?></p>
                    </div>
                    ${rosterContent}
                    <div class="print-footer">
                        <p>Official Document - MSU Buug Faculty Portal | Page 1 of 1</p>
                    </div>
                </body>
                </html>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
        
        // Auto-scroll to roster if selected
        <?php if ($selected_class_id && !$selected_student_id): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const rosterSection = document.querySelector('.dashboard-card');
            if (rosterSection) {
                setTimeout(() => {
                    rosterSection.scrollIntoView({ behavior: 'smooth' });
                }, 500);
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>