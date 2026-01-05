<?php
// my_assignments.php - FACULTY ASSIGNMENTS MANAGEMENT (CONSISTENT WITH DASHBOARD DESIGN)
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
            // General
            'faculty_profile' => 'Faculty Profile',
            'my_profile' => 'My Profile',
            'dashboard' => 'Dashboard',
            'my_classes' => 'My Classes',
            'grade_management' => 'Grade Management',
            'assignments' => 'Assignments',
            'analytics_reports' => 'Analytics & Reports',
            'settings' => 'Settings',
            'logout' => 'Logout',
            'notifications' => 'Notifications',
            
            // Assignments Page
            'assignments_management' => 'Assignments Management',
            'create_manage_assignments' => 'Create and manage assignments and categories',
            'select_class' => 'Select Class',
            'choose_class' => '-- Choose Class --',
            'view' => 'View',
            'total_assignments' => 'Total Assignments',
            'categories' => 'Categories',
            'add_new_category' => 'Add New Category',
            'category_name' => 'Category Name',
            'category_name_placeholder' => 'e.g., Quizzes, Projects, Exams',
            'weight_percent' => 'Weight (%)',
            'weight_placeholder' => 'e.g., 30',
            'percentage_weight' => 'Percentage weight for this category (1-100%)',
            'create_category' => 'Create Category',
            'assignment_categories' => 'Assignment Categories',
            'total_weight' => 'Total Weight',
            'created' => 'Created',
            'actions' => 'Actions',
            'no_categories_created' => 'No Categories Created',
            'no_categories_message' => 'Create your first assignment category to get started.',
            'edit_category' => 'Edit Category',
            'cancel' => 'Cancel',
            'save_changes' => 'Save Changes',
            'delete_category_confirm' => 'Delete this category?',
            'create_new_assignment' => 'Create New Assignment',
            'title' => 'Title',
            'title_placeholder' => 'e.g., Chapter 1 Quiz',
            'description' => 'Description',
            'description_placeholder' => 'Assignment description...',
            'category' => 'Category',
            'select_category' => '-- Select Category --',
            'max_score' => 'Max Score',
            'max_score_placeholder' => 'e.g., 100',
            'deadline' => 'Deadline',
            'create_assignment' => 'Create Assignment',
            'class_assignments' => 'Class Assignments',
            'no_assignments_created' => 'No Assignments Created',
            'no_assignments_message' => 'Create your first assignment to get started.',
            'delete_assignment_confirm' => 'Delete this assignment?',
            'edit_assignment' => 'Edit Assignment',
            'pts' => 'pts',
            'upcoming' => 'Upcoming',
            'today' => 'Today',
            'past' => 'Past',
            
            // Success/Error Messages
            'category_created' => 'Assignment category created successfully!',
            'category_updated' => 'Assignment category updated successfully!',
            'category_deleted' => 'Assignment category deleted successfully!',
            'assignment_created' => 'Assignment created successfully!',
            'assignment_updated' => 'Assignment updated successfully!',
            'assignment_deleted' => 'Assignment deleted successfully!',
            'error_creating_category' => 'Error creating assignment category.',
            'error_updating_category' => 'Error updating assignment category.',
            'error_deleting_category' => 'Cannot delete category with existing assignments.',
            'error_creating_assignment' => 'Error creating assignment.',
            'error_updating_assignment' => 'Error updating assignment.',
            'error_deleting_assignment' => 'Error deleting assignment.'
        ],
        'fil' => [
            // General
            'faculty_profile' => 'Profile ng Faculty',
            'my_profile' => 'Aking Profile',
            'dashboard' => 'Dashboard',
            'my_classes' => 'Aking mga Klase',
            'grade_management' => 'Pamamahala ng Marka',
            'assignments' => 'Mga Gawain',
            'analytics_reports' => 'Analytics & Mga Ulat',
            'settings' => 'Mga Setting',
            'logout' => 'Logout',
            'notifications' => 'Mga Abiso',
            
            // Assignments Page
            'assignments_management' => 'Pamamahala ng mga Gawain',
            'create_manage_assignments' => 'Gumawa at pamahalaan ang mga gawain at kategorya',
            'select_class' => 'Pumili ng Klase',
            'choose_class' => '-- Pumili ng Klase --',
            'view' => 'Tingnan',
            'total_assignments' => 'Kabuuang mga Gawain',
            'categories' => 'Mga Kategorya',
            'add_new_category' => 'Magdagdag ng Bagong Kategorya',
            'category_name' => 'Pangalan ng Kategorya',
            'category_name_placeholder' => 'hal., Mga Pagsusulit, Proyekto, Eksaminasyon',
            'weight_percent' => 'Bigat (%)',
            'weight_placeholder' => 'hal., 30',
            'percentage_weight' => 'Percentage weight para sa kategoryang ito (1-100%)',
            'create_category' => 'Gumawa ng Kategorya',
            'assignment_categories' => 'Mga Kategorya ng Gawain',
            'total_weight' => 'Kabuuang Bigat',
            'created' => 'Nagawa',
            'actions' => 'Mga Aksyon',
            'no_categories_created' => 'Walang Naisang Kategorya',
            'no_categories_message' => 'Gumawa ng iyong unang kategorya ng gawain upang magsimula.',
            'edit_category' => 'I-edit ang Kategorya',
            'cancel' => 'Kanselahin',
            'save_changes' => 'I-save ang Pagbabago',
            'delete_category_confirm' => 'Tanggalin ang kategoryang ito?',
            'create_new_assignment' => 'Gumawa ng Bagong Gawain',
            'title' => 'Pamagat',
            'title_placeholder' => 'hal., Pagsusulit sa Kabanata 1',
            'description' => 'Paglalarawan',
            'description_placeholder' => 'Paglalarawan ng gawain...',
            'category' => 'Kategorya',
            'select_category' => '-- Pumili ng Kategorya --',
            'max_score' => 'Pinakamataas na Marka',
            'max_score_placeholder' => 'hal., 100',
            'deadline' => 'Takdang Petsa',
            'create_assignment' => 'Gumawa ng Gawain',
            'class_assignments' => 'Mga Gawain sa Klase',
            'no_assignments_created' => 'Walang Naisang Gawain',
            'no_assignments_message' => 'Gumawa ng iyong unang gawain upang magsimula.',
            'delete_assignment_confirm' => 'Tanggalin ang gawaing ito?',
            'edit_assignment' => 'I-edit ang Gawain',
            'pts' => 'pts',
            'upcoming' => 'Darating',
            'today' => 'Ngayon',
            'past' => 'Lumipas',
            
            // Success/Error Messages
            'category_created' => 'Matagumpay na nailikha ang kategorya ng gawain!',
            'category_updated' => 'Matagumpay na na-update ang kategorya ng gawain!',
            'category_deleted' => 'Matagumpay na natanggal ang kategorya ng gawain!',
            'assignment_created' => 'Matagumpay na nailikha ang gawain!',
            'assignment_updated' => 'Matagumpay na na-update ang gawain!',
            'assignment_deleted' => 'Matagumpay na natanggal ang gawain!',
            'error_creating_category' => 'Error sa paglikha ng kategorya ng gawain.',
            'error_updating_category' => 'Error sa pag-update ng kategorya ng gawain.',
            'error_deleting_category' => 'Hindi maaaring tanggalin ang kategorya na may mga umiiral na gawain.',
            'error_creating_assignment' => 'Error sa paglikha ng gawain.',
            'error_updating_assignment' => 'Error sa pag-update ng gawain.',
            'error_deleting_assignment' => 'Error sa pagtanggal ng gawain.'
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

// Function to get faculty classes for dropdown
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
    return array();
}

