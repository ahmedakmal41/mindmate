<?php
// MindMate - Database Connection

require_once 'config.php';

// Database connection class
class Database {
    private $connection;
    private $host;
    private $user;
    private $pass;
    private $db;
    
    public function __construct($host, $user, $pass, $db) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
        $this->connect();
    }
    
    private function connect() {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->db);
        
        if ($this->connection->connect_error) {
            logMessage("Database connection failed: " . $this->connection->connect_error, 'ERROR');
            die("Database connection failed: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8mb4");
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result) {
            logMessage("Query failed: " . $this->connection->error, 'ERROR');
        }
        return $result;
    }
    
    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            logMessage("Prepare failed: " . $this->connection->error, 'ERROR');
        }
        return $stmt;
    }
    
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function close() {
        $this->connection->close();
    }
}

// Create global database instance
$db = new Database($host, $user, $pass, $db);
$conn = $db->getConnection();

// Database initialization function
function initializeDatabase() {
    global $conn;
    
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
    
    if (!$conn->query($sql)) {
        logMessage("Error creating users table: " . $conn->error, 'ERROR');
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
    
    if (!$conn->query($sql)) {
        logMessage("Error creating chats table: " . $conn->error, 'ERROR');
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
    
    if (!$conn->query($sql)) {
        logMessage("Error creating mood_checks table: " . $conn->error, 'ERROR');
    }
    
    // Create rate_limits table
    $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($sql)) {
        logMessage("Error creating rate_limits table: " . $conn->error, 'ERROR');
    }
    
    // Create sessions table for better session management
    $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
        id VARCHAR(128) PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($sql)) {
        logMessage("Error creating user_sessions table: " . $conn->error, 'ERROR');
    }
    
    // Create indexes for better performance (MySQL doesn't support IF NOT EXISTS for indexes)
    $indexes = [
        "CREATE INDEX idx_chats_user_id ON chats(user_id)",
        "CREATE INDEX idx_chats_timestamp ON chats(timestamp)",
        "CREATE INDEX idx_mood_checks_user_id ON mood_checks(user_id)",
        "CREATE INDEX idx_mood_checks_timestamp ON mood_checks(timestamp)",
        "CREATE INDEX idx_rate_limits_user_action ON rate_limits(user_id, action)",
        "CREATE INDEX idx_rate_limits_created_at ON rate_limits(created_at)"
    ];
    
    foreach ($indexes as $index) {
        // Check if index exists before creating
        $indexName = extractIndexName($index);
        if (!indexExists($conn, $indexName)) {
            $conn->query($index);
        }
    }
    
    logMessage("Database initialized successfully", 'INFO');
}

// Helper function to extract index name from CREATE INDEX statement
function extractIndexName($sql) {
    preg_match('/CREATE INDEX (\w+) ON/', $sql, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}

// Helper function to check if index exists
function indexExists($conn, $indexName) {
    if (empty($indexName)) return false;
    
    $result = $conn->query("SHOW INDEX FROM chats WHERE Key_name = '$indexName'");
    if ($result && $result->num_rows > 0) return true;
    
    $result = $conn->query("SHOW INDEX FROM mood_checks WHERE Key_name = '$indexName'");
    if ($result && $result->num_rows > 0) return true;
    
    $result = $conn->query("SHOW INDEX FROM rate_limits WHERE Key_name = '$indexName'");
    if ($result && $result->num_rows > 0) return true;
    
    return false;
}

// Initialize database on first run
initializeDatabase();

// Session management functions
function startSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function regenerateSessionId() {
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function destroySession() {
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

// User authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Cleanup function for old sessions
function cleanupOldSessions() {
    global $conn;
    
    $stmt = $conn->prepare("
        DELETE FROM user_sessions 
        WHERE last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
}

// Call cleanup function
cleanupOldSessions();
?>

