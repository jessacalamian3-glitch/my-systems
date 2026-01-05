<?php
// faculty_profile.php - FACULTY PROFILE PAGE (CONSISTENT WITH DASHBOARD DESIGN)
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

// ==================== GET FACULTY DATA ====================
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

// ==================== MAIN PROFILE LOGIC ====================
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
$success_message = '';
$error_message = '';

// Handle profile picture upload ONLY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    try {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/faculty/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['profile_picture']['size'] <= 2097152) {
                    // Get current faculty data to check old picture
                    $current_data = getFacultyData($faculty_id);
                    $old_picture = $current_data['profile_picture'] ?? null;
                    
                    // Delete old profile picture if exists
                    if ($old_picture && file_exists($old_picture)) {
                        unlink($old_picture);
                    }
                    
                    // Generate new filename
                    $file_name = 'faculty_' . $faculty_id . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                        // Update database
                        $database = new Database();
                        $db = $database->getConnection();
                        
                        if ($db) {
                            // Check if profile_picture column exists
                            $check_column = $db->query("SHOW COLUMNS FROM faculty LIKE 'profile_picture'");
                            if ($check_column->rowCount() == 0) {
                                $db->exec("ALTER TABLE faculty ADD COLUMN profile_picture VARCHAR(255) NULL AFTER position");
                            }
                            
                            $stmt = $db->prepare("UPDATE faculty SET profile_picture = ? WHERE faculty_id = ?");
                            $result = $stmt->execute([$file_path, $faculty_id]);
                            
                            if ($result) {
                                $success_message = ($current_language == 'fil') ? "Matagumpay na na-update ang profile picture!" : "Profile picture updated successfully!";
                                $faculty_data = getFacultyData($faculty_id);
                            } else {
                                $error_message = ($current_language == 'fil') ? "Hindi na-update ang profile picture sa database." : "Failed to update profile picture in database.";
                            }
                        }
                    } else {
                        $error_message = ($current_language == 'fil') ? "Hindi na-move ang uploaded file." : "Failed to move uploaded file.";
                    }
                } else {
                    $error_message = ($current_language == 'fil') ? "Masyadong malaki ang file. Maximum size ay 2MB." : "File is too large. Maximum size is 2MB.";
                }
            } else {
                $error_message = ($current_language == 'fil') ? "Invalid na file type. JPG, JPEG, PNG, GIF, at WEBP lang ang allowed." : "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
            }
        } else {
            $error_message = ($current_language == 'fil') ? "Pumili ng valid na image file." : "Please select a valid image file.";
        }
    } catch(PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// ==================== SET SESSION USER INFO ====================
if ($faculty_data) {
    $_SESSION['user_info'] = [
        'name' => ($faculty_data['first_name'] ?? '') . ' ' . ($faculty_data['last_name'] ?? ''),
        'email' => $faculty_data['email'] ?? '',
        'department' => $faculty_data['department'] ?? '',
        'position' => $faculty_data['position'] ?? '',
        'profile_picture' => $faculty_data['profile_picture'] ?? null,
        'first_name' => $faculty_data['first_name'] ?? '',
        'last_name' => $faculty_data['last_name'] ?? '',
        'gender' => $faculty_data['gender'] ?? '',
        'birth_date' => $faculty_data['birth_date'] ?? '',
        'birth_place' => $faculty_data['birth_place'] ?? '',
        'blood_type' => $faculty_data['blood_type'] ?? '',
        'civil_status' => $faculty_data['civil_status'] ?? '',
        'religion' => $faculty_data['religion'] ?? '',
        'phone' => $faculty_data['phone'] ?? '',
        'school_email' => $faculty_data['school_email'] ?? '',
        'province' => $faculty_data['province'] ?? '',
        'municipality' => $faculty_data['municipality'] ?? '',
        'barangay' => $faculty_data['barangay'] ?? '',
        'street' => $faculty_data['street'] ?? '',
        'zip_code' => $faculty_data['zip_code'] ?? ''
    ];
} else {
    $_SESSION['user_info'] = [
        'name' => 'Faculty Member',
        'email' => 'faculty@msubuug.edu.ph',
        'department' => 'College of Information Technology',
        'position' => 'Professor',
        'first_name' => '',
        'last_name' => '',
        'gender' => '',
        'birth_date' => '',
        'birth_place' => '',
        'blood_type' => '',
        'civil_status' => '',
        'religion' => '',
        'phone' => '',
        'school_email' => '',
        'province' => '',
        'municipality' => '',
        'barangay' => '',
        'street' => '',
        'zip_code' => ''
    ];
}

$faculty_info = $_SESSION['user_info'];

// ==================== HELPER FUNCTIONS ====================
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

function getValue($info, $key, $default = 'Not set') {
    if (!$info || !isset($info[$key]) || empty($info[$key])) return $default;
    return htmlspecialchars($info[$key]);
}

function formatDate($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return 'Not set';
    return date('F j, Y', strtotime($date));
}

function getSelected($info, $field, $value) {
    if (!$info || !isset($info[$field])) return '';
    return ($info[$field] == $value) ? 'selected' : '';
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

// ==================== TRANSLATION FUNCTION ====================
function translateProfile($key) {
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
            'note_cannot_edit' => 'Note: Personal information cannot be edited. Only profile picture can be updated.'
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
            'note_cannot_edit' => 'Paalala: Hindi maaaring i-edit ang personal na impormasyon. Profile picture lang ang pwedeng i-update.'
        ]
    ];
    
    return $translations[$current_lang][$key] ?? $translations['en'][$key] ?? $key;
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
    <title><?php echo translateProfile('faculty_profile'); ?> - MSU Buug</title>
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
        
        /* PROFILE CARD STYLES (SAME AS DASHBOARD) */
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
        
        /* UPDATED PROFILE HEADER - COMPACT WITH SMALLER TEXT */
        .profile-header-compact {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #eaeaea;
        }
        
        /* MEDIUM PROFILE PICTURE ON LEFT SIDE */
        .profile-picture-medium {
            position: relative;
            width: 180px;
            height: 180px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 6px solid var(--gold);
            padding: 0;
            transition: all 0.3s ease;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            flex-shrink: 0;
        }
        
        .profile-picture-medium:hover {
            transform: scale(1.03);
            border-color: var(--maroon);
            box-shadow: 0 8px 25px rgba(128,0,0,0.15);
        }
        
        .profile-picture-medium img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .profile-upload-btn-medium {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--maroon);
            border: 3px solid white;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
            box-shadow: 0 3px 12px rgba(0,0,0,0.25);
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .profile-upload-btn-medium:hover {
            transform: scale(1.1);
            background: var(--gold);
            color: var(--maroon);
            border-color: var(--maroon);
        }
        
        /* COMPACT PROFILE DETAILS BESIDE PICTURE */
        .profile-details-compact {
            padding-left: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        
        .profile-details-compact h2 {
            color: var(--maroon);
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.5rem;
        }
        
        .profile-details-compact .position {
            color: var(--maroon);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        
        .profile-details-compact .department {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .profile-info-grid-compact {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 15px;
        }
        
        .profile-info-item-compact {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid var(--maroon);
            transition: all 0.3s ease;
        }
        
        .profile-info-item-compact:hover {
            background: #e9ecef;
            border-left-color: var(--gold);
            transform: translateX(3px);
        }
        
        .profile-info-item-compact i {
            color: var(--maroon);
            font-size: 0.9rem;
            width: 20px;
            text-align: center;
        }
        
        .profile-info-item-compact span {
            color: #333;
            font-size: 0.85rem;
        }
        
        /* PROFILE PICTURE STYLES FOR NAVBAR */
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
        
        /* INFO GRID FOR DETAILS SECTION */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
            padding: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 18px;
            border-radius: 12px;
            border-left: 4px solid var(--maroon);
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            transform: translateX(5px);
            border-left-color: var(--gold);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--maroon);
            margin-bottom: 6px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: #333;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        /* PROFILE MODAL STYLES */
        .profile-modal .modal-content {
            border-radius: 20px;
            overflow: hidden;
            border: 3px solid var(--maroon);
        }
        
        .profile-modal .modal-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border-bottom: 3px solid var(--gold);
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.3rem;
        }
        
        /* RESPONSIVE */
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
            
            .profile-picture-medium {
                width: 150px;
                height: 150px;
            }
            
            .profile-details-compact {
                padding-left: 0;
                margin-top: 20px;
            }
            
            .profile-info-grid-compact {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .profile-picture-medium {
                width: 130px;
                height: 130px;
                border-width: 5px;
            }
            
            .profile-upload-btn-medium {
                width: 40px;
                height: 40px;
                font-size: 1rem;
                bottom: 12px;
                right: 12px;
            }
            
            .profile-header-compact {
                padding: 25px;
            }
            
            .profile-details-compact h2 {
                font-size: 1.3rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .profile-picture-medium {
                width: 110px;
                height: 110px;
                border-width: 4px;
            }
            
            .profile-upload-btn-medium {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
                bottom: 10px;
                right: 10px;
            }
            
            .profile-header-compact {
                padding: 20px;
            }
        }
        
        /* ALERTS */
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
        
        /* BUTTONS */
        .btn-maroon {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-maroon:hover {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-outline-maroon {
            border: 2px solid var(--maroon);
            color: var(--maroon);
            background: transparent;
            transition: all 0.3s ease;
        }
        
        .btn-outline-maroon:hover {
            background: var(--maroon);
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Profile Picture Modal -->
    <div class="modal fade profile-modal" id="profilePictureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-camera me-2"></i><?php echo translateProfile('update_profile_picture'); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="text-center mb-4">
                            <div class="profile-picture-container mb-3">
                                <?php 
                                $profile_pic = $faculty_data['profile_picture'] ?? null;
                                $first_letter = getFirstLetter($faculty_info);
                                ?>
                                
                                <?php if ($profile_pic && file_exists($profile_pic)): ?>
                                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
                                         alt="Profile Picture" 
                                         class="profile-picture" 
                                         id="profileImagePreview">
                                <?php else: ?>
                                    <div class="profile-picture d-flex align-items-center justify-content-center bg-light text-maroon fw-bold fs-1" 
                                         style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);" 
                                         id="profileImagePreview">
                                        <?php echo $first_letter; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <button type="button" class="profile-upload-btn" onclick="document.getElementById('profilePictureInput').click()">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            
                            <input type="file" id="profilePictureInput" name="profile_picture" 
                                   accept="image/*" style="display: none;" 
                                   onchange="handleFileSelect(this)">
                            
                            <div id="fileInfo" class="mt-2"></div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong><?php echo translateProfile('file_requirements'); ?>:</strong><br>
                            • <?php echo translateProfile('max_file_size'); ?><br>
                            • <?php echo translateProfile('allowed_types'); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
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
                        <li><a class="dropdown-item active" href="faculty_profile.php"><i class="fas fa-user-circle me-2 text-maroon"></i><?php echo translateProfile('my_profile'); ?></a></li>
                        <li><a class="dropdown-item" href="faculty_settings.php"><i class="fas fa-cog me-2 text-maroon"></i><?php echo translateProfile('settings'); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="logout_faculty.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i><?php echo translateProfile('logout'); ?>
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
                    if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile">
                    <?php else: ?>
                        <div class="letter-avatar">
                            <?php echo getFirstLetter($faculty_info); ?>
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
                <i class="fas fa-tachometer-alt me-3"></i> <?php echo translateProfile('dashboard'); ?>
            </a>
            <a href="my_classes.php" class="nav-link">
                <i class="fas fa-book me-3"></i> <?php echo translateProfile('my_classes'); ?>
            </a>
            <a href="my_grades.php" class="nav-link">
                <i class="fas fa-chart-line me-3"></i> <?php echo translateProfile('grade_management'); ?>
            </a>
            <a href="my_assignments.php" class="nav-link">
                <i class="fas fa-tasks me-3"></i> <?php echo translateProfile('assignments'); ?>
            </a>
            <a href="faculty_analytics.php" class="nav-link">
                <i class="fas fa-chart-bar me-3"></i> <?php echo translateProfile('analytics_reports'); ?>
            </a>
            <a href="faculty_settings.php" class="nav-link">
                <i class="fas fa-cog me-3"></i> <?php echo translateProfile('settings'); ?>
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

        <!-- COMPACT PROFILE HEADER - PICTURE ON LEFT, SMALL INFO BESIDE -->
        <div class="profile-header-compact">
            <div class="row align-items-center">
                <!-- PROFILE PICTURE ON LEFT -->
                <div class="col-md-4 col-lg-3 text-center text-md-start mb-4 mb-md-0">
                    <div class="d-flex justify-content-center justify-content-md-start">
                        <div class="profile-picture-medium">
                            <?php 
                            $profile_pic_path = $faculty_data['profile_picture'] ?? null;
                            $first_letter = getFirstLetter($faculty_info);
                            ?>
                            
                            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" 
                                     alt="Profile Picture" 
                                     class="profile-picture">
                            <?php else: ?>
                                <div class="profile-picture d-flex align-items-center justify-content-center bg-light text-maroon fw-bold fs-1">
                                    <?php echo $first_letter; ?>
                                </div>
                            <?php endif; ?>
                            
                            <button type="button" class="profile-upload-btn-medium" data-bs-toggle="modal" data-bs-target="#profilePictureModal">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- UPLOAD PROFILE PICTURE BUTTON -->
                    <div class="upload-profile-btn text-center text-md-start mt-3">
                        <button type="button" class="btn btn-outline-maroon btn-sm" data-bs-toggle="modal" data-bs-target="#profilePictureModal">
                            <i class="fas fa-upload me-1"></i> <?php echo translateProfile('upload_profile_picture'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- PROFILE DETAILS BESIDE PICTURE -->
                <div class="col-md-8 col-lg-9">
                    <div class="profile-details-compact">
                        <h2><?php echo htmlspecialchars(getDisplayName($faculty_info)); ?></h2>
                        
                        <div class="position">
                            <i class="fas fa-user-tie me-2"></i>
                            <?php echo htmlspecialchars($faculty_info['position']); ?>
                        </div>
                        
                        <div class="department">
                            <i class="fas fa-building me-2"></i>
                            <?php echo htmlspecialchars($faculty_info['department']); ?>
                        </div>
                        
                        <div class="profile-info-grid-compact">
                            <div class="profile-info-item-compact">
                                <i class="fas fa-id-card"></i>
                                <span>ID: <?php echo htmlspecialchars($faculty_id); ?></span>
                            </div>
                            <div class="profile-info-item-compact">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($faculty_info['email']); ?></span>
                            </div>
                            <div class="profile-info-item-compact">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($faculty_info['phone'] ?: 'Not set'); ?></span>
                            </div>
                            <div class="profile-info-item-compact">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo formatDate($faculty_info['birth_date']); ?></span>
                            </div>
                        </div>
                        
                        <small class="text-muted mt-3 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            <?php echo translateProfile('note_cannot_edit'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- PERSONAL INFORMATION CARD -->
        <div class="dashboard-card">
            <div class="card-header">
                <i class="fas fa-user-circle me-2"></i><?php echo translateProfile('personal_info'); ?>
                <span class="badge bg-secondary float-end"><?php echo translateProfile('read_only'); ?></span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('first_name'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'first_name'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('last_name'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'last_name'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('gender'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'gender'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('birth_date'); ?></div>
                        <div class="info-value"><?php echo formatDate($faculty_info['birth_date']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('birth_place'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'birth_place'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('civil_status'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'civil_status'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('religion'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'religion'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('blood_type'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'blood_type'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTACT INFORMATION CARD -->
        <div class="dashboard-card">
            <div class="card-header">
                <i class="fas fa-address-book me-2"></i><?php echo translateProfile('contact_info'); ?>
                <span class="badge bg-secondary float-end"><?php echo translateProfile('read_only'); ?></span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('personal_email'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'email'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('school_email'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'school_email'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('mobile_number'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'phone'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('department'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'department'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('position'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'position'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ADDRESS INFORMATION CARD -->
        <div class="dashboard-card">
            <div class="card-header">
                <i class="fas fa-home me-2"></i><?php echo translateProfile('address_info'); ?>
                <span class="badge bg-secondary float-end"><?php echo translateProfile('read_only'); ?></span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('street'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'street'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('barangay'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'barangay'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('municipality'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'municipality'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('province'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'province'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo translateProfile('zip_code'); ?></div>
                        <div class="info-value"><?php echo getValue($faculty_info, 'zip_code'); ?></div>
                    </div>
                </div>
            </div>
        </div>
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

        // Profile picture upload function
        function handleFileSelect(input) {
            const fileInfo = document.getElementById('fileInfo');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Display file information
                fileInfo.innerHTML = `
                    <div class="alert alert-info">
                        <strong><?php echo translateProfile('selected_file'); ?>:</strong> ${file.name}<br>
                        <strong><?php echo translateProfile('size'); ?>:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
                        <strong><?php echo translateProfile('type'); ?>:</strong> ${file.type}
                        <div class="spinner-border spinner-border-sm text-maroon mt-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2"><?php echo translateProfile('uploading'); ?></span>
                    </div>
                `;
                
                // Validate file size
                if (file.size > 2097152) {
                    fileInfo.innerHTML = `
                        <div class="alert alert-danger">
                            <?php echo translateProfile('max_file_size'); ?>
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
                            <?php echo translateProfile('allowed_types'); ?>
                        </div>
                    `;
                    input.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const profileImage = document.getElementById('profileImagePreview');
                    if (profileImage && profileImage.tagName === 'IMG') {
                        profileImage.src = e.target.result;
                    } else if (profileImage && profileImage.tagName === 'DIV') {
                        // Replace div with img
                        profileImage.outerHTML = `<img src="${e.target.result}" 
                                                      alt="Profile Picture" 
                                                      class="profile-picture" 
                                                      id="profileImagePreview">`;
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
        
        // Close modal after upload
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('profilePictureModal');
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function () {
                    // Refresh page to show updated profile picture
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                });
            }
        });
    </script>
</body>
</html>