// Function to get class details
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

// Function to get assignment categories for a class
function getAssignmentCategories($class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    category_id,
                    category_name,
                    weight,
                    created_at
                  FROM assignment_categories 
                  WHERE class_id = :class_id
                  ORDER BY created_at";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}

// Function to get assignments for a class (USING CORRECT COLUMN NAMES)
function getClassAssignments($class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    a.assignment_id,
                    a.title,
                    a.description,
                    a.deadline,
                    a.max_score,
                    a.category_id,
                    ac.category_name,
                    ac.weight as category_weight,
                    a.created_at,
                    a.created_by,
                    a.updated_at
                  FROM assignments a
                  JOIN assignment_categories ac ON a.category_id = ac.category_id
                  WHERE a.class_id = :class_id
                  ORDER BY a.deadline DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return array();
}

// Function to create new assignment category
function createAssignmentCategory($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "INSERT INTO assignment_categories 
                  (category_name, weight, class_id, created_at) 
                  VALUES (:category_name, :weight, :class_id, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_name', $data['category_name']);
        $stmt->bindParam(':weight', $data['weight']);
        $stmt->bindParam(':class_id', $data['class_id']);
        
        return $stmt->execute();
    }
    return false;
}

// Function to update assignment category
function updateAssignmentCategory($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "UPDATE assignment_categories 
                  SET category_name = :category_name, 
                      weight = :weight 
                  WHERE category_id = :category_id AND class_id = :class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':category_name', $data['category_name']);
        $stmt->bindParam(':weight', $data['weight']);
        $stmt->bindParam(':class_id', $data['class_id']);
        
        return $stmt->execute();
    }
    return false;
}

