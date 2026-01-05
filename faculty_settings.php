<?php
session_start();

// Debug: Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include database configuration
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'faculty') {
    header("Location: login_faculty.php");
    exit;
}

$faculty_id = $_SESSION['username'];
$success_message = '';
$error_message = '';

// ==================== SET THEME AND LANGUAGE ====================
// Set theme from database to session
if (!isset($_SESSION['theme_color'])) {
    try {
        $stmt = $pdo->prepare("SELECT theme_color FROM faculty_settings WHERE faculty_id = ?");
        $stmt->execute([$faculty_id]);
        $theme_setting = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['theme_color'] = $theme_setting ? $theme_setting['theme_color'] : 'maroon';
    } catch(PDOException $e) {
        $_SESSION['theme_color'] = 'maroon';
    }
}

// Set language from database to session
if (!isset($_SESSION['language'])) {
    try {
        $stmt = $pdo->prepare("SELECT language FROM faculty_settings WHERE faculty_id = ?");
        $stmt->execute([$faculty_id]);
        $lang_setting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lang_setting && !empty($lang_setting['language'])) {
            $_SESSION['language'] = $lang_setting['language'];
        } else {
            $_SESSION['language'] = 'en';
        }
    } catch(PDOException $e) {
        $_SESSION['language'] = 'en';
    }
}

// Get current language for display
$current_language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';

// Simple translation function inline since language.php might have issues
function getTranslatedText($key) {
    $current_lang = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
    
    $translations = [
        'en' => [
            'settings' => 'Settings',
            'save_settings' => 'Save Settings',
            'reset_default' => 'Reset to Default',
            'theme_color' => 'Theme Color',
            'language' => 'Language',
            'appearance' => 'Appearance',
            'notifications' => 'Notifications',
            'security_privacy' => 'Security & Privacy',
            'enable_notifications' => 'Enable Notifications',
            'email_notifications' => 'Email Notifications',
            'sms_notifications' => 'SMS Notifications',
            'auto_publish_results' => 'Auto-publish Results',
            'two_factor_auth' => 'Two-Factor Authentication',
            'profile_visibility' => 'Profile Visibility',
            'timezone' => 'Timezone',
            'default_page' => 'Default Page',
            'items_per_page' => 'Items Per Page',
            'public' => 'Public',
            'students_only' => 'Students Only',
            'faculty_only' => 'Faculty Only',
            'private' => 'Private'
        ],
        'fil' => [
            'settings' => 'Mga Setting',
            'save_settings' => 'I-save ang Setting',
            'reset_default' => 'I-reset sa Default',
            'theme_color' => 'Kulay ng Tema',
            'language' => 'Wika',
            'appearance' => 'Itsura',
            'notifications' => 'Mga Notipikasyon',
            'security_privacy' => 'Seguridad at Privacy',
            'enable_notifications' => 'Paganahin ang Notipikasyon',
            'email_notifications' => 'Notipikasyon sa Email',
            'sms_notifications' => 'Notipikasyon sa SMS',
            'auto_publish_results' => 'Awtomatikong I-publish ang Resulta',
            'two_factor_auth' => 'Two-Factor Authentication',
            'profile_visibility' => 'Visibility ng Profile',
            'timezone' => 'Time Zone',
            'default_page' => 'Default na Pahina',
            'items_per_page' => 'Items sa Bawat Pahina',
            'public' => 'Pampubliko',
            'students_only' => 'Mga Estudyante Lang',
            'faculty_only' => 'Mga Faculty Lang',
            'private' => 'Pribado'
        ]
    ];
    
    if (isset($translations[$current_lang][$key])) {
        return $translations[$current_lang][$key];
    }
    
    return isset($translations['en'][$key]) ? $translations['en'][$key] : $key;
}
// ==================== END SETTINGS ====================

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $theme_color = $_POST['theme_color'] ?? 'maroon';
        $notifications_enabled = isset($_POST['notifications_enabled']) ? 1 : 0;
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        $results_auto_publish = isset($_POST['results_auto_publish']) ? 1 : 0;
        $default_page = $_POST['default_page'] ?? 'dashboard';
        $items_per_page = intval($_POST['items_per_page'] ?? 10);
        $language = $_POST['language'] ?? 'en';
        $timezone = $_POST['timezone'] ?? 'Asia/Manila';
        $two_factor_auth = isset($_POST['two_factor_auth']) ? 1 : 0;
        $profile_visibility = $_POST['profile_visibility'] ?? 'public';

        // Check if settings already exist
        $check_stmt = $pdo->prepare("SELECT faculty_id FROM faculty_settings WHERE faculty_id = ?");
        $check_stmt->execute([$faculty_id]);
        $settings_exist = $check_stmt->fetch();

        if ($settings_exist) {
            // Update existing settings
            $stmt = $pdo->prepare("
                UPDATE faculty_settings 
                SET theme_color = ?, notifications_enabled = ?, email_notifications = ?, 
                    sms_notifications = ?, results_auto_publish = ?, default_page = ?, 
                    items_per_page = ?, language = ?, timezone = ?, two_factor_auth = ?, 
                    profile_visibility = ?, updated_at = NOW()
                WHERE faculty_id = ?
            ");
            $stmt->execute([
                $theme_color, $notifications_enabled, $email_notifications,
                $sms_notifications, $results_auto_publish, $default_page,
                $items_per_page, $language, $timezone, $two_factor_auth,
                $profile_visibility, $faculty_id
            ]);
        } else {
            // Insert new settings
            $stmt = $pdo->prepare("
                INSERT INTO faculty_settings 
                (faculty_id, theme_color, notifications_enabled, email_notifications, 
                 sms_notifications, results_auto_publish, default_page, items_per_page, 
                 language, timezone, two_factor_auth, profile_visibility) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $faculty_id, $theme_color, $notifications_enabled, $email_notifications,
                $sms_notifications, $results_auto_publish, $default_page, $items_per_page,
                $language, $timezone, $two_factor_auth, $profile_visibility
            ]);
        }

        // Update session immediately
        $_SESSION['theme_color'] = $theme_color;
        $_SESSION['language'] = $language;

        // Set success message
        if ($language == 'fil') {
            $success_message = "Matagumpay na na-update ang setting!";
        } else {
            $success_message = "Settings updated successfully!";
        }

        // Redirect to prevent form resubmission
        header("Location: faculty_settings.php?success=1");
        exit;

    } catch(PDOException $e) {
        error_log("Faculty Settings Error: " . $e->getMessage());
        $error_message = "Error updating settings: " . $e->getMessage();
    }
}

