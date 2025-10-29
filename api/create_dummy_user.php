<?php
// MindMate - Create Dummy User Script

// Database connection settings
$host = "localhost";
$user = "root";
$pass = "";
$db = "mindmate";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL successfully!\n";

// Create dummy user
$username = "testuser";
$email = "test@mindmate.com";
$password = "password123";
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "User already exists! Updating password...\n";
    
    // Update existing user
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $password_hash, $email);
    
    if ($stmt->execute()) {
        echo "✅ User password updated successfully!\n";
    } else {
        echo "❌ Error updating user: " . $stmt->error . "\n";
    }
} else {
    // Create new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);
    
    if ($stmt->execute()) {
        echo "✅ Dummy user created successfully!\n";
    } else {
        echo "❌ Error creating user: " . $stmt->error . "\n";
    }
}

echo "\n🎉 Dummy User Credentials:\n";
echo "Email: test@mindmate.com\n";
echo "Password: password123\n";
echo "Username: testuser\n\n";

echo "You can now sign in at: http://localhost:8000/login.php\n";

$conn->close();
?>
