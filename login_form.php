<?php
session_start();

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    switch ($_SESSION['user_type']) {
        case 'student':
            header("Location: student_dashboard.php");
            break;
        case 'faculty':
            header("Location: faculty_dashboard.php");
            break;
        case 'admin':
            header("Location: admin_dashboard.php");
            break;
    }
    exit();
}

// Database connection - CORRECTED DATABASE NAME
$host = 'localhost';
$dbname = 'msubuug_db';  // Tama na ito
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$login_error = '';

// Login handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'];
    $username = trim($_POST['username']);
    $input_password = trim($_POST['password']);
    
    if (empty($username) || empty($input_password)) {
        $login_error = "Username and password are required!";
    } else {
        try {
            // Define table and ID column names for each user type - UPDATED BASED ON YOUR ACTUAL TABLES
            switch ($user_type) {
                case 'student':
                    $table = 'students'; // Your actual table name
                    $id_column = 'student_id';
                    break;
                case 'faculty':
                    $table = 'faculty'; // Your actual table name
                    $id_column = 'faculty_id';
                    break;
                case 'admin':
                    $table = 'admin_users'; // Your actual table name for admin
                    $id_column = 'username';
                    break;
                default:
                    throw new Exception("Invalid user type selected");
            }
            
            // Prepare and execute the query
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE $id_column = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // IMPROVED PASSWORD VERIFICATION
                $password_valid = false;
                
                if (isset($user['password'])) {
                    // Check if password is hashed (starts with $2y$)
                    if (strpos($user['password'], '$2y$') === 0) {
                        // Password is hashed - use password_verify
                        if (password_verify($input_password, $user['password'])) {
                            $password_valid = true;
                        }
                    } else {
                        // Password is plain text - direct comparison
                        if ($input_password === $user['password']) {
                            $password_valid = true;
                            
                            // Auto-convert plain text to hash for security
                            $hashed_password = password_hash($input_password, PASSWORD_DEFAULT);
                            $update_stmt = $pdo->prepare("UPDATE $table SET password = ? WHERE $id_column = ?");
                            $update_stmt->execute([$hashed_password, $username]);
                        }
                    }
                }
                
                if ($password_valid) {
                    // Set session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_id'] = $user[$id_column] ?? $username;
                    
                    // Set user info based on type
                    switch ($user_type) {
                        case 'student':
                            $_SESSION['user_info'] = [
                                'name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                                'course' => $user['course'] ?? '',
                                'year_level' => $user['year_level'] ?? '',
                                'student_id' => $user['student_id'] ?? $username
                            ];
                            header("Location: student_dashboard.php");
                            break;
                        case 'faculty':
                            $_SESSION['user_info'] = [
                                'name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                                'department' => $user['department'] ?? '',
                                'position' => $user['position'] ?? '',
                                'faculty_id' => $user['faculty_id'] ?? $username
                            ];
                            header("Location: faculty_dashboard.php");
                            break;
                        case 'admin':
                            $_SESSION['user_info'] = [
                                'name' => $user['name'] ?? 'Administrator',
                                'role' => $user['role'] ?? 'Admin',
                                'admin_id' => $user['admin_id'] ?? $username
                            ];
                            header("Location: admin_dashboard.php");
                            break;
                    }
                    exit();
                } else {
                    $login_error = "Invalid username or password!";
                }
            } else {
                $login_error = "Invalid username or password!";
            }
        } catch(PDOException $e) {
            $login_error = "Database error during login: " . $e->getMessage();
            // Log the actual error for debugging
            error_log("Login Error: " . $e->getMessage());
        } catch(Exception $e) {
            $login_error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --maroon-light: #a30000;
            --gold: #FFD700;
            --blue: #007bff;
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
            max-width: 450px;
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
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
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
        
        .role-icon {
            font-size: 1.2rem;
            margin-right: 8px;
        }
        
        .student-option { color: var(--blue); }
        .faculty-option { color: var(--maroon); }
        .admin-option { color: var(--green); }
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
                        <p>Student Information System</p>
                    </div>
                </div>
                <p class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Multi-Role Access Portal</p>
            </div>
            
            <div class="login-body">
                <form method="POST" action="" id="loginForm">
                    <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($login_error); ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Select your role and enter your credentials
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user-tag"></i>
                            Login As
                        </label>
                        <select class="form-select" name="user_type" required>
                            <option value="student" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'student') ? 'selected' : ''; ?>>
                                <i class="fas fa-user-graduate role-icon student-option"></i> Student
                            </option>
                            <option value="faculty" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'faculty') ? 'selected' : ''; ?>>
                                <i class="fas fa-chalkboard-teacher role-icon faculty-option"></i> Faculty
                            </option>
                            <option value="admin" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'admin') ? 'selected' : ''; ?>>
                                <i class="fas fa-user-shield role-icon admin-option"></i> Admin
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-card"></i>
                            Username / ID
                        </label>
                        <input type="text" class="form-control" name="username" required 
                               placeholder="Enter your ID number" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
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
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to Portal
                    </button>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Select your role to access the appropriate dashboard
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
            const userType = this.querySelector('select[name="user_type"]').value;
            
            let buttonText = 'Signing In...';
            switch(userType) {
                case 'student':
                    buttonText = 'Accessing Student Portal...';
                    break;
                case 'faculty':
                    buttonText = 'Accessing Faculty Portal...';
                    break;
                case 'admin':
                    buttonText = 'Accessing Admin Portal...';
                    break;
            }
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> ' + buttonText;
            submitBtn.disabled = true;
        });

        // Dynamic placeholder based on role selection
        document.querySelector('select[name="user_type"]').addEventListener('change', function() {
            const usernameInput = document.querySelector('input[name="username"]');
            const role = this.value;
            
            switch(role) {
                case 'student':
                    usernameInput.placeholder = 'Enter your Student ID';
                    break;
                case 'faculty':
                    usernameInput.placeholder = 'Enter your Faculty ID';
                    break;
                case 'admin':
                    usernameInput.placeholder = 'Enter your Admin ID';
                    break;
                default:
                    usernameInput.placeholder = 'Enter your ID number';
            }
        });
    </script>
</body>
</html>