// Check for success parameter
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $current_lang = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
    if ($current_lang == 'fil') {
        $success_message = "Matagumpay na na-update ang setting!";
    } else {
        $success_message = "Settings updated successfully!";
    }
}

// Get faculty settings
try {
    $stmt = $pdo->prepare("SELECT * FROM faculty_settings WHERE faculty_id = ?");
    $stmt->execute([$faculty_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no settings exist, create default settings
    if (!$settings) {
        $settings = [
            'theme_color' => 'maroon',
            'notifications_enabled' => 1,
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'results_auto_publish' => 0,
            'default_page' => 'dashboard',
            'items_per_page' => 10,
            'language' => 'en',
            'timezone' => 'Asia/Manila',
            'two_factor_auth' => 0,
            'profile_visibility' => 'public'
        ];
    }

} catch(PDOException $e) {
    error_log("Faculty Settings Fetch Error: " . $e->getMessage());
    $settings = [
        'theme_color' => 'maroon',
        'notifications_enabled' => 1,
        'email_notifications' => 1,
        'sms_notifications' => 0,
        'results_auto_publish' => 0,
        'default_page' => 'dashboard',
        'items_per_page' => 10,
        'language' => 'en',
        'timezone' => 'Asia/Manila',
        'two_factor_auth' => 0,
        'profile_visibility' => 'public'
    ];
}

// Get faculty basic info for display
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, department FROM faculty WHERE faculty_id = ?");
    $stmt->execute([$faculty_id]);
    $faculty_info = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Faculty Info Fetch Error: " . $e->getMessage());
    $faculty_info = ['first_name' => '', 'last_name' => '', 'email' => '', 'department' => ''];
}

// Helper functions
function getDisplayName($info) {
    if (isset($info['first_name']) && isset($info['last_name'])) {
        return $info['first_name'] . ' ' . $info['last_name'];
    } else {
        return 'Faculty Member';
    }
}

function getFirstLetter($name) {
    return $name ? strtoupper(substr($name, 0, 1)) : '?';
}

function getChecked($settings, $key) {
    return isset($settings[$key]) && $settings[$key] ? 'checked' : '';
}

function getSelected($settings, $key, $value) {
    return (isset($settings[$key]) && $settings[$key] == $value) ? 'selected' : '';
}

// DYNAMIC THEME CSS BASED ON SESSION
$theme_colors = [
    'maroon' => ['primary' => '#800000', 'secondary' => '#5a0000'],
    'blue' => ['primary' => '#007bff', 'secondary' => '#0056b3'],
    'green' => ['primary' => '#28a745', 'secondary' => '#1e7e34'],
    'purple' => ['primary' => '#6f42c1', 'secondary' => '#5a36a8'],
    'dark' => ['primary' => '#343a40', 'secondary' => '#23272b']
];

$current_theme = isset($_SESSION['theme_color']) ? $_SESSION['theme_color'] : 'maroon';
$current_colors = $theme_colors[$current_theme] ?? $theme_colors['maroon'];
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getTranslatedText('settings'); ?> - MSU Buug Faculty Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: <?php echo $current_colors['primary']; ?>;
            --maroon-dark: <?php echo $current_colors['secondary']; ?>;
            --gold: #FFD700;
            --light-bg: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 12px 0;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
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
            transition: all 0.3s ease;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
            width: calc(100% - 280px);
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 15px 25px;
            border-left: 4px solid transparent;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--gold);
            transform: translateX(5px);
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .btn-maroon {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-maroon:hover {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-maroon {
            border: 2px solid var(--maroon);
            color: var(--maroon);
            background: transparent;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-maroon:hover {
            background: var(--maroon);
            color: white;
            transform: translateY(-2px);
        }
        
        .text-maroon {
            color: var(--maroon) !important;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--gold), #ffed4e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--maroon);
            font-weight: bold;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--maroon);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
        }
        
        .section-title {
            border-left: 4px solid var(--maroon);
            padding-left: 15px;
            margin: 25px 0 20px 0;
            color: var(--maroon);
            transition: all 0.3s ease;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
        }
        
        .settings-section {
            padding: 25px;
            border-bottom: 1px solid #eee;
        }
        
        .settings-section:last-child {
            border-bottom: none;
        }
        
        .form-check-input:checked {
            background-color: var(--maroon);
            border-color: var(--maroon);
        }
        
        .theme-color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            cursor: pointer;
            border: 4px solid transparent;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .theme-color-option:hover {
            transform: scale(1.1);
        }
        
        .theme-color-option.active {
            border-color: var(--maroon);
            transform: scale(1.1);
        }
        
        .theme-color-option.active::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 16px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .theme-maroon { background-color: #800000; }
        .theme-blue { background-color: #007bff; }
        .theme-green { background-color: #28a745; }
        .theme-purple { background-color: #6f42c1; }
        .theme-dark { background-color: #343a40; }
        
        .theme-preview {
            padding: 10px;
            border-radius: 8px;
            background: var(--maroon);
            color: white;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .theme-color-option {
                width: 35px;
                height: 35px;
                margin-right: 10px;
            }
        }
        
        @media (max-width: 768px) {
            .theme-color-option {
                width: 30px;
                height: 30px;
                margin-right: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle me-3">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="#">
                <i class="fas fa-cog me-2"></i>
                MSU BUUG - <?php echo getTranslatedText('settings'); ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <?php echo getFirstLetter(getDisplayName($faculty_info)); ?>
                        </div>
                        <span class="text-white"><?php echo htmlspecialchars(getDisplayName($faculty_info)); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="faculty_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item active" href="faculty_settings.php"><i class="fas fa-cog me-2"></i><?php echo getTranslatedText('settings'); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="faculty_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="d-flex flex-column pt-4">
                    <a class="nav-link" href="faculty_dashboard.php">
                        <i class="fas fa-tachometer-alt me-3"></i> Dashboard
                    </a>
                    <a class="nav-link" href="my_classes.php">
                        <i class="fas fa-book me-3"></i> My Classes
                    </a>
                    <a class="nav-link" href="my_grades.php">
                        <i class="fas fa-chart-line me-3"></i> Grade Management
                    </a>
                    <a class="nav-link" href="my_assignments.php">
                        <i class="fas fa-tasks me-3"></i> Assignments
                    </a>
                    <a class="nav-link" href="faculty_analytics.php">
                        <i class="fas fa-chart-bar me-3"></i> Analytics & Reports
                    </a>
                    <a class="nav-link active" href="faculty_settings.php">
                        <i class="fas fa-cog me-3"></i> <?php echo getTranslatedText('settings'); ?>
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

                <div class="settings-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-white"><i class="fas fa-cog me-2"></i><?php echo getTranslatedText('settings'); ?></h4>
                            <span class="text-white-50">Current Theme: <?php echo strtoupper($current_theme); ?></span>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <form method="POST" id="settingsForm">
                            <!-- Appearance Settings -->
                            <div class="settings-section">
                                <h5 class="section-title"><?php echo getTranslatedText('appearance'); ?></h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><?php echo getTranslatedText('theme_color'); ?></label>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="theme-color-option theme-maroon <?php echo $settings['theme_color'] === 'maroon' ? 'active' : ''; ?>" 
                                                 data-color="maroon" onclick="selectTheme('maroon')" title="Maroon Theme"></div>
                                            <div class="theme-color-option theme-blue <?php echo $settings['theme_color'] === 'blue' ? 'active' : ''; ?>" 
                                                 data-color="blue" onclick="selectTheme('blue')" title="Blue Theme"></div>
                                            <div class="theme-color-option theme-green <?php echo $settings['theme_color'] === 'green' ? 'active' : ''; ?>" 
                                                 data-color="green" onclick="selectTheme('green')" title="Green Theme"></div>
                                            <div class="theme-color-option theme-purple <?php echo $settings['theme_color'] === 'purple' ? 'active' : ''; ?>" 
                                                 data-color="purple" onclick="selectTheme('purple')" title="Purple Theme"></div>
                                            <div class="theme-color-option theme-dark <?php echo $settings['theme_color'] === 'dark' ? 'active' : ''; ?>" 
                                                 data-color="dark" onclick="selectTheme('dark')" title="Dark Theme"></div>
                                        </div>
                                        <input type="hidden" name="theme_color" id="themeColor" value="<?php echo htmlspecialchars($settings['theme_color']); ?>">
                                        <div class="theme-preview" id="themePreview">
                                            Current: <?php echo strtoupper($settings['theme_color']); ?> Theme
                                        </div>
                                        <small class="form-text text-muted">
                                            <?php echo $current_language == 'fil' ? 'Pindutin para pumili ng tema, tapos i-save para ma-apply' : 'Click to select theme, then save to apply'; ?>
                                        </small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="language" class="form-label"><?php echo getTranslatedText('language'); ?></label>
                                        <select class="form-select" id="language" name="language">
                                            <option value="en" <?php echo getSelected($settings, 'language', 'en'); ?>>English</option>
                                            <option value="fil" <?php echo getSelected($settings, 'language', 'fil'); ?>>Filipino</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="default_page" class="form-label"><?php echo getTranslatedText('default_page'); ?></label>
                                        <select class="form-select" id="default_page" name="default_page">
                                            <option value="dashboard" <?php echo getSelected($settings, 'default_page', 'dashboard'); ?>><?php echo $current_language == 'fil' ? 'Dashboard' : 'Dashboard'; ?></option>
                                            <option value="my_classes" <?php echo getSelected($settings, 'default_page', 'my_classes'); ?>><?php echo $current_language == 'fil' ? 'Aking mga Klase' : 'My Classes'; ?></option>
                                            <option value="my_grades" <?php echo getSelected($settings, 'default_page', 'my_grades'); ?>><?php echo $current_language == 'fil' ? 'Pamamahala ng Marka' : 'Grade Management'; ?></option>
                                            <option value="my_assignments" <?php echo getSelected($settings, 'default_page', 'my_assignments'); ?>><?php echo $current_language == 'fil' ? 'Mga Gawain' : 'Assignments'; ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="items_per_page" class="form-label"><?php echo getTranslatedText('items_per_page'); ?></label>
                                        <select class="form-select" id="items_per_page" name="items_per_page">
                                            <option value="5" <?php echo getSelected($settings, 'items_per_page', '5'); ?>>5 <?php echo $current_language == 'fil' ? 'items' : 'items'; ?></option>
                                            <option value="10" <?php echo getSelected($settings, 'items_per_page', '10'); ?>>10 <?php echo $current_language == 'fil' ? 'items' : 'items'; ?></option>
                                            <option value="15" <?php echo getSelected($settings, 'items_per_page', '15'); ?>>15 <?php echo $current_language == 'fil' ? 'items' : 'items'; ?></option>
                                            <option value="20" <?php echo getSelected($settings, 'items_per_page', '20'); ?>>20 <?php echo $current_language == 'fil' ? 'items' : 'items'; ?></option>
                                            <option value="25" <?php echo getSelected($settings, 'items_per_page', '25'); ?>>25 <?php echo $current_language == 'fil' ? 'items' : 'items'; ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification Settings -->
                            <div class="settings-section">
                                <h5 class="section-title"><?php echo getTranslatedText('notifications'); ?></h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notifications_enabled" 
                                                   name="notifications_enabled" <?php echo getChecked($settings, 'notifications_enabled'); ?>>
                                            <label class="form-check-label" for="notifications_enabled">
                                                <?php echo getTranslatedText('enable_notifications'); ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" 
                                                   name="email_notifications" <?php echo getChecked($settings, 'email_notifications'); ?>>
                                            <label class="form-check-label" for="email_notifications">
                                                <?php echo getTranslatedText('email_notifications'); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="sms_notifications" 
                                                   name="sms_notifications" <?php echo getChecked($settings, 'sms_notifications'); ?>>
                                            <label class="form-check-label" for="sms_notifications">
                                                <?php echo getTranslatedText('sms_notifications'); ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="results_auto_publish" 
                                                   name="results_auto_publish" <?php echo getChecked($settings, 'results_auto_publish'); ?>>
                                            <label class="form-check-label" for="results_auto_publish">
                                                <?php echo getTranslatedText('auto_publish_results'); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Settings -->
                            <div class="settings-section">
                                <h5 class="section-title"><?php echo getTranslatedText('security_privacy'); ?></h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="two_factor_auth" 
                                                   name="two_factor_auth" <?php echo getChecked($settings, 'two_factor_auth'); ?>>
                                            <label class="form-check-label" for="two_factor_auth">
                                                <?php echo getTranslatedText('two_factor_auth'); ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="profile_visibility" class="form-label"><?php echo getTranslatedText('profile_visibility'); ?></label>
                                        <select class="form-select" id="profile_visibility" name="profile_visibility">
                                            <option value="public" <?php echo getSelected($settings, 'profile_visibility', 'public'); ?>><?php echo getTranslatedText('public'); ?></option>
                                            <option value="students" <?php echo getSelected($settings, 'profile_visibility', 'students'); ?>><?php echo getTranslatedText('students_only'); ?></option>
                                            <option value="faculty" <?php echo getSelected($settings, 'profile_visibility', 'faculty'); ?>><?php echo getTranslatedText('faculty_only'); ?></option>
                                            <option value="private" <?php echo getSelected($settings, 'profile_visibility', 'private'); ?>><?php echo getTranslatedText('private'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="timezone" class="form-label"><?php echo getTranslatedText('timezone'); ?></label>
                                        <select class="form-select" id="timezone" name="timezone">
                                            <option value="Asia/Manila" <?php echo getSelected($settings, 'timezone', 'Asia/Manila'); ?>>Philippine Time (PHT)</option>
                                            <option value="UTC" <?php echo getSelected($settings, 'timezone', 'UTC'); ?>>UTC</option>
                                            <option value="America/New_York" <?php echo getSelected($settings, 'timezone', 'America/New_York'); ?>>Eastern Time (ET)</option>
                                            <option value="Europe/London" <?php echo getSelected($settings, 'timezone', 'Europe/London'); ?>>London Time (GMT)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="settings-section">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-3">
                                            <button type="button" class="btn btn-secondary px-4" onclick="resetToDefault()">
                                                <i class="fas fa-undo me-2"></i><?php echo getTranslatedText('reset_default'); ?>
                                            </button>
                                            <button type="submit" class="btn btn-maroon px-4">
                                                <i class="fas fa-save me-2"></i>
                                                <?php echo getTranslatedText('save_settings'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Theme color selection with REAL-TIME PREVIEW
        function selectTheme(color) {
            // Remove active class from all theme options
            document.querySelectorAll('.theme-color-option').forEach(option => {
                option.classList.remove('active');
            });
            
            // Add active class to selected theme
            event.target.classList.add('active');
            
            // Update hidden input value
            document.getElementById('themeColor').value = color;
            
            // Update theme preview text
            document.getElementById('themePreview').textContent = 'Selected: ' + color.toUpperCase() + ' Theme';
            document.getElementById('themePreview').style.background = getThemeColor(color);
            
            // Show success message
            const currentLang = document.getElementById('language').value;
            const message = currentLang === 'fil' 
                ? 'Napiling tema: ' + color.toUpperCase() + '. Pindutin ang "I-save ang Setting" para ma-apply.' 
                : 'Theme selected: ' + color.toUpperCase() + '. Click "Save Settings" to apply.';
            showTempMessage(message, 'success');
        }

        // Get theme color for preview
        function getThemeColor(theme) {
            const colors = {
                'maroon': '#800000',
                'blue': '#007bff', 
                'green': '#28a745',
                'purple': '#6f42c1',
                'dark': '#343a40'
            };
            return colors[theme] || '#800000';
        }

        // Show temporary message
        function showTempMessage(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert after existing alerts
            const existingAlerts = document.querySelector('.main-content').querySelector('.alert');
            if (existingAlerts) {
                existingAlerts.parentNode.insertBefore(alertDiv, existingAlerts.nextSibling);
            } else {
                document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.main-content').firstChild);
            }
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 3000);
        }

        // Reset to default settings
        function resetToDefault() {
            const currentLang = document.getElementById('language').value;
            const confirmMsg = currentLang === 'fil' 
                ? 'Sigurado ka bang gusto mong i-reset ang lahat ng setting sa default?' 
                : 'Are you sure you want to reset all settings to default?';
            
            if (confirm(confirmMsg)) {
                // Reset theme
                document.querySelectorAll('.theme-color-option').forEach(option => {
                    option.classList.remove('active');
                });
                document.querySelector('.theme-maroon').classList.add('active');
                document.getElementById('themeColor').value = 'maroon';
                document.getElementById('themePreview').textContent = 'Selected: MAROON Theme';
                document.getElementById('themePreview').style.background = '#800000';
                
                // Reset form elements to default values
                document.getElementById('language').value = 'en';
                document.getElementById('default_page').value = 'dashboard';
                document.getElementById('items_per_page').value = '10';
                document.getElementById('notifications_enabled').checked = true;
                document.getElementById('email_notifications').checked = true;
                document.getElementById('sms_notifications').checked = false;
                document.getElementById('results_auto_publish').checked = false;
                document.getElementById('two_factor_auth').checked = false;
                document.getElementById('profile_visibility').value = 'public';
                document.getElementById('timezone').value = 'Asia/Manila';
                
                const successMsg = currentLang === 'fil'
                    ? 'Na-reset na ang lahat ng setting sa default values.'
                    : 'All settings have been reset to default values.';
                showTempMessage(successMsg, 'info');
            }
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

        // Form submission handling
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const currentLang = document.getElementById('language').value;
            const savingMsg = currentLang === 'fil'
                ? 'Sine-save ang setting...'
                : 'Saving settings...';
            showTempMessage(savingMsg, 'info');
        });

        // Add hover effects to theme options
        document.querySelectorAll('.theme-color-option').forEach(option => {
            option.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1.1)';
                }
            });
            
            option.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1)';
                }
            });
        });
    </script>
</body>
</html>