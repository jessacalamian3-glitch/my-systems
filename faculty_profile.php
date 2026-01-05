<?php
// faculty_profile.php - FACULTY PROFILE WITH EDIT FUNCTIONALITY
// ==================== SESSION FIXES ====================
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);
session_set_cookie_params(7200);
session_start();

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
            'academic_info' => 'Academic Information',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'gender' => 'Gender',
            'birth_date' => 'Birth Date',
            'birth_place' => 'Birth Place',
            'blood_type' => 'Blood Type',
            'civil_status' => 'Civil Status',
            'religion' => 'Religion',
            'phone_number' => 'Phone Number',
            'personal_email' => 'Personal Email',
            'school_email' => 'School Email',
            'department' => 'Department',
            'position' => 'Position',
            'street_address' => 'Street Address',
            'barangay' => 'Barangay',
            'city_municipality' => 'City/Municipality',
            'province' => 'Province',
            'zip_code' => 'ZIP Code',
            'edit_profile' => 'Edit Profile',
            'change_password' => 'Change Password',
            'save_changes' => 'Save Changes',
            'cancel' => 'Cancel',
            'update_password' => 'Update Password',
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password',
            'password_requirements' => 'Password Requirements',
            'account_security' => 'Account Security',
            'last_changed' => 'Last changed',
            'never' => 'Never',
            'not_set' => 'Not set',
            'click_edit_to_add' => 'Click edit to add',
            'edit' => 'Edit',
            'change' => 'Change',
            'profile_picture_info' => 'Profile picture: Max 2MB. Allowed: JPG, JPEG, PNG, GIF, WEBP',
            'select_file' => 'Selected File',
            'size' => 'Size',
            'type' => 'Type',
            'uploading' => 'Uploading...',
            'select_option' => 'Select option',
            'single' => 'Single',
            'married' => 'Married',
            'separated' => 'Separated',
            'widowed' => 'Widowed',
            'a_plus' => 'A+',
            'a_minus' => 'A-',
            'b_plus' => 'B+',
            'b_minus' => 'B-',
            'ab_plus' => 'AB+',
            'ab_minus' => 'AB-',
            'o_plus' => 'O+',
            'o_minus' => 'O-'
        ],
        'fil' => [
            'faculty_profile' => 'Profile ng Faculty',
            'my_profile' => 'Aking Profile',
            'dashboard' => 'Dashboard',
            'my_classes' => 'Aking mga Klase',
            'grade_management' => 'Pamamahala ng Marka',
            'assignments' => 'Mga Gawain',
            'analytics_reports' => 'Analytics at Mga Ulat',
            'settings' => 'Mga Setting',
            'logout' => 'Logout',
            'personal_info' => 'Personal na Impormasyon',
            'contact_info' => 'Impormasyon sa Pakikipag-ugnayan',
            'address_info' => 'Impormasyon ng Address',
            'academic_info' => 'Akademikong Impormasyon',
            'first_name' => 'Pangalan',
            'last_name' => 'Apelyido',
            'gender' => 'Kasarian',
            'birth_date' => 'Petsa ng Kapanganakan',
            'birth_place' => 'Lugar ng Kapanganakan',
            'blood_type' => 'Uri ng Dugo',
            'civil_status' => 'Katayuang Sibil',
            'religion' => 'Relihiyon',
            'phone_number' => 'Numero ng Telepono',
            'personal_email' => 'Personal na Email',
            'school_email' => 'School Email',
            'department' => 'Departamento',
            'position' => 'Posisyon',
            'street_address' => 'Address ng Kalye',
            'barangay' => 'Barangay',
            'city_municipality' => 'Lungsod/Munisipyo',
            'province' => 'Lalawigan',
            'zip_code' => 'ZIP Code',
            'edit_profile' => 'I-edit ang Profile',
            'change_password' => 'Palitan ang Password',
            'save_changes' => 'I-save ang Mga Pagbabago',
            'cancel' => 'Kanselahin',
            'update_password' => 'I-update ang Password',
            'current_password' => 'Kasalukuyang Password',
            'new_password' => 'Bagong Password',
            'confirm_password' => 'Kumpirmahin ang Password',
            'password_requirements' => 'Mga Pangangailangan sa Password',
            'account_security' => 'Seguridad ng Account',
            'last_changed' => 'Huling binago',
            'never' => 'Hindi pa',
            'not_set' => 'Hindi naka-set',
            'click_edit_to_add' => 'I-click ang edit para magdagdag',
            'edit' => 'I-edit',
            'change' => 'Palitan',
            'profile_picture_info' => 'Profile picture: Max na 2MB. Pinapayagan: JPG, JPEG, PNG, GIF, WEBP',
            'select_file' => 'Napiling File',
            'size' => 'Laki',
            'type' => 'Tipo',
            'uploading' => 'Nag-u-upload...',
            'select_option' => 'Pumili ng opsyon',
            'single' => 'Single',
            'married' => 'May asawa',
            'separated' => 'Hiwalay',
            'widowed' => 'Biyudo/Biyuda',
            'a_plus' => 'A+',
            'a_minus' => 'A-',
            'b_plus' => 'B+',
            'b_minus' => 'B-',
            'ab_plus' => 'AB+',
            'ab_minus' => 'AB-',
            'o_plus' => 'O+',
            'o_minus' => 'O-'
        ]
    ];
    
    return $translations[$current_lang][$key] ?? $translations['en'][$key] ?? $key;
}

