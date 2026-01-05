<?php
// system_settings.php - COMPLETE SYSTEM SETTINGS MANAGEMENT MODULE
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

// Sample system settings data (replace with database)
$system_settings = [
    'general' => [
        'system_name' => 'MSU Buug Campus Management System',
        'system_version' => 'v2.1.0',
        'institution_name' => 'Mindanao State University - Buug Campus',
        'institution_address' => 'Buug, Zamboanga Sibugay, Philippines',
        'institution_phone' => '(062) 123-4567',
        'institution_email' => 'info@msubuug.edu.ph',
        'academic_year' => '2023-2024',
        'current_semester' => '1st Semester',
        'timezone' => 'Asia/Manila',
        'date_format' => 'Y-m-d',
        'time_format' => '12-hour',
        'language' => 'en',
        'maintenance_mode' => false
    ],
    'academic' => [
        'max_units_per_semester' => 24,
        'min_units_per_semester' => 12,
        'grading_system' => 'percentage',
        'passing_grade' => 75.0,
        'allow_grade_override' => true,
        'auto_promote_students' => false,
        'enrollment_deadline' => '2024-01-30',
        'grade_submission_deadline' => '2024-03-30'
    ],
    'email' => [
        'smtp_host' => 'smtp.msubuug.edu.ph',
        'smtp_port' => 587,
        'smtp_username' => 'noreply@msubuug.edu.ph',
        'smtp_password' => 'encrypted_password',
        'smtp_encryption' => 'tls',
        'from_email' => 'noreply@msubuug.edu.ph',
        'from_name' => 'MSU Buug Campus',
        'email_notifications' => true,
        'student_notifications' => true,
        'instructor_notifications' => true
    ],
    'security' => [
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numbers' => true,
        'password_require_special' => true,
        'password_expiry_days' => 90,
        'max_login_attempts' => 5,
        'lockout_duration' => 30,
        'session_timeout' => 60,
        'two_factor_auth' => false,
        'ip_whitelist' => '',
        'login_audit_log' => true
    ],
    'backup' => [
        'auto_backup' => true,
        'backup_frequency' => 'daily',
        'backup_time' => '02:00',
        'backup_retention' => 30,
        'backup_database' => true,
        'backup_files' => true,
        'backup_location' => 'local',
        'last_backup' => '2024-01-28 02:15:00',
        'backup_status' => 'completed'
    ],
    'appearance' => [
        'theme' => 'default',
        'primary_color' => '#800000',
        'secondary_color' => '#5a0000',
        'accent_color' => '#FFD700',
        'logo' => 'msu_logo.png',
        'favicon' => 'favicon.ico',
        'login_background' => 'default.jpg',
        'custom_css' => '',
        'sidebar_collapsed' => false
    ]
];

// Sample options for dropdowns
$timezones = [
    'Asia/Manila' => 'Manila, Philippines (UTC+8)',
    'Asia/Singapore' => 'Singapore (UTC+8)',
    'Asia/Tokyo' => 'Tokyo, Japan (UTC+9)',
    'America/New_York' => 'New York, USA (UTC-5)',
    'Europe/London' => 'London, UK (UTC+0)'
];

$date_formats = [
    'Y-m-d' => 'YYYY-MM-DD (2024-01-30)',
    'm/d/Y' => 'MM/DD/YYYY (01/30/2024)',
    'd/m/Y' => 'DD/MM/YYYY (30/01/2024)',
    'F j, Y' => 'Month Day, Year (January 30, 2024)'
];

$time_formats = [
    '12-hour' => '12-hour (2:30 PM)',
    '24-hour' => '24-hour (14:30)'
];

$languages = [
    'en' => 'English',
    'fil' => 'Filipino',
    'es' => 'Spanish'
];

$grading_systems = [
    'percentage' => 'Percentage (0-100%)',
    'letter_grade' => 'Letter Grade (A-F)',
    '4_point' => '4-Point Scale (0.0-4.0)',
    'pass_fail' => 'Pass/Fail'
];

$backup_frequencies = [
    'daily' => 'Daily',
    'weekly' => 'Weekly',
    'monthly' => 'Monthly'
];

$backup_locations = [
    'local' => 'Local Server',
    'google_drive' => 'Google Drive',
    'dropbox' => 'Dropbox',
    'aws' => 'Amazon S3'
];

