<?php
// SIMPLE SESSION START - NO COMPLICATED SETTINGS
session_start();

// If already logged in, redirect
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['user_type'] === 'faculty') {
    header("Location: faculty_dashboard.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'msubuug_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$login_error = '';
$registration_error = '';
$active_tab = 'login';

// ✅ FIXED: SIMPLE AND CLEAN LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check which form was submitted
    if (isset($_POST['from_registration'])) {
        // REGISTRATION FORM WAS SUBMITTED
        $active_tab = 'signup';
        
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $faculty_id = trim($_POST['faculty_id']);
        $department = $_POST['department'];
        $position = $_POST['position'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Simple validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($faculty_id)) {
            $registration_error = "All fields are required!";
        } elseif ($password !== $confirm_password) {
            $registration_error = "Passwords do not match!";
        } elseif (strlen($password) < 6) {
            $registration_error = "Password must be at least 6 characters long!";
        } else {
            try {
                // Check if faculty ID exists
                $checkStmt = $pdo->prepare("SELECT faculty_id FROM faculty WHERE faculty_id = ?");
                $checkStmt->execute([$faculty_id]);
                
                if ($checkStmt->fetch()) {
                    $registration_error = "Faculty ID '$faculty_id' already exists!";
                } else {
                    // Check if email exists
                    $checkEmailStmt = $pdo->prepare("SELECT email FROM faculty WHERE email = ?");
                    $checkEmailStmt->execute([$email]);
                    
                    if ($checkEmailStmt->fetch()) {
                        $registration_error = "Email '$email' already exists!";
                    } else {
                        // Insert new faculty
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        $sql = "INSERT INTO faculty (faculty_id, first_name, last_name, email, department, position, password) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        
                        $stmt = $pdo->prepare($sql);
                        $result = $stmt->execute([$faculty_id, $first_name, $last_name, $email, $department, $position, $hashed_password]);
                        
                        if ($result) {
                            // Get fresh data
                            $freshStmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
                            $freshStmt->execute([$faculty_id]);
                            $new_faculty = $freshStmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($new_faculty) {
                                // Set session
                                $_SESSION['loggedin'] = true;
                                $_SESSION['user_type'] = 'faculty';
                                $_SESSION['username'] = $new_faculty['faculty_id'];
                                $_SESSION['user_info'] = [
                                    'name' => $new_faculty['first_name'] . ' ' . $new_faculty['last_name'],
                                    'department' => $new_faculty['department'],
                                    'position' => $new_faculty['position'],
                                    'email' => $new_faculty['email']
                                ];
                                
                                header("Location: faculty_dashboard.php");
                                exit();
                            }
                        } else {
                            $registration_error = "Registration failed! Please try again.";
                        }
                    }
                }
            } catch (PDOException $e) {
                $registration_error = "Database error: " . $e->getMessage();
            }
        }
        
    } else {
        // LOGIN FORM WAS SUBMITTED
        $active_tab = 'login';
        
        $username = trim($_POST['username']);
        $input_password = trim($_POST['password']);
        
        if (empty($username) || empty($input_password)) {
            $login_error = "Faculty ID and password are required!";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
                $stmt->execute([$username]);
                $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($faculty) {
                    // Check password
                    if (password_verify($input_password, $faculty['password']) || $input_password === $faculty['password']) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['user_type'] = 'faculty';
                        $_SESSION['username'] = $faculty['faculty_id'];
                        $_SESSION['user_info'] = [
                            'name' => $faculty['first_name'] . ' ' . $faculty['last_name'],
                            'department' => $faculty['department'],
                            'position' => $faculty['position'],
                            'email' => $faculty['email']
                        ];
                        
                        header("Location: faculty_dashboard.php");
                        exit();
                    } else {
                        $login_error = "Invalid faculty ID or password!";
                    }
                } else {
                    $login_error = "Invalid faculty ID or password!";
                }
            } catch (PDOException $e) {
                $login_error = "Database error during login.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --maroon-light: #a30000;
            --gold: #FFD700;
            --green: #28a745;
        }
        
        body {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 15px;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border: 2px solid var(--gold);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            color: white;
            padding: 20px 15px;
            text-align: center;
            position: relative;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gold);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        
        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px solid var(--gold);
            object-fit: cover;
            background: white;
            padding: 4px;
        }
        
        .university-name {
            text-align: center;
        }
        
        .university-name h4 {
            margin: 0;
            font-weight: bold;
            color: var(--gold);
            font-size: 1.3rem;
        }
        
        .university-name p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 25px 20px;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 25px;
        }
        
        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
            border: none;
            padding: 12px 25px;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--maroon);
            border-bottom: 3px solid var(--maroon);
            background: none;
            font-weight: 600;
        }
        
        .tab-content {
            min-height: 400px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--maroon);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-light));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--green), #1e7e34);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <div class="logo-container">
                    <img src="msu-logo.png" alt="MSU Logo" class="logo-img" onerror="this.style.display='none'">
                    <div class="university-name">
                        <h4>MSU BUUG</h4>
                        <p>Faculty Portal</p>
                    </div>
                </div>
                <p class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Faculty Access Portal</p>
            </div>
            
            <div class="login-body">
                <ul class="nav nav-tabs" id="authTabs">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="login-tab" data-bs-toggle="tab" href="#login">
                            <i class="fas fa-sign-in-alt me-2"></i>Faculty Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_tab === 'signup' ? 'active' : ''; ?>" id="signup-tab" data-bs-toggle="tab" href="#signup">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Login Tab -->
                    <div class="tab-pane fade <?php echo $active_tab === 'login' ? 'show active' : ''; ?>" id="login">
                        <form method="POST" action="" id="facultyLoginForm">
                            <?php if (!empty($login_error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($login_error); ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Use your faculty credentials to login
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-id-card"></i>
                                    Faculty ID
                                </label>
                                <input type="text" class="form-control" name="username" required 
                                       placeholder="Enter your Faculty ID">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Password
                                </label>
                                <input type="password" class="form-control" name="password" required 
                                       placeholder="Enter your password">
                            </div>
                            
                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In to Faculty Portal
                            </button>
                        </form>
                    </div>
                    
                    <!-- Registration Tab -->
                    <div class="tab-pane fade <?php echo $active_tab === 'signup' ? 'show active' : ''; ?>" id="signup">
                        <form method="POST" action="" id="facultyRegisterForm">
                            <input type="hidden" name="from_registration" value="true">
                            
                            <?php if (!empty($registration_error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($registration_error); ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Fill out the form to create your faculty account
                            </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-user"></i>
                                            First Name
                                        </label>
                                        <input type="text" class="form-control" name="first_name" required placeholder="Enter first name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-user"></i>
                                            Last Name
                                        </label>
                                        <input type="text" class="form-control" name="last_name" required placeholder="Enter last name">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" class="form-control" name="email" required placeholder="Enter email address">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-id-card"></i>
                                    Faculty ID
                                </label>
                                <input type="text" class="form-control" name="faculty_id" required 
                                       placeholder="e.g., FAC-001">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-building"></i>
                                            Department
                                        </label>
                                        <select class="form-control" name="department" required>
                                            <option value="">Select Department</option>
                                            <option value="College of Information Technology">College of Information Technology</option>
                                            <option value="College of Hospitality Management">College of Hospitality Management</option>
                                            <option value="College of Education">College of Education</option>
                                            <option value="College of Business Administration">College of Business Administration</option>
                                            <option value="College of Nursing">College of Nursing</option>
                                            <option value="College of Agriculture">College of Agriculture</option>
                                            <option value="College of Arts & Sciences">College of Arts and Sciences</option>
                                            <option value="College of Forestry and Environmental Studies">College of Foresty and Environmental Studies</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-briefcase"></i>
                                            Position
                                        </label>
                                        <select class="form-control" name="position" required>
                                            <option value="">Select Position</option>
                                            <option value="Professor">Acting Dean</option>
                                            <option value="Associate Professor">Program Chairman</option>
                                            <option value="Assistant Professor">College Secretary</option>
                                            <option value="Instructor">Faculty</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-lock"></i>
                                            Password
                                        </label>
                                        <input type="password" class="form-control" name="password" required placeholder="Enter password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-lock"></i>
                                            Confirm Password
                                        </label>
                                        <input type="password" class="form-control" name="confirm_password" required placeholder="Confirm password">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-register">
                                <i class="fas fa-user-plus me-2"></i>Create Faculty Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple form handlers
        document.getElementById('facultyLoginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Signing In...';
            submitBtn.disabled = true;
        });

        document.getElementById('facultyRegisterForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating Account...';
            submitBtn.disabled = true;
        });

        // Auto-switch tabs
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = '<?php echo $active_tab; ?>';
            if (activeTab === 'signup') {
                const signupTab = new bootstrap.Tab(document.getElementById('signup-tab'));
                signupTab.show();
            }
        });
    </script>
</body>
</html>