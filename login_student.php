<?php
session_start();

// Redirect if already logged in
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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $input_password = trim($_POST['password']);

    if (empty($username) || empty($input_password)) {
        $login_error = "Student ID and password are required!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$username]);
            $student = $stmt->fetch();

            if ($student) {
                if ($student['status'] !== 'active') {
                    $login_error = "Your account is inactive. Please contact the administrator.";
                } else {
                    $isValid = password_verify($input_password, $student['password']) || $input_password === $student['password'];
                    if ($isValid) {
                        // Update last login
                        $updateLogin = $pdo->prepare("UPDATE students SET last_login = NOW() WHERE student_id = ?");
                        $updateLogin->execute([$username]);

                        $_SESSION['loggedin'] = true;
                        $_SESSION['user_type'] = 'student';
                        $_SESSION['username'] = $username;
                        $_SESSION['user_info'] = [
                            'name' => $student['first_name'] . ' ' . $student['last_name'],
                            'email' => $student['email'],
                            'course' => $student['course'],
                            'year_level' => $student['year_level']
                        ];

                        header("Location: student_dashboard.php");
                        exit();
                    } else {
                        $login_error = "Invalid student ID or password!";
                    }
                }
            } else {
                $login_error = "Invalid student ID or password!";
            }
        } catch (PDOException $e) {
            $login_error = "Database error during login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #800000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            border: 2px solid #FFD700;
        }
        .btn-login {
            background: #800000;
            color: white;
            width: 100%;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h4 class="text-center mb-4">Student Login</h4>
        <?php if (!empty($login_error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Student ID</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-login">Sign In</button>
        </form>
    </div>
</body>
</html>