$themes = [
    'default' => 'Default (Maroon & Gold)',
    'dark' => 'Dark Mode',
    'light' => 'Light Mode',
    'blue' => 'Blue Theme',
    'green' => 'Green Theme'
];

// Calculate system statistics
$total_settings = count($system_settings, COUNT_RECURSIVE) - count($system_settings);
$active_settings = array_reduce($system_settings, function($carry, $category) {
    return $carry + count(array_filter($category, function($value) {
        return $value === true || (is_string($value) && !empty($value));
    }));
}, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>System Settings - MSU Buug</title>
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
        
        /* System Settings Specific Styles */
        .settings-card { 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); 
            margin-bottom: 20px; 
            padding: 1.5rem;
            border-left: 4px solid #800000;
        }
        .settings-card .card-header {
            background: transparent;
            border-bottom: 1px solid #e9ecef;
            padding: 0 0 1rem 0;
            margin-bottom: 1rem;
        }
        .settings-section {
            margin-bottom: 2rem;
        }
        .settings-section:last-child {
            margin-bottom: 0;
        }
        .form-switch .form-check-input:checked {
            background-color: #800000;
            border-color: #800000;
        }
        .settings-group {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .settings-group h6 {
            color: #800000;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        /* Status indicators */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-active { background: #28a745; }
        .status-inactive { background: #dc3545; }
        .status-warning { background: #ffc107; }
        
        /* Quick stats cards */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #800000;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #800000;
            margin: 10px 0;
        }
        
        /* Tab navigation */
        .settings-tabs .nav-link {
            color: #495057;
            border: none;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
        }
        .settings-tabs .nav-link.active {
            background: #800000;
            color: white;
            border: none;
        }
        .settings-tabs .nav-link:hover {
            background: #5a0000;
            color: white;
        }
        
        /* System status indicators */
        .system-status {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .system-status.good {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .system-status.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .system-status.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }

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
                <i class="fas fa-cogs me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - System Settings</span>
                <span class="d-inline d-sm-none">System Settings</span>
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
                        <li><a class="dropdown-item text-danger" href="logout_admin.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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

            <a href="fines_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fines</a>
            <a href="reports_management.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link active"><i class="fas fa-cogs me-2"></i> System Settings</a>
          
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">System Settings & Configuration</h2>
                    <p class="mb-1">Manage system-wide settings, configurations, and preferences</p>
                    <small>System: <?php echo htmlspecialchars($system_settings['general']['system_name']); ?> | Version: <?php echo htmlspecialchars($system_settings['general']['system_version']); ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="number"><?php echo $total_settings; ?></div>
                    <p class="small">Total Settings</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="number"><?php echo $active_settings; ?></div>
                    <p class="small">Active Settings</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="number">6</div>
                    <p class="small">Categories</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="number"><?php echo date('M j, Y', strtotime($system_settings['backup']['last_backup'])); ?></div>
                    <p class="small">Last Backup</p>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="dashboard-card mb-4">
            <div class="card-body">
                <h5 class="text-msu mb-3">System Status</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="system-status good">
                            <div class="d-flex align-items-center">
                                <span class="status-indicator status-active me-2"></span>
                                <strong>System Online</strong>
                            </div>
                            <small class="text-muted">All services are running normally</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="system-status <?php echo $system_settings['backup']['auto_backup'] ? 'good' : 'warning'; ?>">
                            <div class="d-flex align-items-center">
                                <span class="status-indicator <?php echo $system_settings['backup']['auto_backup'] ? 'status-active' : 'status-warning'; ?> me-2"></span>
                                <strong>Backup Status</strong>
                            </div>
                            <small class="text-muted">
                                <?php echo $system_settings['backup']['auto_backup'] ? 'Auto-backup enabled' : 'Auto-backup disabled'; ?>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="system-status <?php echo $system_settings['general']['maintenance_mode'] ? 'warning' : 'good'; ?>">
                            <div class="d-flex align-items-center">
                                <span class="status-indicator <?php echo $system_settings['general']['maintenance_mode'] ? 'status-warning' : 'status-active'; ?> me-2"></span>
                                <strong>Maintenance Mode</strong>
                            </div>
                            <small class="text-muted">
                                <?php echo $system_settings['general']['maintenance_mode'] ? 'Enabled' : 'Disabled'; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Navigation Tabs -->
        <div class="dashboard-card mb-4">
            <div class="card-body">
                <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-cog me-2"></i>General
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">
                            <i class="fas fa-graduation-cap me-2"></i>Academic
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                            <i class="fas fa-envelope me-2"></i>Email
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                            <i class="fas fa-shield-alt me-2"></i>Security
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                            <i class="fas fa-database me-2"></i>Backup
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab">
                            <i class="fas fa-palette me-2"></i>Appearance
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-4" id="settingsTabsContent">
                    
                    <!-- General Settings Tab -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form id="generalSettingsForm">
                            <div class="settings-group">
                                <h6><i class="fas fa-info-circle me-2"></i>System Information</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">System Name</label>
                                        <input type="text" class="form-control" name="system_name" value="<?php echo htmlspecialchars($system_settings['general']['system_name']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Institution Name</label>
                                        <input type="text" class="form-control" name="institution_name" value="<?php echo htmlspecialchars($system_settings['general']['institution_name']); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Institution Address</label>
                                        <textarea class="form-control" name="institution_address" rows="2"><?php echo htmlspecialchars($system_settings['general']['institution_address']); ?></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Information</label>
                                        <input type="text" class="form-control mb-2" name="institution_phone" value="<?php echo htmlspecialchars($system_settings['general']['institution_phone']); ?>" placeholder="Phone">
                                        <input type="email" class="form-control" name="institution_email" value="<?php echo htmlspecialchars($system_settings['general']['institution_email']); ?>" placeholder="Email">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-calendar-alt me-2"></i>Academic Period</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Academic Year</label>
                                        <select class="form-select" name="academic_year">
                                            <?php for($year = 2020; $year <= 2030; $year++): ?>
                                                <?php $ay = $year . '-' . ($year + 1); ?>
                                                <option value="<?php echo $ay; ?>" <?php echo $system_settings['general']['academic_year'] === $ay ? 'selected' : ''; ?>>
                                                    <?php echo $ay; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Semester</label>
                                        <select class="form-select" name="current_semester">
                                            <option value="1st Semester" <?php echo $system_settings['general']['current_semester'] === '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                                            <option value="2nd Semester" <?php echo $system_settings['general']['current_semester'] === '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                                            <option value="Summer" <?php echo $system_settings['general']['current_semester'] === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-globe me-2"></i>Regional Settings</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Timezone</label>
                                        <select class="form-select" name="timezone">
                                            <?php foreach ($timezones as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['general']['timezone'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Date Format</label>
                                        <select class="form-select" name="date_format">
                                            <?php foreach ($date_formats as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['general']['date_format'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Time Format</label>
                                        <select class="form-select" name="time_format">
                                            <?php foreach ($time_formats as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['general']['time_format'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Language</label>
                                        <select class="form-select" name="language">
                                            <?php foreach ($languages as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['general']['language'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">System Mode</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="maintenance_mode" <?php echo $system_settings['general']['maintenance_mode'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Maintenance Mode</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetGeneralSettings()">Reset</button>
                                <button type="submit" class="btn btn-msu">Save General Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- Academic Settings Tab -->
                    <div class="tab-pane fade" id="academic" role="tabpanel">
                        <form id="academicSettingsForm">
                            <div class="settings-group">
                                <h6><i class="fas fa-book me-2"></i>Course & Units</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Maximum Units per Semester</label>
                                        <input type="number" class="form-control" name="max_units_per_semester" value="<?php echo htmlspecialchars($system_settings['academic']['max_units_per_semester']); ?>" min="1" max="30">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Minimum Units per Semester</label>
                                        <input type="number" class="form-control" name="min_units_per_semester" value="<?php echo htmlspecialchars($system_settings['academic']['min_units_per_semester']); ?>" min="1" max="30">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-chart-line me-2"></i>Grading System</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Grading System</label>
                                        <select class="form-select" name="grading_system">
                                            <?php foreach ($grading_systems as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['academic']['grading_system'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Passing Grade</label>
                                        <input type="number" class="form-control" name="passing_grade" value="<?php echo htmlspecialchars($system_settings['academic']['passing_grade']); ?>" step="0.1" min="0" max="100">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="allow_grade_override" <?php echo $system_settings['academic']['allow_grade_override'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Allow Grade Override by Administrators</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="auto_promote_students" <?php echo $system_settings['academic']['auto_promote_students'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Auto-promote Students</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-clock me-2"></i>Deadlines</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Enrollment Deadline</label>
                                        <input type="date" class="form-control" name="enrollment_deadline" value="<?php echo htmlspecialchars($system_settings['academic']['enrollment_deadline']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Grade Submission Deadline</label>
                                        <input type="date" class="form-control" name="grade_submission_deadline" value="<?php echo htmlspecialchars($system_settings['academic']['grade_submission_deadline']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetAcademicSettings()">Reset</button>
                                <button type="submit" class="btn btn-msu">Save Academic Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- Email Settings Tab -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <form id="emailSettingsForm">
                            <div class="settings-group">
                                <h6><i class="fas fa-server me-2"></i>SMTP Configuration</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" name="smtp_host" value="<?php echo htmlspecialchars($system_settings['email']['smtp_host']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" name="smtp_port" value="<?php echo htmlspecialchars($system_settings['email']['smtp_port']); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" name="smtp_username" value="<?php echo htmlspecialchars($system_settings['email']['smtp_username']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">SMTP Password</label>
                                        <input type="password" class="form-control" name="smtp_password" value="<?php echo htmlspecialchars($system_settings['email']['smtp_password']); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Encryption</label>
                                        <select class="form-select" name="smtp_encryption">
                                            <option value="tls" <?php echo $system_settings['email']['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                            <option value="ssl" <?php echo $system_settings['email']['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="none" <?php echo $system_settings['email']['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>None</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">From Email</label>
                                        <input type="email" class="form-control" name="from_email" value="<?php echo htmlspecialchars($system_settings['email']['from_email']); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">From Name</label>
                                        <input type="text" class="form-control" name="from_name" value="<?php echo htmlspecialchars($system_settings['email']['from_name']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="mt-4">
                                            <button type="button" class="btn btn-outline-msu" onclick="testEmailConfiguration()">
                                                <i class="fas fa-paper-plane me-1"></i> Test Email Configuration
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-bell me-2"></i>Email Notifications</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="email_notifications" <?php echo $system_settings['email']['email_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Enable Email Notifications</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="student_notifications" <?php echo $system_settings['email']['student_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Student Notifications</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="instructor_notifications" <?php echo $system_settings['email']['instructor_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Instructor Notifications</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetEmailSettings()">Reset</button>
                                <button type="submit" class="btn btn-msu">Save Email Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Settings Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <form id="securitySettingsForm">
                            <div class="settings-group">
                                <h6><i class="fas fa-lock me-2"></i>Password Policy</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Minimum Password Length</label>
                                        <input type="number" class="form-control" name="password_min_length" value="<?php echo htmlspecialchars($system_settings['security']['password_min_length']); ?>" min="6" max="20">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password Expiry (Days)</label>
                                        <input type="number" class="form-control" name="password_expiry_days" value="<?php echo htmlspecialchars($system_settings['security']['password_expiry_days']); ?>" min="0" max="365">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="password_require_uppercase" <?php echo $system_settings['security']['password_require_uppercase'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Uppercase Letters</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="password_require_lowercase" <?php echo $system_settings['security']['password_require_lowercase'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Lowercase Letters</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="password_require_numbers" <?php echo $system_settings['security']['password_require_numbers'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Numbers</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="password_require_special" <?php echo $system_settings['security']['password_require_special'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Special Characters</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-user-shield me-2"></i>Login Security</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Max Login Attempts</label>
                                        <input type="number" class="form-control" name="max_login_attempts" value="<?php echo htmlspecialchars($system_settings['security']['max_login_attempts']); ?>" min="1" max="10">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Lockout Duration (Minutes)</label>
                                        <input type="number" class="form-control" name="lockout_duration" value="<?php echo htmlspecialchars($system_settings['security']['lockout_duration']); ?>" min="1" max="1440">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Session Timeout (Minutes)</label>
                                        <input type="number" class="form-control" name="session_timeout" value="<?php echo htmlspecialchars($system_settings['security']['session_timeout']); ?>" min="1" max="480">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-shield-alt me-2"></i>Advanced Security</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="two_factor_auth" <?php echo $system_settings['security']['two_factor_auth'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Enable Two-Factor Authentication</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="login_audit_log" <?php echo $system_settings['security']['login_audit_log'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Enable Login Audit Log</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">IP Whitelist (Optional)</label>
                                        <textarea class="form-control" name="ip_whitelist" rows="3" placeholder="Enter IP addresses separated by commas"><?php echo htmlspecialchars($system_settings['security']['ip_whitelist']); ?></textarea>
                                        <small class="form-text text-muted">Leave empty to allow all IP addresses</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetSecuritySettings()">Reset</button>
                                <button type="submit" class="btn btn-msu">Save Security Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- Backup Settings Tab -->
                    <div class="tab-pane fade" id="backup" role="tabpanel">
                        <form id="backupSettingsForm">
                            <div class="settings-group">
                                <h6><i class="fas fa-robot me-2"></i>Auto Backup Configuration</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="auto_backup" <?php echo $system_settings['backup']['auto_backup'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Enable Auto Backup</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Backup Frequency</label>
                                        <select class="form-select" name="backup_frequency">
                                            <?php foreach ($backup_frequencies as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['backup']['backup_frequency'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Backup Time</label>
                                        <input type="time" class="form-control" name="backup_time" value="<?php echo htmlspecialchars($system_settings['backup']['backup_time']); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Backup Retention (Days)</label>
                                        <input type="number" class="form-control" name="backup_retention" value="<?php echo htmlspecialchars($system_settings['backup']['backup_retention']); ?>" min="1" max="365">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Backup Location</label>
                                        <select class="form-select" name="backup_location">
                                            <?php foreach ($backup_locations as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['backup']['backup_location'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-hdd me-2"></i>Backup Content</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="backup_database" <?php echo $system_settings['backup']['backup_database'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Backup Database</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="backup_files" <?php echo $system_settings['backup']['backup_files'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Backup Files & Documents</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-tools me-2"></i>Backup Actions</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <button type="button" class="btn btn-outline-msu w-100" onclick="createBackupNow()">
                                            <i class="fas fa-plus me-1"></i> Create Backup Now
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button type="button" class="btn btn-outline-success w-100" onclick="restoreFromBackup()">
                                            <i class="fas fa-undo me-1"></i> Restore Backup
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button type="button" class="btn btn-outline-info w-100" onclick="viewBackupHistory()">
                                            <i class="fas fa-history me-1"></i> View History
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <strong>Last Backup:</strong> 
                                            <?php echo date('F j, Y g:i A', strtotime($system_settings['backup']['last_backup'])); ?> 
                                            | <strong>Status:</strong> 
                                            <span class="badge badge-<?php echo $system_settings['backup']['backup_status']; ?>">
                                                <?php echo ucfirst($system_settings['backup']['backup_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetBackupSettings()">Reset</button>
                                <button type="submit" class="btn btn-msu">Save Backup Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- Appearance Settings Tab -->
                    <div class="tab-pane fade" id="appearance" role="tabpanel">
                        <form id="appearanceSettingsForm">
                            <div class="settings-group">
                                <h6><i class="fas fa-paint-brush me-2"></i>Theme & Colors</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Theme</label>
                                        <select class="form-select" name="theme">
                                            <?php foreach ($themes as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $system_settings['appearance']['theme'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Accent Color</label>
                                        <div class="input-group color-picker">
                                            <input type="color" class="form-control form-control-color" name="accent_color" value="<?php echo htmlspecialchars($system_settings['appearance']['accent_color']); ?>" title="Choose accent color">
                                            <span class="input-group-text"><?php echo htmlspecialchars($system_settings['appearance']['accent_color']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Primary Color</label>
                                        <div class="input-group color-picker">
                                            <input type="color" class="form-control form-control-color" name="primary_color" value="<?php echo htmlspecialchars($system_settings['appearance']['primary_color']); ?>" title="Choose primary color">
                                            <span class="input-group-text"><?php echo htmlspecialchars($system_settings['appearance']['primary_color']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Secondary Color</label>
                                        <div class="input-group color-picker">
                                            <input type="color" class="form-control form-control-color" name="secondary_color" value="<?php echo htmlspecialchars($system_settings['appearance']['secondary_color']); ?>" title="Choose secondary color">
                                            <span class="input-group-text"><?php echo htmlspecialchars($system_settings['appearance']['secondary_color']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-image me-2"></i>Branding & Images</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Logo</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" name="logo" accept="image/*">
                                            <button type="button" class="btn btn-outline-secondary" onclick="previewLogo()">Preview</button>
                                        </div>
                                        <small class="form-text text-muted">Current: <?php echo htmlspecialchars($system_settings['appearance']['logo']); ?></small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Favicon</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" name="favicon" accept="image/x-icon,image/png">
                                            <button type="button" class="btn btn-outline-secondary" onclick="previewFavicon()">Preview</button>
                                        </div>
                                        <small class="form-text text-muted">Current: <?php echo htmlspecialchars($system_settings['appearance']['favicon']); ?></small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Login Background</label>
                                        <select class="form-select" name="login_background">
                                            <option value="default.jpg" <?php echo $system_settings['appearance']['login_background'] === 'default.jpg' ? 'selected' : ''; ?>>Default Background</option>
                                            <option value="campus.jpg" <?php echo $system_settings['appearance']['login_background'] === 'campus.jpg' ? 'selected' : ''; ?>>Campus View</option>
                                            <option value="custom.jpg" <?php echo $system_settings['appearance']['login_background'] === 'custom.jpg' ? 'selected' : ''; ?>>Custom Image</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-group">
                                <h6><i class="fas fa-code me-2"></i>Customization</h6>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Custom CSS</label>
                                        <textarea class="form-control" name="custom_css" rows="6" placeholder="Enter custom CSS code"><?php echo htmlspecialchars($system_settings['appearance']['custom_css']); ?></textarea>
                                        <small class="form-text text-muted">Add custom CSS to override default styles</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="sidebar_collapsed" <?php echo $system_settings['appearance']['sidebar_collapsed'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Collapse Sidebar by Default</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetAppearanceSettings()">Reset</button>
                                <button type="submit" class="btn btn-msu">Save Appearance Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Form submission handlers
        document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings('general', this);
        });

        document.getElementById('academicSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings('academic', this);
        });

        document.getElementById('emailSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings('email', this);
        });

        document.getElementById('securitySettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings('security', this);
        });

        document.getElementById('backupSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings('backup', this);
        });

        document.getElementById('appearanceSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings('appearance', this);
        });

        function saveSettings(category, form) {
            const formData = new FormData(form);
            const settings = Object.fromEntries(formData);
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            submitBtn.disabled = true;

            // Simulate API call
            setTimeout(() => {
                alert(`${category.charAt(0).toUpperCase() + category.slice(1)} settings saved successfully!`);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Update system status indicators if needed
                if (category === 'backup' || category === 'general') {
                    location.reload(); // Reload to update status indicators
                }
            }, 1500);
        }

        // Reset functions
        function resetGeneralSettings() {
            if (confirm('Are you sure you want to reset all general settings to their default values?')) {
                document.getElementById('generalSettingsForm').reset();
            }
        }

        function resetAcademicSettings() {
            if (confirm('Are you sure you want to reset all academic settings to their default values?')) {
                document.getElementById('academicSettingsForm').reset();
            }
        }

        function resetEmailSettings() {
            if (confirm('Are you sure you want to reset all email settings to their default values?')) {
                document.getElementById('emailSettingsForm').reset();
            }
        }

        function resetSecuritySettings() {
            if (confirm('Are you sure you want to reset all security settings to their default values?')) {
                document.getElementById('securitySettingsForm').reset();
            }
        }

        function resetBackupSettings() {
            if (confirm('Are you sure you want to reset all backup settings to their default values?')) {
                document.getElementById('backupSettingsForm').reset();
            }
        }

        function resetAppearanceSettings() {
            if (confirm('Are you sure you want to reset all appearance settings to their default values?')) {
                document.getElementById('appearanceSettingsForm').reset();
            }
        }

        // Email test function
        function testEmailConfiguration() {
            alert('Testing email configuration... This would send a test email in a real application.');
        }

        // Backup functions
        function createBackupNow() {
            if (confirm('Are you sure you want to create a backup now? This may take several minutes.')) {
                alert('Backup process started... You will be notified when it is complete.');
            }
        }

        function restoreFromBackup() {
            alert('This would open a backup restoration dialog in a real application.');
        }

        function viewBackupHistory() {
            alert('This would show backup history in a real application.');
        }

        // Preview functions
        function previewLogo() {
            alert('This would show a preview of the selected logo.');
        }

        function previewFavicon() {
            alert('This would show a preview of the selected favicon.');
        }

        // Color picker updates
        document.querySelectorAll('.color-picker input[type="color"]').forEach(picker => {
            picker.addEventListener('input', function() {
                const display = this.parentElement.querySelector('.input-group-text');
                display.textContent = this.value;
            });
        });

        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Set active link based on current page
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
    </script>
</body>
</html>