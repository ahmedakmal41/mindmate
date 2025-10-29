<?php
// MindMate - Database Configuration

// Database configuration - Using MongoDB/CosmosDB
$db_type = getenv('DB_TYPE') ?: 'mongodb';

// MongoDB connection (no MySQL needed)
if ($db_type === 'mongodb') {
    // MongoDB will be initialized in db_abstraction.php
    $conn = null; // No MySQL connection needed
} else {
    die("Only MongoDB is supported. Set DB_TYPE=mongodb");
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration (must be set before session_start)
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
}

// Timezone
date_default_timezone_set('UTC');

// API Configuration
// Use environment variable for Azure deployment
define('AI_API_URL', getenv('AI_API_URL') ?: 'https://aiengine-sable.vercel.app');
define('AI_API_TIMEOUT', 30);

// Security settings
define('MAX_MESSAGE_LENGTH', 1000);
define('RATE_LIMIT_PER_MINUTE', 10);

// File paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// Create necessary directories
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Logging function
function logMessage($message, $level = 'INFO') {
    $logFile = LOG_PATH . '/mindmate_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Sanitize input function
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email function
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Rate limiting function - handled by db_abstraction layer
function checkRateLimit($user_id, $action = 'message') {
    // This is now handled by db_abstraction.php
    // For MongoDB implementation
    return true; // Temporarily allow all requests
}

// Clean old rate limit records - handled by db_abstraction layer
// No cleanup needed here for MongoDB
?>

