<?php
session_start();

// Debug: Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['username'];
$success_message = '';
$error_message = '';
$edit_mode = false;

// Check if edit mode is requested
if (isset($_GET['edit']) && $_GET['edit'] === 'true') {
    $edit_mode = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data with proper null handling
        $first_name = !empty($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $last_name = !empty($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : '';
        $address = !empty($_POST['address']) ? trim($_POST['address']) : '';
        $bio = !empty($_POST['bio']) ? trim($_POST['bio']) : '';
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        
        // Handle profile picture upload - FIXED FOR WEBP
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            error_log("File upload detected: " . $_FILES['profile_picture']['name']);
            
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/admins/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    error_log("Failed to create directory: " . $upload_dir);
                    $error_message = "Failed to create upload directory.";
                } else {
                    error_log("Created directory: " . $upload_dir);
                }
            }
            
            // Check if directory is writable
            if (!is_writable($upload_dir)) {
                error_log("Directory not writable: " . $upload_dir);
                $error_message = "Upload directory is not writable. Please check permissions.";
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            // ALLOW WEBP FILES
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            error_log("File extension: " . $file_extension);
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['profile_picture']['size'] <= 2097152) { // 2MB
                    $file_name = 'admin_' . $admin_id . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    error_log("Attempting to move file to: " . $file_path);
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                        $profile_picture = $file_path;
                        error_log("File uploaded successfully: " . $profile_picture);
                    } else {
                        error_log("File move failed");
                        $error_message = "Failed to upload profile picture. Please try again.";
                    }
                } else {
                    error_log("File too large: " . $_FILES['profile_picture']['size']);
                    $error_message = "File size must be less than 2MB.";
                }
            } else {
                error_log("Invalid file type: " . $file_extension);
                $error_message = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
            }
        } else {
            $upload_error = $_FILES['profile_picture']['error'] ?? 'No file uploaded';
            error_log("No file uploaded or upload error: " . $upload_error);
            
            // Keep existing profile picture if no new one uploaded
            if ($admin_info && isset($admin_info['profile_picture'])) {
                $profile_picture = $admin_info['profile_picture'];
            }
        }
        
        // Check if admin exists
        $check_stmt = $pdo->prepare("SELECT admin_id, profile_picture FROM admin_profiles WHERE admin_id = ?");
        $check_stmt->execute([$admin_id]);
        $admin_exists = $check_stmt->fetch();
        
        if ($admin_exists) {
            // Update existing admin profile
            if ($profile_picture && $profile_picture !== $admin_exists['profile_picture']) {
                // Delete old profile picture if it exists and is different from new one
                if (!empty($admin_exists['profile_picture']) && 
                    file_exists($admin_exists['profile_picture']) && 
                    $profile_picture !== $admin_exists['profile_picture']) {
                    unlink($admin_exists['profile_picture']);
                }
                
                $stmt = $pdo->prepare("
                    UPDATE admin_profiles 
                    SET first_name = ?, last_name = ?, phone = ?, address = ?, 
                        bio = ?, profile_picture = ?, date_of_birth = ?, gender = ?, updated_at = NOW()
                    WHERE admin_id = ?
                ");
                $result = $stmt->execute([
                    $first_name, $last_name, $phone, $address, 
                    $bio, $profile_picture, $date_of_birth, $gender, $admin_id
                ]);
            } else {
                // Update without changing profile picture
                $stmt = $pdo->prepare("
                    UPDATE admin_profiles 
                    SET first_name = ?, last_name = ?, phone = ?, address = ?, 
                        bio = ?, date_of_birth = ?, gender = ?, updated_at = NOW()
                    WHERE admin_id = ?
                ");
                $result = $stmt->execute([
                    $first_name, $last_name, $phone, $address, 
                    $bio, $date_of_birth, $gender, $admin_id
                ]);
            }
            $success_message = "Profile updated successfully!";
            $edit_mode = false;
        } else {
            // Create new admin profile record
            $stmt = $pdo->prepare("
                INSERT INTO admin_profiles 
                (admin_id, first_name, last_name, phone, address, bio, 
                 profile_picture, date_of_birth, gender) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $admin_id, $first_name, $last_name, $phone, $address, 
                $bio, $profile_picture, $date_of_birth, $gender
            ]);
            $success_message = "Profile created successfully!";
            $edit_mode = false;
        }
        
        // Refresh admin data after update
        $stmt = $pdo->prepare("SELECT * FROM admin_profiles WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        $admin_info = $stmt->fetch();
        
    } catch(PDOException $e) {
        error_log("Admin Profile Error: " . $e->getMessage());
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Get admin data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_profiles WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin_info = $stmt->fetch();
    
} catch(PDOException $e) {
    error_log("Admin Data Fetch Error: " . $e->getMessage());
    $admin_info = null;
}

// Helper functions
function getDisplayName($info) {
    if (!$info) return 'Administrator';
    
    $firstName = $info['first_name'] ?? '';
    $lastName = $info['last_name'] ?? '';
    
    $name = $firstName;
    if (!empty($lastName)) {
        $name .= ' ' . $lastName;
    }
    
    return trim($name) ?: 'Administrator';
}

function getFirstLetter($info) {
    if (!$info) return 'A';
    $name = $info['first_name'] ?? '';
    return !empty($name) ? strtoupper(substr($name, 0, 1)) : 'A';
}

function formatDate($date) {
    if (empty($date) || $date == '0000-00-00') return '';
    return date('F j, Y', strtotime($date));
}

function formatDateInput($date) {
    if (empty($date) || $date == '0000-00-00') return '';
    return date('Y-m-d', strtotime($date));
}

function getValue($info, $key) {
    if (!$info || !isset($info[$key])) return '';
    return htmlspecialchars($info[$key]);
}

function getSelected($info, $key, $value) {
    if (!$info || !isset($info[$key])) return '';
    return $info[$key] == $value ? 'selected' : '';
}

function displayField($label, $value, $emptyText = 'Not set') {
    $displayValue = !empty($value) ? $value : $emptyText;
    $textClass = !empty($value) ? '' : 'text-muted';
    
    echo "
    <div class='row mb-3'>
        <div class='col-md-4 fw-bold'>$label</div>
        <div class='col-md-8 $textClass'>$displayValue</div>
    </div>
    ";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - MSU Buug Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
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
        
        .profile-card {
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
        }
        
        .btn-maroon {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-maroon:hover {
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: white;
        }
        
        .btn-outline-maroon {
            border: 2px solid var(--maroon);
            color: var(--maroon);
            background: transparent;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-outline-maroon:hover {
            background: var(--maroon);
            color: white;
        }
        
        .text-maroon {
            color: var(--maroon) !important;
        }
        
        .profile-picture-container {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto;
        }
        
        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .profile-upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--gold);
            border: none;
            color: var(--maroon);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
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
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
        }
        
        .info-section {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .info-section:last-child {
            border-bottom: none;
        }
        
        .centered-content {
            text-align: center;
        }
        
        .profile-header {
            padding: 30px 20px;
        }
        
        .user-name {
            font-size: 2rem;
            font-weight: 700;
            margin: 20px 0 10px 0;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .user-role {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .profile-info-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .bio-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
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
            
            .profile-picture-container {
                width: 150px;
                height: 150px;
            }
        }
        
        @media (max-width: 768px) {
            .profile-picture-container {
                width: 120px;
                height: 120px;
            }
            
            .user-name {
                font-size: 1.5rem;
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
                <i class="fas fa-user-shield me-2"></i>
                MSU BUUG - Admin Portal
            </a>
            
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <?php echo getFirstLetter($admin_info); ?>
                        </div>
                        <span class="text-white"><?php echo htmlspecialchars(getDisplayName($admin_info)); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item active" href="admin_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="manage_users.php"><i class="fas fa-users me-2"></i>Manage Users</a></li>
                        <li><a class="dropdown-item" href="system_settings.php"><i class="fas fa-cog me-2"></i>System Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt me-3"></i> Dashboard
                    </a>
                    <a class="nav-link" href="manage_students.php">
                        <i class="fas fa-user-graduate me-3"></i> Manage Students
                    </a>
                    <a class="nav-link" href="manage_faculty.php">
                        <i class="fas fa-chalkboard-teacher me-3"></i> Manage Faculty
                    </a>
                    <a class="nav-link" href="manage_courses.php">
                        <i class="fas fa-book me-3"></i> Manage Courses
                    </a>
                    <a class="nav-link" href="manage_classes.php">
                        <i class="fas fa-calendar-alt me-3"></i> Manage Classes
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar me-3"></i> Reports
                    </a>
                    <a class="nav-link active" href="admin_profile.php">
                        <i class="fas fa-user me-3"></i> My Profile
                    </a>
                    <a class="nav-link" href="system_settings.php">
                        <i class="fas fa-cog me-3"></i> System Settings
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

                <div class="profile-card">
                    <?php if (!$edit_mode): ?>
                    <!-- VIEW MODE - Centered Layout -->
                    <div class="card-header">
                        <div class="centered-content profile-header">
                            <div class="profile-picture-container">
                                <?php if (!empty($admin_info['profile_picture']) && file_exists($admin_info['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($admin_info['profile_picture']); ?>?v=<?php echo time(); ?>" 
                                         alt="Profile Picture" class="profile-picture">
                                <?php else: ?>
                                    <div class="profile-picture d-flex align-items-center justify-content-center bg-light text-maroon fw-bold fs-1">
                                        <?php echo getFirstLetter($admin_info); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h1 class="user-name"><?php echo htmlspecialchars(getDisplayName($admin_info)); ?></h1>
                            
                            <p class="user-role">Administrator</p>
                            
                            <a href="?edit=true" class="btn btn-maroon btn-lg">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="profile-info-container">
                            <!-- Personal Information -->
                            <div class="info-section">
                                <h5 class="section-title">Personal Information</h5>
                                <?php
                                displayField('Admin ID', getValue($admin_info, 'admin_id'));
                                displayField('First Name', getValue($admin_info, 'first_name'));
                                displayField('Last Name', getValue($admin_info, 'last_name'));
                                displayField('Gender', getValue($admin_info, 'gender'));
                                displayField('Date of Birth', formatDate($admin_info['date_of_birth'] ?? ''));
                                displayField('Phone Number', getValue($admin_info, 'phone'));
                                ?>
                            </div>

                            <!-- Address Information -->
                            <div class="info-section">
                                <h5 class="section-title">Address Information</h5>
                                <?php
                                displayField('Address', getValue($admin_info, 'address'));
                                ?>
                            </div>

                            <!-- Bio Section -->
                            <?php if (!empty($admin_info['bio'])): ?>
                            <div class="info-section">
                                <h5 class="section-title">About Me</h5>
                                <div class="bio-section">
                                    <p class="mb-0"><?php echo nl2br(getValue($admin_info, 'bio')); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Account Information -->
                            <div class="info-section">
                                <h5 class="section-title">Account Information</h5>
                                <?php
                                displayField('Member Since', formatDate($admin_info['created_at'] ?? ''));
                                displayField('Last Updated', formatDate($admin_info['updated_at'] ?? ''));
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- EDIT MODE - Show Form -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-white"><i class="fas fa-edit me-2"></i>Edit Profile</h4>
                            <a href="admin_profile.php" class="btn btn-outline-light">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" id="profileForm">
                            <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" style="display: none;" onchange="handleFileSelect(this)">
                            
                            <!-- Profile Picture Section -->
                            <div class="centered-content mb-5">
                                <div class="profile-picture-container mb-3">
                                    <?php if (!empty($admin_info['profile_picture']) && file_exists($admin_info['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($admin_info['profile_picture']); ?>?v=<?php echo time(); ?>" 
                                             alt="Profile Picture" class="profile-picture" id="profileImage">
                                    <?php else: ?>
                                        <div class="profile-picture d-flex align-items-center justify-content-center bg-light text-maroon fw-bold fs-1" id="profileImage">
                                            <?php echo getFirstLetter($admin_info); ?>
                                        </div>
                                    <?php endif; ?>
                                    <button type="button" class="profile-upload-btn" onclick="document.getElementById('profilePictureInput').click()">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <button type="button" class="btn btn-outline-maroon" onclick="document.getElementById('profilePictureInput').click()">
                                    <i class="fas fa-camera me-2"></i>Change Profile Picture
                                </button>
                                <small class="form-text text-muted d-block mt-2">Max file size: 2MB. Allowed types: JPG, JPEG, PNG, GIF, WEBP</small>
                                <div id="fileInfo" class="mt-2"></div>
                            </div>

                            <!-- Personal Information Section -->
                            <h5 class="section-title">Personal Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo getValue($admin_info, 'first_name'); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo getValue($admin_info, 'last_name'); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo getSelected($admin_info, 'gender', 'Male'); ?>>Male</option>
                                        <option value="Female" <?php echo getSelected($admin_info, 'gender', 'Female'); ?>>Female</option>
                                        <option value="Other" <?php echo getSelected($admin_info, 'gender', 'Other'); ?>>Other</option>
                                        <option value="Prefer not to say" <?php echo getSelected($admin_info, 'gender', 'Prefer not to say'); ?>>Prefer not to say</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="<?php echo formatDateInput($admin_info['date_of_birth'] ?? ''); ?>">
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <h5 class="section-title">Contact Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo getValue($admin_info, 'phone'); ?>" 
                                           placeholder="Enter phone number">
                                </div>
                            </div>

                            <!-- Address Information -->
                            <h5 class="section-title">Address Information</h5>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              placeholder="Enter your complete address"><?php echo getValue($admin_info, 'address'); ?></textarea>
                                </div>
                            </div>

                            <!-- Bio Section -->
                            <h5 class="section-title">About Me</h5>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="5" 
                                              placeholder="Tell us about yourself..."><?php echo getValue($admin_info, 'bio'); ?></textarea>
                                    <small class="form-text text-muted">Write a brief description about yourself.</small>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="row mt-5">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="admin_profile.php" class="btn btn-secondary px-4">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-maroon px-4">
                                            <i class="fas fa-save me-2"></i>
                                            Update Profile
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
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

        // Profile picture preview and file validation
        function handleFileSelect(input) {
            const fileInfo = document.getElementById('fileInfo');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                
                // Display file information
                fileInfo.innerHTML = `
                    <div class="alert alert-info">
                        <strong>Selected File:</strong> ${file.name}<br>
                        <strong>Size:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
                        <strong>Type:</strong> ${file.type}
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
                
                // Validate file type - ALLOW WEBP
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
                
                reader.onload = function(e) {
                    const profileImage = document.getElementById('profileImage');
                    if (profileImage && profileImage.tagName === 'IMG') {
                        profileImage.src = e.target.result;
                    } else {
                        const profileContainer = document.querySelector('.profile-picture-container');
                        profileContainer.innerHTML = `
                            <img src="${e.target.result}" alt="Profile Picture" class="profile-picture" id="profileImage">
                            <button type="button" class="profile-upload-btn" onclick="document.getElementById('profilePictureInput').click()">
                                <i class="fas fa-camera"></i>
                            </button>
                        `;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                fileInfo.innerHTML = '';
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
    </script>
</body>
</html>