// ==================== GET CURRENT THEME & LANGUAGE ====================
$faculty_id = $_SESSION['username'] ?? null;

// Get faculty settings for theme and language
$faculty_settings = getFacultySettings($faculty_id);

// Set theme and language in session if not set
if (!isset($_SESSION['theme_color'])) {
    $_SESSION['theme_color'] = $faculty_settings['theme_color'];
}
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = $faculty_settings['language'];
}

$current_theme = $_SESSION['theme_color'] ?? 'maroon';
$current_language = $_SESSION['language'] ?? 'en';

// ==================== FUNCTIONS ====================
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

// Function to verify password
function verifyPassword($db, $faculty_id, $current_password) {
    $query = "SELECT password FROM faculty WHERE faculty_id = :faculty_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':faculty_id', $faculty_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && password_verify($current_password, $result['password'])) {
        return true;
    }
    return false;
}

// ==================== MAIN PROFILE LOGIC ====================
$faculty_id = $_SESSION['username'] ?? null;
$faculty_data = getFacultyData($faculty_id);
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
            $error_message = ($current_language == 'fil') ? "Error sa database: " : "Database error: " . $e->getMessage();
        }
    }
    
    // Handle editable fields update
    if (isset($_POST['update_profile'])) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                // Define editable fields (dynamic fields only for faculty)
                $editable_fields = [
                    'phone', 'email', 'school_email',
                    'street', 'barangay', 'municipality', 'province', 'zip_code',
                    'civil_status', 'religion', 'blood_type'
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
                    $update_values[] = $faculty_id; // For WHERE clause
                    
                    $query = "UPDATE faculty SET " . implode(', ', $update_fields) . ", updated_at = NOW() WHERE faculty_id = ?";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute($update_values);
                    
                    if ($result) {
                        $success_message = ($current_language == 'fil') ? "Matagumpay na na-update ang profile!" : "Profile updated successfully!";
                        $editable_fields_updated = true;
                        // Refresh faculty data
                        $faculty_data = getFacultyData($faculty_id);
                        $edit_mode = false;
                    } else {
                        $error_message = ($current_language == 'fil') ? "Hindi na-update ang profile information." : "Failed to update profile information.";
                    }
                }
            }
        } catch(PDOException $e) {
            $error_message = ($current_language == 'fil') ? "Error sa database: " : "Database error: " . $e->getMessage();
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = ($current_language == 'fil') ? "Paki-fill ang lahat ng password fields." : "Please fill in all password fields.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = ($current_language == 'fil') ? "Hindi magkatugma ang mga bagong password." : "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error_message = ($current_language == 'fil') ? "Ang bagong password ay dapat hindi bababa sa 8 characters ang haba." : "New password must be at least 8 characters long.";
        } else {
            try {
                $database = new Database();
                $db = $database->getConnection();
                
                if ($db) {
                    // Verify current password
                    if (verifyPassword($db, $faculty_id, $current_password)) {
                        // Hash new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        // Update password
                        $stmt = $db->prepare("UPDATE faculty SET password = ?, updated_at = NOW() WHERE faculty_id = ?");
                        $result = $stmt->execute([$hashed_password, $faculty_id]);
                        
                        if ($result) {
                            $success_message = ($current_language == 'fil') ? "Matagumpay na napalitan ang password!" : "Password changed successfully!";
                            $show_password_form = false;
                        } else {
                            $error_message = ($current_language == 'fil') ? "Hindi na-update ang password." : "Failed to update password.";
                        }
                    } else {
                        $error_message = ($current_language == 'fil') ? "Mali ang kasalukuyang password." : "Current password is incorrect.";
                    }
                }
            } catch(PDOException $e) {
                $error_message = ($current_language == 'fil') ? "Error sa database: " : "Database error: " . $e->getMessage();
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

// Helper functions
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

function getValue($info, $key, $default = '') {
    if (!$info || !isset($info[$key]) || empty($info[$key])) return $default;
    return htmlspecialchars($info[$key]);
}

function formatDate($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return translateProfile('not_set');
    return date('F j, Y', strtotime($date));
}

function formatDateInput($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return '';
    return date('Y-m-d', strtotime($date));
}

// Function to display profile information (view mode)
function displayFacultyInfo($faculty_info, $edit_mode = false) {
    $current_language = $_SESSION['language'] ?? 'en';
    
    $categories = [
        'personal_info' => [
            'first_name' => ['label' => translateProfile('first_name'), 'editable' => false],
            'last_name' => ['label' => translateProfile('last_name'), 'editable' => false],
            'gender' => ['label' => translateProfile('gender'), 'editable' => false],
            'birth_date' => ['label' => translateProfile('birth_date'), 'editable' => false, 'type' => 'date'],
            'birth_place' => ['label' => translateProfile('birth_place'), 'editable' => false],
            'civil_status' => ['label' => translateProfile('civil_status'), 'editable' => true, 'options' => [
                'Single' => translateProfile('single'),
                'Married' => translateProfile('married'),
                'Separated' => translateProfile('separated'),
                'Widowed' => translateProfile('widowed')
            ]],
            'religion' => ['label' => translateProfile('religion'), 'editable' => true],
            'blood_type' => ['label' => translateProfile('blood_type'), 'editable' => true, 'options' => [
                'A+' => translateProfile('a_plus'),
                'A-' => translateProfile('a_minus'),
                'B+' => translateProfile('b_plus'),
                'B-' => translateProfile('b_minus'),
                'AB+' => translateProfile('ab_plus'),
                'AB-' => translateProfile('ab_minus'),
                'O+' => translateProfile('o_plus'),
                'O-' => translateProfile('o_minus')
            ]],
        ],
        'contact_info' => [
            'phone' => ['label' => translateProfile('phone_number'), 'editable' => true, 'type' => 'tel'],
            'email' => ['label' => translateProfile('personal_email'), 'editable' => true, 'type' => 'email'],
            'school_email' => ['label' => translateProfile('school_email'), 'editable' => true, 'type' => 'email'],
        ],
        'address_info' => [
            'street' => ['label' => translateProfile('street_address'), 'editable' => true],
            'barangay' => ['label' => translateProfile('barangay'), 'editable' => true],
            'municipality' => ['label' => translateProfile('city_municipality'), 'editable' => true],
            'province' => ['label' => translateProfile('province'), 'editable' => true],
            'zip_code' => ['label' => translateProfile('zip_code'), 'editable' => true],
        ],
        'academic_info' => [
            'department' => ['label' => translateProfile('department'), 'editable' => false],
            'position' => ['label' => translateProfile('position'), 'editable' => false],
        ],
    ];
    
    $html = '';
    
    foreach ($categories as $categoryKey => $fields) {
        $categoryTitle = translateProfile($categoryKey);
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
                        $value = formatDate($faculty_info[$field] ?? '');
                        break;
                    default:
                        $value = getValue($faculty_info, $field, translateProfile('not_set'));
                }
            } else {
                $value = getValue($faculty_info, $field, translateProfile('not_set'));
            }
            
            // Check if this field has data
            if ($value !== translateProfile('not_set')) {
                $hasData = true;
            }
            
            if ($edit_mode && $editable) {
                // Edit mode - show input field
                $input_html = '';
                if (isset($fieldInfo['options'])) {
                    // Select dropdown
                    $input_html = '<select name="' . $field . '" class="form-control form-control-sm">';
                    $input_html .= '<option value="">' . translateProfile('select_option') . '</option>';
                    foreach ($fieldInfo['options'] as $optionValue => $optionLabel) {
                        $selected = ($faculty_info[$field] ?? '') == $optionValue ? 'selected' : '';
                        $input_html .= '<option value="' . htmlspecialchars($optionValue) . '" ' . $selected . '>' . $optionLabel . '</option>';
                    }
                    $input_html .= '</select>';
                } else {
                    // Regular input
                    $input_type = isset($fieldInfo['type']) && in_array($fieldInfo['type'], ['tel', 'email']) ? $fieldInfo['type'] : 'text';
                    $input_html = '<input type="' . $input_type . '" name="' . $field . '" value="' . htmlspecialchars($faculty_info[$field] ?? '') . '" class="form-control form-control-sm">';
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
                if ($value === translateProfile('not_set') && $editable) {
                    $display_value = '<span class="text-muted">' . translateProfile('click_edit_to_add') . '</span>';
                }
                
                $categoryHtml .= '
                <div class="info-item">
                    <div class="info-label">' . $label . '</div>
                    <div class="info-value">' . $display_value . '</div>
                </div>';
            }
        }
        
        // Only show the category if it has data or if it's Academic Information
        if ($hasData || $categoryKey === 'academic_info' || $categoryKey === 'personal_info') {
            $html .= '
            <div class="profile-section mb-4">
                <div class="section-header">
                    <h5 class="section-title">
                        <i class="fas ' . getCategoryIcon($categoryKey) . ' me-2"></i>' . $categoryTitle . '
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
        'personal_info' => 'fa-user-circle',
        'contact_info' => 'fa-address-book',
        'address_info' => 'fa-home',
        'academic_info' => 'fa-university',
    ];
    
    return $icons[$category] ?? 'fa-info-circle';
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translateProfile('my_profile'); ?> - MSU Buug Faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --msu-maroon: <?php echo getThemeColor($current_theme, 'primary'); ?>;
            --msu-maroon-dark: <?php echo getThemeColor($current_theme, 'secondary'); ?>;
            --msu-maroon-light: <?php echo getThemeColor($current_theme, 'light'); ?>;
            --msu-gold: #FFD700;
            --light-bg: #f8f9fa;
            --sidebar-width: 250px;
            --navbar-height: 70px;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* EXACT SAME SIDEBAR & HEADER STYLES */
        .navbar {
            background: var(--msu-maroon) !important;
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
            background: var(--msu-maroon-dark);
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
            background: var(--msu-maroon-light);
            border-left-color: var(--msu-gold);
            color: var(--msu-gold);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: var(--msu-maroon-light);
            border-left-color: var(--msu-gold);
            color: var(--msu-gold);
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
            color: var(--msu-gold);
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
            border: 2px solid var(--msu-gold);
            transition: all 0.3s ease;
            background: var(--msu-maroon);
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
            background: var(--msu-maroon-dark);
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
            border-left: 5px solid var(--msu-maroon);
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
            color: var(--msu-maroon);
            font-size: 3rem;
            font-weight: bold;
        }
        
        .avatar-upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            background: var(--msu-maroon);
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
            background: var(--msu-gold);
            color: var(--msu-maroon);
            transform: scale(1.1);
        }
        
        .profile-info {
            flex: 1;
            min-width: 300px;
        }
        
        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--msu-maroon);
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
            color: var(--msu-maroon);
        }
        
        .profile-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* BUTTONS - NEW STYLE */
        .btn-msu {
            background: var(--msu-maroon);
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
            background: var(--msu-maroon-light);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128,0,0,0.3);
        }
        
        .btn-outline-msu {
            background: transparent;
            color: var(--msu-maroon);
            border: 2px solid var(--msu-maroon);
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline-msu:hover {
            background: var(--msu-maroon);
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
            background: linear-gradient(135deg, var(--msu-maroon) 0%, var(--msu-maroon-dark) 100%);
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
            border-left: 4px solid var(--msu-maroon);
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
            color: var(--msu-maroon);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #333;
            font-size: 1rem;
        }
        
        .edit-mode .form-control {
            border-color: var(--msu-maroon);
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
            color: var(--msu-maroon);
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
            border-color: var(--msu-maroon);
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
            border-left: 4px solid var(--msu-maroon);
        }
        
        .password-requirements h6 {
            color: var(--msu-maroon);
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
            border-top: 5px solid var(--msu-maroon);
        }
        
        .security-title {
            color: var(--msu-maroon);
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
            color: var(--msu-maroon);
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
            <a class="navbar-brand nav-brand" href="faculty_dashboard.php">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Faculty Portal</span>
                <span class="d-inline d-sm-none">Faculty Portal</span>
            </a>
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <!-- PERFECT CIRCLE PROFILE PICTURE IN NAVIGATION -->
                        <?php
                        $profile_pic_path = $faculty_data['profile_picture'] ?? null;
                        $first_letter = getFirstLetter($faculty_info);
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
                            <strong><?php echo getDisplayName($faculty_info); ?></strong><br>
                            <small>Faculty</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">

                        <li><a class="dropdown-item active" href="faculty_profile.php"><i class="fas fa-user me-2" style="color: var(--msu-maroon);"></i><?php echo translateProfile('my_profile'); ?></a></li>
                        <li><a class="dropdown-item" href="faculty_settings.php"><i class="fas fa-cog me-2" style="color: var(--msu-maroon);"></i><?php echo translateProfile('settings'); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout_faculty.php"><i class="fas fa-sign-out-alt me-2"></i><?php echo translateProfile('logout'); ?></a></li>
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
                        <?php echo getFirstLetter($faculty_info); ?>
                    </div>
                    <div>
                        <strong><?php echo getDisplayName($faculty_info); ?></strong><br>
                        <small>Faculty ID: <?php echo htmlspecialchars($faculty_id); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="d-flex flex-column pt-3">
                <a href="faculty_dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> <?php echo translateProfile('dashboard'); ?>
                </a>
                <a href="my_classes.php" class="nav-link">
                    <i class="fas fa-book"></i> <?php echo translateProfile('my_classes'); ?>
                </a>
                
                <a href="my_grades.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> <?php echo translateProfile('grade_management'); ?>
                </a>
                <a href="my_assignments.php" class="nav-link">
                    <i class="fas fa-tasks"></i> <?php echo translateProfile('assignments'); ?>
                </a>
                <a href="faculty_analytics.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i> <?php echo translateProfile('analytics_reports'); ?>
                </a>
                <a href="faculty_profile.php" class="nav-link active">
                    <i class="fas fa-user"></i> <?php echo translateProfile('my_profile'); ?>
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
                        $profile_pic = $faculty_data['profile_picture'] ?? null;
                        $first_letter = getFirstLetter($faculty_info);
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
                        <h1 class="profile-name"><?php echo getDisplayName($faculty_info); ?></h1>
                        <div class="profile-id">
                            <i class="fas fa-id-card me-1"></i>
                            Faculty ID: <?php echo htmlspecialchars($faculty_id); ?>
                        </div>
                        
                        <div class="profile-meta">
                            <div class="meta-item">
                                <i class="fas fa-user-tie"></i>
                                <span><?php echo getValue($faculty_info, 'position', translateProfile('not_set')); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-building"></i>
                                <span><?php echo getValue($faculty_info, 'department', translateProfile('not_set')); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo getValue($faculty_info, 'email', translateProfile('not_set')); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo getValue($faculty_info, 'phone', translateProfile('not_set')); ?></span>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <?php if (!$edit_mode && !$show_password_form): ?>
                                <a href="?edit=1" class="btn btn-msu">
                                    <i class="fas fa-edit me-1"></i> <?php echo translateProfile('edit_profile'); ?>
                                </a>
                                <a href="?change_password=1" class="btn btn-outline-msu">
                                    <i class="fas fa-key me-1"></i> <?php echo translateProfile('change_password'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <small class="form-text d-block mt-3 text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <?php echo translateProfile('profile_picture_info'); ?>
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
                    
                    <?php echo displayFacultyInfo($faculty_info, $edit_mode); ?>
                    
                    <?php if ($edit_mode): ?>
                        <div class="action-buttons">
                            <button type="submit" name="update_profile" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> <?php echo translateProfile('save_changes'); ?>
                            </button>
                            <button type="submit" name="cancel_edit" class="btn btn-outline-msu">
                                <i class="fas fa-times me-1"></i> <?php echo translateProfile('cancel'); ?>
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
                            <i class="fas fa-shield-alt"></i> <?php echo translateProfile('account_security'); ?>
                        </h3>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h6><?php echo translateProfile('password'); ?></h6>
                                <p><?php echo translateProfile('last_changed'); ?>: <?php 
                                    $last_updated = isset($faculty_data['updated_at']) && $faculty_data['updated_at'] !== '0000-00-00 00:00:00' 
                                        ? formatDate($faculty_data['updated_at']) 
                                        : translateProfile('never');
                                    echo $last_updated;
                                ?></p>
                            </div>
                            <div>
                                <a href="?change_password=1" class="btn btn-outline-msu btn-sm">
                                    <i class="fas fa-key me-1"></i> <?php echo translateProfile('change'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h6><?php echo translateProfile('email_address'); ?></h6>
                                <p><?php echo getValue($faculty_info, 'email', translateProfile('not_set')); ?></p>
                            </div>
                            <div>
                                <?php if (!$edit_mode): ?>
                                    <a href="?edit=1" class="btn btn-outline-msu btn-sm">
                                        <i class="fas fa-edit me-1"></i> <?php echo translateProfile('edit'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h6><?php echo translateProfile('phone_number'); ?></h6>
                                <p><?php echo getValue($faculty_info, 'phone', translateProfile('not_set')); ?></p>
                            </div>
                            <div>
                                <?php if (!$edit_mode): ?>
                                    <a href="?edit=1" class="btn btn-outline-msu btn-sm">
                                        <i class="fas fa-edit me-1"></i> <?php echo translateProfile('edit'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Change Form -->
                    <?php if ($show_password_form): ?>
                        <div class="password-form-container">
                            <h3 class="password-form-title">
                                <i class="fas fa-key"></i> <?php echo translateProfile('change_password'); ?>
                            </h3>
                            
                            <form method="POST" action="" id="passwordForm">
                                <div class="form-group">
                                    <label class="form-label"><?php echo translateProfile('current_password'); ?></label>
                                    <input type="password" name="current_password" class="form-control" required 
                                           placeholder="<?php echo translateProfile('current_password'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label"><?php echo translateProfile('new_password'); ?></label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" required 
                                           placeholder="<?php echo translateProfile('new_password'); ?>" minlength="8">
                                    <div class="password-strength text-muted">
                                        <small><?php echo translateProfile('password_strength'); ?>: <span id="passwordStrength">Weak</span></small>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label"><?php echo translateProfile('confirm_password'); ?></label>
                                    <input type="password" name="confirm_password" class="form-control" required 
                                           placeholder="<?php echo translateProfile('confirm_password'); ?>">
                                </div>
                                
                                <div class="password-requirements">
                                    <h6><?php echo translateProfile('password_requirements'); ?>:</h6>
                                    <ul>
                                        <li><?php echo translateProfile('at_least_8_characters'); ?></li>
                                        <li><?php echo translateProfile('include_uppercase_lowercase'); ?></li>
                                        <li><?php echo translateProfile('include_at_least_one_number'); ?></li>
                                        <li><?php echo translateProfile('include_special_character'); ?></li>
                                    </ul>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" name="change_password" class="btn btn-success">
                                        <i class="fas fa-key me-1"></i> <?php echo translateProfile('update_password'); ?>
                                    </button>
                                    <button type="submit" name="cancel_edit" class="btn btn-outline-msu">
                                        <i class="fas fa-times me-1"></i> <?php echo translateProfile('cancel'); ?>
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
                        <strong><?php echo translateProfile('select_file'); ?>:</strong> ${file.name}<br>
                        <strong><?php echo translateProfile('size'); ?>:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
                        <strong><?php echo translateProfile('type'); ?>:</strong> ${file.type}
                        <div class="spinner-border spinner-border-sm mt-2" style="color: var(--msu-maroon);" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2"><?php echo translateProfile('uploading'); ?></span>
                    </div>
                `;
                
                // Validate file size
                if (file.size > 2097152) {
                    fileInfo.innerHTML = `
                        <div class="alert alert-danger">
                            <?php echo translateProfile('file_too_large'); ?>
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
                            <?php echo translateProfile('invalid_file_type'); ?>
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
                alert('<?php echo translateProfile('passwords_do_not_match'); ?>');
                return;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('<?php echo translateProfile('password_min_length'); ?>');
                return;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[name="change_password"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> <?php echo translateProfile('updating'); ?>...';
                submitBtn.disabled = true;
            }
        });
        <?php endif; ?>

        // Profile edit form validation
        <?php if ($edit_mode): ?>
        document.getElementById('profileEditForm').addEventListener('submit', function(e) {
            const emailInput = document.querySelector('input[name="email"]');
            const schoolEmailInput = document.querySelector('input[name="school_email"]');
            const phoneInput = document.querySelector('input[name="phone"]');
            
            if (emailInput && emailInput.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    e.preventDefault();
                    alert('<?php echo translateProfile('valid_email_required'); ?>');
                    emailInput.focus();
                    return;
                }
            }
            
            if (schoolEmailInput && schoolEmailInput.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(schoolEmailInput.value)) {
                    e.preventDefault();
                    alert('<?php echo translateProfile('valid_school_email_required'); ?>');
                    schoolEmailInput.focus();
                    return;
                }
            }
            
            if (phoneInput && phoneInput.value) {
                const phoneRegex = /^[0-9+\-\s()]{10,}$/;
                if (!phoneRegex.test(phoneInput.value)) {
                    e.preventDefault();
                    alert('<?php echo translateProfile('valid_phone_required'); ?>');
                    phoneInput.focus();
                    return;
                }
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[name="update_profile"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> <?php echo translateProfile('saving'); ?>...';
                submitBtn.disabled = true;
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>