<?php
// MindMate - Database Initialization Script

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

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Users table created successfully\n";
} else {
    echo "❌ Error creating users table: " . $conn->error . "\n";
}

// Create chats table
$sql = "CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    sentiment VARCHAR(50),
    confidence DECIMAL(5,4),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Chats table created successfully\n";
} else {
    echo "❌ Error creating chats table: " . $conn->error . "\n";
}

// Create mood_checks table
$sql = "CREATE TABLE IF NOT EXISTS mood_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mood VARCHAR(20) NOT NULL,
    notes TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Mood checks table created successfully\n";
} else {
    echo "❌ Error creating mood_checks table: " . $conn->error . "\n";
}

// Create rate_limits table
$sql = "CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Rate limits table created successfully\n";
} else {
    echo "❌ Error creating rate_limits table: " . $conn->error . "\n";
}

// Create user_sessions table
$sql = "CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ User sessions table created successfully\n";
} else {
    echo "❌ Error creating user_sessions table: " . $conn->error . "\n";
}

// Create indexes for better performance
$indexes = [
    "CREATE INDEX idx_chats_user_id ON chats(user_id)",
    "CREATE INDEX idx_chats_timestamp ON chats(timestamp)",
    "CREATE INDEX idx_mood_checks_user_id ON mood_checks(user_id)",
    "CREATE INDEX idx_mood_checks_timestamp ON mood_checks(timestamp)",
    "CREATE INDEX idx_rate_limits_user_action ON rate_limits(user_id, action)",
    "CREATE INDEX idx_rate_limits_created_at ON rate_limits(created_at)"
];

foreach ($indexes as $index) {
    if ($conn->query($index) === TRUE) {
        echo "✅ Index created successfully\n";
    } else {
        echo "❌ Error creating index: " . $conn->error . "\n";
    }
}

echo "\n🎉 Database initialization completed!\n";
echo "You can now access the MindMate application at: http://localhost:8000\n";

$conn->close();
?>
