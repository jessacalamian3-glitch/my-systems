<?php
session_start();

// Kung naka-login na as STUDENT, redirect sa student dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['user_type'] === 'student') {
    header("Location: student_dashboard.php");
    exit();
}

// Kung naka-login na as FACULTY, redirect sa faculty dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['user_type'] === 'faculty') {
    header("Location: faculty_dashboard.php");
    exit();
}

// Initialize error variable
$error = '';

// Process login ONLY if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Student accounts
    $student_accounts = [
        '2020-12345' => [
            'password' => 'password',
            'name' => 'Avril Lyza Suan',
            'course' => 'BS Computer Science',
            'year' => '3rd Year'
        ],
        '2020-12346' => [
            'password' => 'password',
            'name' => 'Maria Santos',
            'course' => 'BS Information Technology',
            'year' => '2nd Year'
        ],
        '2020-12347' => [
            'password' => 'password',
            'name' => 'Pedro Reyes',
            'course' => 'BS Computer Engineering',
            'year' => '4th Year'
        ]
    ];
    
    // Check credentials
    if (isset($student_accounts[$username]) && $student_accounts[$username]['password'] === $password) {
        // Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['user_type'] = 'student';
        $_SESSION['username'] = $username;
        $_SESSION['user_info'] = $student_accounts[$username];
        
        // Redirect to STUDENT dashboard
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Invalid student ID or password!";
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
        }
        
        body {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            margin: 20px auto;
            border: 3px solid var(--gold);
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
            height: 5px;
            background: var(--gold);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--gold);
            object-fit: cover;
            background: white;
            padding: 5px;
        }
        
        .university-name {
            text-align: left;
        }
        
        .university-name h4 {
            margin: 0;
            font-weight: bold;
            color: var(--gold);
        }
        
        .university-name p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--maroon);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.15);
        }
        
        .btn-student {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-light));
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
        }
        
        .btn-student:hover {
            background: linear-gradient(135deg, var(--maroon-light), var(--maroon-dark));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128, 0, 0, 0.4);
            color: white;
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 20px;
        }
        
        .floating-label label {
            position: absolute;
            top: -10px;
            left: 12px;
            background: white;
            padding: 0 5px;
            font-size: 0.85rem;
            color: var(--maroon);
            font-weight: 500;
        }
        
        .back-link {
            color: var(--maroon);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: var(--maroon-dark);
            text-decoration: underline;
        }
        
        .test-accounts {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 15px;
            margin-top: 25px;
            border-left: 4px solid var(--gold);
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }
        
        .form-control.error {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Header Section -->
            <div class="login-header">
                <div class="logo-container">
                    <img src="msu-logo.png" alt="MSU Logo" class="logo-img" onerror="this.style.display='none'">
                    <div class="university-name">
                        <h4>MSU BUUG</h4>
                        <p>Student Portal</p>
                    </div>
                </div>
                <p class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Login Access</p>
            </div>
            
            <!-- Login Form -->
            <div class="login-body">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="studentLoginForm">
                    <!-- Student ID Field -->
                    <div class="floating-label">
                        <label for="username"><i class="fas fa-id-card me-1"></i>Student ID</label>
                        <input type="text" class="form-control" id="username" name="username" required 
                               placeholder="Enter your student ID (e.g., 2020-12345)"
                               value="<?php echo $_POST['username'] ?? ''; ?>">
                    </div>
                    
                    <!-- Password Field -->
                    <div class="floating-label">
                        <label for="password"><i class="fas fa-lock me-1"></i>Password</label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Enter your password">
                    </div>
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        <a href="#" class="back-link">
                            Forgot password?
                        </a>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" class="btn btn-student w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to Student Portal
                    </button>
                    
                    <!-- Back to Main -->
                    <div class="text-center">
                        <a href="index.php" class="back-link">
                            <i class="fas fa-arrow-left me-1"></i>Back to Portal Selection
                        </a>
                    </div>
                </form>
                
                <!-- Test Accounts -->
                <div class="test-accounts">
                    <h6 class="mb-3" style="color: var(--maroon);">
                        <i class="fas fa-key me-2"></i>Test Student Accounts
                    </h6>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <small class="text-muted d-block">Student Account 1:</small>
                            <strong>2020-12345</strong><br>
                            <small class="text-muted">Password: <code>password</code></small>
                            <br>
                            <small class="text-muted">Avril Lyza Suan - BS Computer Science</small>
                        </div>
                        <div class="col-12 mb-3">
                            <small class="text-muted d-block">Student Account 2:</small>
                            <strong>2020-12346</strong><br>
                            <small class="text-muted">Password: <code>password</code></small>
                            <br>
                            <small class="text-muted">Maria Santos - BS Information Technology</small>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Student Account 3:</small>
                            <strong>2020-12347</strong><br>
                            <small class="text-muted">Password: <code>password</code></small>
                            <br>
                            <small class="text-muted">Pedro Reyes - BS Computer Engineering</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission animation
        document.getElementById('studentLoginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>