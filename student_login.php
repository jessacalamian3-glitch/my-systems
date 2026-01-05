<?php
// ==================== SESSION FIXES - MUST BE BEFORE session_start() ====================
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.cookie_lifetime', 7200);
session_set_cookie_params(7200);

// NOW START THE SESSION
session_start();
// ==================== END SESSION FIXES ====================

// If already logged in as student, redirect to dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['user_type'] === 'student') {
    header("Location: student_dashboard.php");
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

// Rate limiting
$login_attempts_key = 'login_attempts_' . ($_SERVER['REMOTE_ADDR'] ?? '');
if (!isset($_SESSION[$login_attempts_key])) {
    $_SESSION[$login_attempts_key] = 0;
}

// Login handling - STUDENT ONLY
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION[$login_attempts_key] >= 5) {
        $login_error = "Too many login attempts. Please try again in 30 minutes.";
    } else {
        $student_id = trim($_POST['student_id']);
        $password = trim($_POST['password']);
        
        // Input validation
        if (empty($student_id) || empty($password)) {
            $login_error = "Student ID and password are required!";
        } elseif (!preg_match('/^[a-zA-Z0-9\-_]+$/', $student_id)) {
            $login_error = "Invalid Student ID format!";
        } else {
            try {
                // Check if student exists
                $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
                $stmt->execute([$student_id]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($student) {
                    $password_valid = false;
                    $password_updated = false;
                    
                    // Enhanced password verification with AUTO-CONVERSION
                    if (isset($student['password'])) {
                        // Check if password is already hashed
                        if (strpos($student['password'], '$2y$') === 0) {
                            // Password is hashed - verify normally
                            if (password_verify($password, $student['password'])) {
                                $password_valid = true;
                            }
                        } else {
                            // Password is plain text - check match and AUTO-CONVERT
                            if ($password === $student['password']) {
                                $password_valid = true;
                                $password_updated = true;
                                
                                // AUTO-CONVERT to hash for security
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $update_stmt = $pdo->prepare("UPDATE students SET password = ? WHERE student_id = ?");
                                $update_stmt->execute([$hashed_password, $student_id]);
                                
                                error_log("Password auto-converted to hash for student: " . $student_id);
                            }
                        }
                    }
                    
                    if ($password_valid) {
                        // Reset login attempts
                        $_SESSION[$login_attempts_key] = 0;
                        
                        // Set session variables
                        $_SESSION['loggedin'] = true;
                        $_SESSION['user_type'] = 'student';
                        $_SESSION['username'] = $student_id;
                        $_SESSION['user_id'] = $student_id;
                        $_SESSION['last_activity'] = time(); // Session tracking
                        
                        // Set student info
                        $_SESSION['user_info'] = [
                            'name' => ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''),
                            'course' => $student['course'] ?? '',
                            'year_level' => $student['year_level'] ?? '',
                            'student_id' => $student_id,
                            'email' => $student['email'] ?? '',
                            'section' => $student['section'] ?? ''
                        ];
                        
                        // Add success message if password was updated
                        if ($password_updated) {
                            $_SESSION['login_message'] = "Welcome! Your password has been securely updated.";
                        }
                        
                        header("Location: student_dashboard.php");
                        exit();
                    } else {
                        $_SESSION[$login_attempts_key]++;
                        $login_error = "Invalid Student ID or password!";
                        error_log("Failed login attempt for student ID: " . $student_id . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                    }
                } else {
                    $_SESSION[$login_attempts_key]++;
                    $login_error = "Invalid Student ID or password!";
                }
            } catch(PDOException $e) {
                $login_error = "System error. Please try again.";
                error_log("Student Login Error: " . $e->getMessage());
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
    <title>Student Login - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --maroon-light: #a30000;
            --gold: #FFD700;
            --blue: #007bff;
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
            max-width: 420px;
            margin: 0 auto;
            border: 2px solid var(--gold);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            color: white;
            padding: 25px 20px;
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
            padding: 30px 25px;
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
        
        .form-control:focus {
            border-color: var(--maroon);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128, 0, 0, 0.4);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 15px;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: var(--maroon);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .student-icon {
            color: var(--blue);
            font-size: 1.2rem;
        }
        
        .attempts-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .security-notice {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            margin-top: 15px;
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
                        <p>Student Portal Login</p>
                    </div>
                </div>
                <p class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Access Only</p>
            </div>
            
            <div class="login-body">
                <form method="POST" action="" id="loginForm">
                    <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($login_error); ?>
                    </div>
                    <?php elseif ($_SESSION[$login_attempts_key] > 0): ?>
                    <div class="attempts-warning">
                        <i class="fas fa-shield-alt me-2"></i>
                        Login attempts: <?php echo $_SESSION[$login_attempts_key]; ?>/5
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Enter your Student ID and password
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-card student-icon"></i>
                            Student ID
                        </label>
                        <input type="text" class="form-control" name="student_id" required 
                               placeholder="Enter your Student ID" 
                               value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock student-icon"></i>
                            Password
                        </label>
                        <input type="password" class="form-control" name="password" required 
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to Student Portal
                    </button>
                    
                    <div class="forgot-password">
                        <a href="forgot_password.php?type=student">
                            <i class="fas fa-key me-1"></i>Forgot Password?
                        </a>
                    </div>
                    
                    <div class="security-notice">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Security Enhanced:</strong> Passwords are now securely encrypted.
                    </div>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            For faculty login, visit the main portal
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission handler
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Signing In...';
            submitBtn.disabled = true;
            
            // Show security notice during conversion
            const securityNotice = document.querySelector('.security-notice');
            if (securityNotice) {
                securityNotice.innerHTML = '<i class="fas fa-sync-alt fa-spin me-2"></i> <strong>Updating Security:</strong> Securing your account...';
            }
        });

        // Clear error on input change
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                const errorAlert = document.querySelector('.alert-danger');
                if (errorAlert) {
                    errorAlert.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>