// Function to delete assignment category
function deleteAssignmentCategory($category_id, $class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        // First check if there are assignments in this category
        $checkQuery = "SELECT COUNT(*) as assignment_count 
                      FROM assignments 
                      WHERE category_id = :category_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':category_id', $category_id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['assignment_count'] > 0) {
            return false; // Cannot delete category with assignments
        }
        
        // Delete category
        $query = "DELETE FROM assignment_categories 
                  WHERE category_id = :category_id AND class_id = :class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':class_id', $class_id);
        
        return $stmt->execute();
    }
    return false;
}

// Function to create new assignment (USING CORRECT COLUMN NAMES)
function createAssignment($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "INSERT INTO assignments 
                  (class_id, category_id, title, description, deadline, max_score, created_by, created_at, updated_at) 
                  VALUES (:class_id, :category_id, :title, :description, :deadline, :max_score, :created_by, NOW(), NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $data['class_id']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':deadline', $data['deadline']);
        $stmt->bindParam(':max_score', $data['max_score']);
        $stmt->bindParam(':created_by', $data['created_by']);
        
        return $stmt->execute();
    }
    return false;
}

// Function to update assignment (USING CORRECT COLUMN NAMES)
function updateAssignment($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "UPDATE assignments 
                  SET title = :title, 
                      description = :description,
                      deadline = :deadline,
                      max_score = :max_score,
                      category_id = :category_id,
                      updated_at = NOW()
                  WHERE assignment_id = :assignment_id AND class_id = :class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':assignment_id', $data['assignment_id']);
        $stmt->bindParam(':class_id', $data['class_id']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':deadline', $data['deadline']);
        $stmt->bindParam(':max_score', $data['max_score']);
        
        return $stmt->execute();
    }
    return false;
}

