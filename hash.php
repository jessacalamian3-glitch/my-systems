<?php
// hash.php
// Simple PHP script for hashing and verifying passwords

function createHash($password) {
    // Use bcrypt by default
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyHash($password, $hash) {
    return password_verify($password, $hash);
}

// Example usage
if (isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = createHash($password);
    echo "Password: " . htmlspecialchars($password) . "<br>";
    echo "Hash: " . $hash . "<br>";

    // Optional: verify immediately
    if (verifyHash($password, $hash)) {
        echo "Verification: Success!";
    } else {
        echo "Verification: Failed!";
    }
}
?>

<!-- Simple HTML form -->
<form method="post">
    <label>Password: <input type="text" name="password"></label>
    <button type="submit">Hash Password</button>
</form>
