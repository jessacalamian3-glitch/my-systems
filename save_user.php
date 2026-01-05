<?php
// DATABASE CONNECTION
$host = "localhost";
$user = "root";
$pass = "";
$db   = "msubuug_db"; // ← change to your real DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only allow POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data
    $role       = $_POST['role']; // admin | student | faculty
    $firstname  = $_POST['firstname'];
    $lastname   = $_POST['lastname'];
    $email      = $_POST['email'];
    $username   = $_POST['username'];
    $password   = $_POST['password'];

    // Basic validation
    if (empty($role) || empty($firstname) || empty($lastname) || empty($email) || empty($username) || empty($password)) {
        echo "Error: All fields are required.";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SELECT TARGET TABLE
    if ($role === "admin") {
        $table = "admin";
    } elseif ($role === "student") {
        $table = "students";
    } elseif ($role === "faculty") {
        $table = "faculty";
    } else {
        echo "Error: Invalid role.";
        exit;
    }

    // CHECK IF EMAIL OR USERNAME EXISTS IN THAT SPECIFIC TABLE
    $check = $conn->prepare("SELECT * FROM $table WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "Error: Email or Username already exists in $table.";
        exit;
    }

    // INSERT INTO CORRECT TABLE
    $stmt = $conn->prepare("
        INSERT INTO $table (firstname, lastname, email, username, password)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssss", $firstname, $lastname, $email, $username, $hashed_password);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