// Function to delete assignment
function deleteAssignment($assignment_id, $class_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        // Delete assignment submissions first if table exists
        try {
            $checkTable = $db->query("SHOW TABLES LIKE 'assignment_submissions'");
            if ($checkTable->rowCount() > 0) {
                $deleteSubmissions = "DELETE FROM assignment_submissions WHERE assignment_id = :assignment_id";
                $stmt1 = $db->prepare($deleteSubmissions);
                $stmt1->bindParam(':assignment_id', $assignment_id);
                $stmt1->execute();
            }
        } catch (Exception $e) {
            // Table doesn't exist, continue
        }
        
        // Delete assignment
        $query = "DELETE FROM assignments WHERE assignment_id = :assignment_id AND class_id = :class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->bindParam(':class_id', $class_id);
        
        return $stmt->execute();
    }
    return false;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle category operations
    if (isset($_POST['create_category'])) {
        $category_data = array(
            'category_name' => $_POST['category_name'],
            'weight' => $_POST['weight'],
            'class_id' => $_POST['class_id']
        );
        
        if (createAssignmentCategory($category_data)) {
            $_SESSION['success_message'] = translate('category_created');
        } else {
            $_SESSION['error_message'] = translate('error_creating_category');
        }
        header("Location: my_assignments.php?class_id=" . $_POST['class_id'] . "&tab=categories");
        exit();
    }
    
    if (isset($_POST['update_category'])) {
        $category_data = array(
            'category_id' => $_POST['category_id'],
            'category_name' => $_POST['category_name'],
            'weight' => $_POST['weight'],
            'class_id' => $_POST['class_id']
        );
        
        if (updateAssignmentCategory($category_data)) {
            $_SESSION['success_message'] = translate('category_updated');
        } else {
            $_SESSION['error_message'] = translate('error_updating_category');
        }
        header("Location: my_assignments.php?class_id=" . $_POST['class_id'] . "&tab=categories");
        exit();
    }
    
    if (isset($_POST['delete_category'])) {
        if (deleteAssignmentCategory($_POST['category_id'], $_POST['class_id'])) {
            $_SESSION['success_message'] = translate('category_deleted');
        } else {
            $_SESSION['error_message'] = translate('error_deleting_category');
        }
        header("Location: my_assignments.php?class_id=" . $_POST['class_id'] . "&tab=categories");
        exit();
    }
    
    // Handle assignment operations
    if (isset($_POST['create_assignment'])) {
        $assignment_data = array(
            'class_id' => $_POST['class_id'],
            'category_id' => $_POST['category_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'deadline' => $_POST['deadline'],
            'max_score' => $_POST['max_score'],
            'created_by' => $_SESSION['username']
        );
        
        if (createAssignment($assignment_data)) {
            $_SESSION['success_message'] = translate('assignment_created');
        } else {
            $_SESSION['error_message'] = translate('error_creating_assignment');
        }
        header("Location: my_assignments.php?class_id=" . $_POST['class_id'] . "&tab=assignments");
        exit();
    }
    
    if (isset($_POST['update_assignment'])) {
        $assignment_data = array(
            'assignment_id' => $_POST['assignment_id'],
            'class_id' => $_POST['class_id'],
            'category_id' => $_POST['category_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'deadline' => $_POST['deadline'],
            'max_score' => $_POST['max_score']
        );
        
        if (updateAssignment($assignment_data)) {
            $_SESSION['success_message'] = translate('assignment_updated');
        } else {
            $_SESSION['error_message'] = translate('error_updating_assignment');
        }
        header("Location: my_assignments.php?class_id=" . $_POST['class_id'] . "&tab=assignments");
        exit();
    }
    
    if (isset($_POST['delete_assignment'])) {
        if (deleteAssignment($_POST['assignment_id'], $_POST['class_id'])) {
            $_SESSION['success_message'] = translate('assignment_deleted');
        } else {
            $_SESSION['error_message'] = translate('error_deleting_assignment');
        }
        header("Location: my_assignments.php?class_id=" . $_POST['class_id'] . "&tab=assignments");
        exit();
    }
}

// ==================== SET SESSION USER INFO ====================
$faculty_id = $_SESSION['username'] ?? null;
$faculty_data = getFacultyData($faculty_id);

if ($faculty_data) {
    $_SESSION['user_info'] = array(
        'name' => ($faculty_data['first_name'] ?? '') . ' ' . ($faculty_data['last_name'] ?? ''),
        'email' => $faculty_data['email'] ?? '',
        'department' => $faculty_data['department'] ?? '',
        'position' => $faculty_data['position'] ?? '',
        'profile_picture' => $faculty_data['profile_picture'] ?? null,
        'first_name' => $faculty_data['first_name'] ?? '',
        'last_name' => $faculty_data['last_name'] ?? ''
    );
} else {
    $_SESSION['user_info'] = array(
        'name' => 'Faculty Member',
        'email' => 'faculty@msubuug.edu.ph',
        'department' => 'College of Information Technology',
        'position' => 'Professor',
        'first_name' => '',
        'last_name' => ''
    );
}

$faculty_info = $_SESSION['user_info'];
$classes = getFacultyClasses($faculty_id);

// Get selected class and active tab
$selected_class_id = $_GET['class_id'] ?? null;
$active_tab = $_GET['tab'] ?? 'categories';
$class_details = null;
$assignment_categories = array();
$class_assignments = array();

if ($selected_class_id) {
    $class_details = getClassDetails($selected_class_id);
    $assignment_categories = getAssignmentCategories($selected_class_id);
    $class_assignments = getClassAssignments($selected_class_id);
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

function formatDate($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return 'Not set';
    return date('F j, Y g:i A', strtotime($date));
}

function formatDateInput($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return '';
    return date('Y-m-d\TH:i', strtotime($date));
}

function getSelected($info, $field, $value) {
    if (!$info || !isset($info[$field])) return '';
    return ($info[$field] == $value) ? 'selected' : '';
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
    <title><?php echo translate('assignments_management'); ?> - MSU Buug</title>
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
        
        /* TABS STYLING */
        .nav-tabs-custom {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 25px;
        }
        
        .nav-tabs-custom .nav-link {
            color: #666;
            font-weight: 600;
            padding: 15px 25px;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            background: none;
        }
        
        .nav-tabs-custom .nav-link:hover {
            color: var(--maroon);
            background: rgba(128, 0, 0, 0.05);
        }
        
        .nav-tabs-custom .nav-link.active {
            color: var(--maroon);
            border-bottom: 3px solid var(--maroon);
            background: rgba(128, 0, 0, 0.05);
        }
        
        /* ASSIGNMENTS TABLE */
        .assignments-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .assignments-table thead {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
        }
        
        .assignments-table th {
            color: white;
            padding: 18px 15px;
            font-weight: 600;
            text-align: left;
            border: none;
            font-size: 0.95rem;
        }
        
        .assignments-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: middle;
        }
        
        .assignments-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .assignments-table tbody tr:hover {
            background: rgba(128, 0, 0, 0.03);
            transform: translateY(-2px);
        }
        
        /* CATEGORY BADGES */
        .category-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            background: rgba(128, 0, 0, 0.1);
            color: var(--maroon);
            border: 1px solid rgba(128, 0, 0, 0.2);
        }
        
        /* DUE DATE BADGE */
        .due-date-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .due-date-upcoming {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .due-date-today {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
        
        .due-date-past {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        /* WEIGHT INDICATOR */
        .weight-indicator {
            display: inline-block;
            padding: 4px 8px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* ACTION BUTTONS */
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .action-btn-edit {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .action-btn-edit:hover {
            background: #ffc107;
            color: white;
        }
        
        .action-btn-delete {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .action-btn-delete:hover {
            background: #dc3545;
            color: white;
        }
        
        /* MODAL STYLES */
        .modal-custom .modal-content {
            border-radius: 15px;
            overflow: hidden;
            border: 3px solid var(--maroon);
        }
        
        .modal-custom .modal-header {
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
            
            .assignments-table {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 768px) {
            .nav-tabs-custom .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .assignments-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .nav-tabs-custom .nav-link {
                padding: 8px 12px;
                font-size: 0.85rem;
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
                        <li><a class="dropdown-item active" href="my_assignments.php"><i class="fas fa-tasks me-2 text-maroon"></i><?php echo translate('assignments'); ?></a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2 text-maroon"></i><?php echo translate('settings'); ?></a></li>
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
                    $first_letter = getFirstLetter($faculty_info);
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
            <a href="my_grades.php" class="nav-link">
                <i class="fas fa-chart-line me-3"></i> <?php echo translate('grade_management'); ?>
            </a>
            <a href="my_assignments.php" class="nav-link active">
                <i class="fas fa-tasks me-3"></i> <?php echo translate('assignments'); ?>
            </a>
            <a href="faculty_analytics.php" class="nav-link">
                <i class="fas fa-chart-bar me-3"></i> <?php echo translate('analytics_reports'); ?>
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
                    <i class="fas fa-tasks me-2"></i><?php echo translate('assignments_management'); ?>
                    <small class="d-block mt-1" style="opacity: 0.9;"><?php echo translate('create_manage_assignments'); ?></small>
                </div>
            </div>
            <div class="card-body">
                <!-- CLASS SELECTOR -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-bold text-maroon"><?php echo translate('select_class'); ?>:</label>
                        <form method="GET" action="my_assignments.php" class="d-flex gap-2">
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
                <div class="class-info-card" style="
                    background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
                    color: white;
                    border-radius: 15px;
                    padding: 20px;
                    margin-bottom: 25px;
                ">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="fas fa-chalkboard-teacher me-2"></i>
                                <?php echo htmlspecialchars($class_details['subject_code']); ?> - Section <?php echo htmlspecialchars($class_details['section']); ?>
                            </h4>
                            <p class="mb-1"><?php echo htmlspecialchars($class_details['subject_name']); ?></p>
                            <p class="mb-0 opacity-90">
                                <i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($class_details['instructor_name']); ?> • 
                                <i class="fas fa-users me-1"></i> <?php echo $class_details['total_students']; ?> Students
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column align-items-end">
                                <div class="fs-1 fw-bold text-gold">
                                    <?php echo count($class_assignments); ?>
                                </div>
                                <div class="small"><?php echo translate('total_assignments'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS -->
                <ul class="nav nav-tabs-custom">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_tab == 'categories' ? 'active' : ''; ?>" 
                           href="my_assignments.php?class_id=<?php echo $selected_class_id; ?>&tab=categories">
                           <i class="fas fa-folder me-2"></i><?php echo translate('categories'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_tab == 'assignments' ? 'active' : ''; ?>" 
                           href="my_assignments.php?class_id=<?php echo $selected_class_id; ?>&tab=assignments">
                           <i class="fas fa-tasks me-2"></i><?php echo translate('assignments'); ?>
                        </a>
                    </li>
                </ul>

                <?php if ($active_tab == 'categories'): ?>
                <!-- CATEGORIES TAB -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="fas fa-plus-circle me-2"></i><?php echo translate('add_new_category'); ?>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="my_assignments.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                    <input type="hidden" name="create_category" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('category_name'); ?></label>
                                        <input type="text" name="category_name" class="form-control" required 
                                               placeholder="<?php echo translate('category_name_placeholder'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('weight_percent'); ?></label>
                                        <input type="number" name="weight" class="form-control" required 
                                               min="1" max="100" step="1" placeholder="<?php echo translate('weight_placeholder'); ?>">
                                        <small class="text-muted"><?php echo translate('percentage_weight'); ?></small>
                                    </div>
                                    
                                    <button type="submit" class="btn w-100" style="
                                        background: var(--maroon);
                                        color: white;
                                        border: none;
                                        padding: 12px;
                                        border-radius: 10px;
                                        font-weight: 600;
                                    ">
                                        <i class="fas fa-save me-1"></i> <?php echo translate('create_category'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-folder me-2"></i><?php echo translate('assignment_categories'); ?>
                                    <span class="badge bg-secondary ms-2"><?php echo count($assignment_categories); ?></span>
                                </div>
                                <div>
                                    <span class="badge bg-success"><?php echo translate('total_weight'); ?>: 
                                        <?php 
                                        $total_weight = 0;
                                        foreach ($assignment_categories as $cat) {
                                            $total_weight += $cat['weight'];
                                        }
                                        echo $total_weight;
                                        ?>%
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($assignment_categories) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="assignments-table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo translate('category_name'); ?></th>
                                                    <th><?php echo translate('weight_percent'); ?></th>
                                                    <th><?php echo translate('created'); ?></th>
                                                    <th><?php echo translate('actions'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($assignment_categories as $category): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="weight-indicator"><?php echo $category['weight']; ?>%</span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo formatDate($category['created_at']); ?></small>
                                                    </td>
                                                    <td>
                                                        <button class="action-btn action-btn-edit me-2" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editCategoryModal"
                                                                data-category-id="<?php echo $category['category_id']; ?>"
                                                                data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                                data-weight="<?php echo $category['weight']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" action="my_assignments.php" class="d-inline" onsubmit="return confirm('<?php echo translate('delete_category_confirm'); ?>');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                            <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                                            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                                            <input type="hidden" name="delete_category" value="1">
                                                            <button type="submit" class="action-btn action-btn-delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted"><?php echo translate('no_categories_created'); ?></h4>
                                        <p class="text-muted"><?php echo translate('no_categories_message'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- EDIT CATEGORY MODAL -->
                <div class="modal fade modal-custom" id="editCategoryModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-edit me-2"></i><?php echo translate('edit_category'); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="my_assignments.php" id="editCategoryForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                    <input type="hidden" name="category_id" id="editCategoryId">
                                    <input type="hidden" name="update_category" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('category_name'); ?></label>
                                        <input type="text" name="category_name" id="editCategoryName" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('weight_percent'); ?></label>
                                        <input type="number" name="weight" id="editWeight" class="form-control" required 
                                               min="1" max="100" step="1">
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal"><?php echo translate('cancel'); ?></button>
                                        <button type="submit" class="btn" style="
                                            background: var(--maroon);
                                            color: white;
                                            border: none;
                                            padding: 8px 20px;
                                            border-radius: 8px;
                                        ">
                                            <i class="fas fa-save me-1"></i> <?php echo translate('save_changes'); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($active_tab == 'assignments'): ?>
                <!-- ASSIGNMENTS TAB -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="fas fa-plus-circle me-2"></i><?php echo translate('create_new_assignment'); ?>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="my_assignments.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                    <input type="hidden" name="create_assignment" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('title'); ?></label>
                                        <input type="text" name="title" class="form-control" required 
                                               placeholder="<?php echo translate('title_placeholder'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('description'); ?></label>
                                        <textarea name="description" class="form-control" rows="3" 
                                                  placeholder="<?php echo translate('description_placeholder'); ?>"></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label"><?php echo translate('category'); ?></label>
                                            <select name="category_id" class="form-select" required>
                                                <option value=""><?php echo translate('select_category'); ?></option>
                                                <?php foreach ($assignment_categories as $category): ?>
                                                    <option value="<?php echo $category['category_id']; ?>">
                                                        <?php echo htmlspecialchars($category['category_name']); ?> (<?php echo $category['weight']; ?>%)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><?php echo translate('max_score'); ?></label>
                                            <input type="number" name="max_score" class="form-control" required 
                                                   min="1" max="1000" step="1" placeholder="<?php echo translate('max_score_placeholder'); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('deadline'); ?></label>
                                        <input type="datetime-local" name="deadline" class="form-control" required>
                                    </div>
                                    
                                    <button type="submit" class="btn w-100" style="
                                        background: var(--maroon);
                                        color: white;
                                        border: none;
                                        padding: 12px;
                                        border-radius: 10px;
                                        font-weight: 600;
                                    ">
                                        <i class="fas fa-save me-1"></i> <?php echo translate('create_assignment'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-tasks me-2"></i><?php echo translate('class_assignments'); ?>
                                    <span class="badge bg-secondary ms-2"><?php echo count($class_assignments); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($class_assignments) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="assignments-table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo translate('title'); ?></th>
                                                    <th><?php echo translate('category'); ?></th>
                                                    <th><?php echo translate('deadline'); ?></th>
                                                    <th><?php echo translate('max_score'); ?></th>
                                                    <th><?php echo translate('created'); ?></th>
                                                    <th><?php echo translate('actions'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($class_assignments as $assignment): 
                                                    $deadline = strtotime($assignment['deadline']);
                                                    $current_date = time();
                                                    $due_class = 'due-date-upcoming';
                                                    $due_text = translate('upcoming');
                                                    if ($current_date > $deadline) {
                                                        $due_class = 'due-date-past';
                                                        $due_text = translate('past');
                                                    } elseif (date('Y-m-d', $current_date) == date('Y-m-d', $deadline)) {
                                                        $due_class = 'due-date-today';
                                                        $due_text = translate('today');
                                                    }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                                        <?php if (!empty($assignment['description'])): ?>
                                                            <br><small class="text-muted"><?php echo substr(htmlspecialchars($assignment['description']), 0, 50); ?>...</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="category-badge">
                                                            <?php echo htmlspecialchars($assignment['category_name']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="due-date-badge <?php echo $due_class; ?>">
                                                            <?php echo formatDate($assignment['deadline']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold"><?php echo $assignment['max_score']; ?></span> <?php echo translate('pts'); ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo formatDate($assignment['created_at']); ?></small>
                                                    </td>
                                                    <td>
                                                        <button class="action-btn action-btn-edit me-2" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editAssignmentModal"
                                                                data-assignment-id="<?php echo $assignment['assignment_id']; ?>"
                                                                data-title="<?php echo htmlspecialchars($assignment['title']); ?>"
                                                                data-description="<?php echo htmlspecialchars($assignment['description']); ?>"
                                                                data-category-id="<?php echo $assignment['category_id']; ?>"
                                                                data-max-score="<?php echo $assignment['max_score']; ?>"
                                                                data-deadline="<?php echo formatDateInput($assignment['deadline']); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" action="my_assignments.php" class="d-inline" onsubmit="return confirm('<?php echo translate('delete_assignment_confirm'); ?>');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                            <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                                                            <input type="hidden" name="delete_assignment" value="1">
                                                            <button type="submit" class="action-btn action-btn-delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted"><?php echo translate('no_assignments_created'); ?></h4>
                                        <p class="text-muted"><?php echo translate('no_assignments_message'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- EDIT ASSIGNMENT MODAL -->
                <div class="modal fade modal-custom" id="editAssignmentModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-edit me-2"></i><?php echo translate('edit_assignment'); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="my_assignments.php" id="editAssignmentForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                                    <input type="hidden" name="assignment_id" id="editAssignmentId">
                                    <input type="hidden" name="update_assignment" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('title'); ?></label>
                                        <input type="text" name="title" id="editTitle" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('description'); ?></label>
                                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label"><?php echo translate('category'); ?></label>
                                            <select name="category_id" id="editCategoryIdSelect" class="form-select" required>
                                                <option value=""><?php echo translate('select_category'); ?></option>
                                                <?php foreach ($assignment_categories as $category): ?>
                                                    <option value="<?php echo $category['category_id']; ?>">
                                                        <?php echo htmlspecialchars($category['category_name']); ?> (<?php echo $category['weight']; ?>%)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><?php echo translate('max_score'); ?></label>
                                            <input type="number" name="max_score" id="editMaxScore" class="form-control" required 
                                                   min="1" max="1000" step="1">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo translate('deadline'); ?></label>
                                        <input type="datetime-local" name="deadline" id="editDeadline" class="form-control" required>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal"><?php echo translate('cancel'); ?></button>
                                        <button type="submit" class="btn" style="
                                            background: var(--maroon);
                                            color: white;
                                            border: none;
                                            padding: 8px 20px;
                                            border-radius: 8px;
                                        ">
                                            <i class="fas fa-save me-1"></i> <?php echo translate('save_changes'); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Edit Category Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editCategoryModal = document.getElementById('editCategoryModal');
            if (editCategoryModal) {
                editCategoryModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const categoryId = button.getAttribute('data-category-id');
                    const categoryName = button.getAttribute('data-category-name');
                    const weight = button.getAttribute('data-weight');
                    
                    document.getElementById('editCategoryId').value = categoryId;
                    document.getElementById('editCategoryName').value = categoryName;
                    document.getElementById('editWeight').value = weight;
                });
            }

            // Edit Assignment Modal
            const editAssignmentModal = document.getElementById('editAssignmentModal');
            if (editAssignmentModal) {
                editAssignmentModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const assignmentId = button.getAttribute('data-assignment-id');
                    const title = button.getAttribute('data-title');
                    const description = button.getAttribute('data-description');
                    const categoryId = button.getAttribute('data-category-id');
                    const maxScore = button.getAttribute('data-max-score');
                    const deadline = button.getAttribute('data-deadline');
                    
                    document.getElementById('editAssignmentId').value = assignmentId;
                    document.getElementById('editTitle').value = title;
                    document.getElementById('editDescription').value = description;
                    document.getElementById('editCategoryIdSelect').value = categoryId;
                    document.getElementById('editMaxScore').value = maxScore;
                    document.getElementById('editDeadline').value = deadline;
                });
            }

            // Set default deadline to tomorrow
            const deadlineInput = document.querySelector('input[name="deadline"]');
            if (deadlineInput && !deadlineInput.value) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setHours(23, 59, 0, 0);
                const formatted = tomorrow.toISOString().slice(0, 16);
                deadlineInput.value = formatted;
            }
        });
    </script>
</body>
</html>