<?php
session_start();

// If already logged in as faculty, redirect to dashboard
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

// Login handling - FACULTY ONLY
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty_id = trim($_POST['faculty_id']);
    $password = trim($_POST['password']);
    
    if (empty($faculty_id) || empty($password)) {
        $login_error = "Faculty ID and password are required!";
    } else {
        try {
            // Check if faculty exists
            $stmt = $pdo->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
            $stmt->execute([$faculty_id]);
            $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($faculty) {
                // Password verification
                $password_valid = false;
                
                if (isset($faculty['password'])) {
                    // Check if password is hashed
                    if (strpos($faculty['password'], '$2y$') === 0) {
                        // Password is hashed
                        if (password_verify($password, $faculty['password'])) {
                            $password_valid = true;
                        }
                    } else {
                        // Password is plain text
                        if ($password === $faculty['password']) {
                            $password_valid = true;
                            
                            // Auto-convert to hash for security
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $update_stmt = $pdo->prepare("UPDATE faculty SET password = ? WHERE faculty_id = ?");
                            $update_stmt->execute([$hashed_password, $faculty_id]);
                        }
                    }
                }
                
                if ($password_valid) {
                    // Set session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_type'] = 'faculty';
                    $_SESSION['username'] = $faculty_id;
                    $_SESSION['user_id'] = $faculty_id;
                    
                    // Set faculty info
                    $_SESSION['user_info'] = [
                        'name' => ($faculty['first_name'] ?? '') . ' ' . ($faculty['last_name'] ?? ''),
                        'department' => $faculty['department'] ?? '',
                        'position' => $faculty['position'] ?? '',
                        'faculty_id' => $faculty_id,
                        'email' => $faculty['email'] ?? '',
                        'contact_number' => $faculty['contact_number'] ?? ''
                    ];
                    
                    header("Location: faculty_dashboard.php");
                    exit();
                } else {
                    $login_error = "Invalid Faculty ID or password!";
                }
            } else {
                $login_error = "Faculty ID not found!";
            }
        } catch(PDOException $e) {
            $login_error = "System error. Please try again.";
            error_log("Faculty Login Error: " . $e->getMessage());
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
        
        .faculty-icon {
            color: var(--green);
            font-size: 1.2rem;
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
                        <p>Faculty Portal Login</p>
                    </div>
                </div>
                <p class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Faculty Access Only</p>
            </div>
            
            <div class="login-body">
                <form method="POST" action="" id="loginForm">
                    <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($login_error); ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Enter your Faculty ID and password
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-card faculty-icon"></i>
                            Faculty ID
                        </label>
                        <input type="text" class="form-control" name="faculty_id" required 
                               placeholder="Enter your Faculty ID" 
                               value="<?php echo isset($_POST['faculty_id']) ? htmlspecialchars($_POST['faculty_id']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock faculty-icon"></i>
                            Password
                        </label>
                        <input type="password" class="form-control" name="password" required 
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to Faculty Portal
                    </button>
                    
                    <div class="forgot-password">
                        <a href="forgot_password.php?type=faculty">
                            <i class="fas fa-key me-1"></i>Forgot Password?
                        </a>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            For student login, visit the student portal
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
        });
    </script>
</body>
</html>