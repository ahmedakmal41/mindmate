<?php
// MindMate - API Bridge (PHP to Python)

session_start();
require_once 'db_abstraction.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit();
}

// Validate required fields
if (!isset($input['message']) || empty(trim($input['message']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit();
}

$message = sanitizeInput($input['message']);
$action = isset($input['action']) ? sanitizeInput($input['action']) : 'chat';

// Validate message length
if (strlen($message) > MAX_MESSAGE_LENGTH) {
    http_response_code(400);
    echo json_encode(['error' => 'Message too long']);
    exit();
}

// Check rate limiting
if (!checkRateLimit($_SESSION['user_id'], 'api_call')) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit();
}

try {
    // Prepare data for AI API
    $requestData = [
        'message' => $message,
        'user_id' => $_SESSION['user_id'],
        'action' => $action,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Add user context if available
    $user = getCurrentUser();
    if ($user) {
        $requestData['user_context'] = [
            'username' => $user['username'],
            'user_id' => $user['id']
        ];
    }
    
    // Get recent chat history for context
    $recentChats = getRecentChatHistory($_SESSION['user_id'], 5);
    if ($recentChats) {
        $requestData['chat_history'] = $recentChats;
    }
    
    // Call AI API
    $response = callAIAPI($requestData);
    
    if (!$response) {
        throw new Exception('Failed to get AI response');
    }
    
    // Log the API call
    logMessage("API call successful - User: {$_SESSION['user_id']}, Action: $action", 'INFO');
    
    echo json_encode($response);
    
} catch (Exception $e) {
    logMessage("API bridge error: " . $e->getMessage(), 'ERROR');
    
    // Return a fallback response
    $fallbackResponse = [
        'reply' => "I apologize, but I'm having trouble connecting right now. Please try again in a moment. Your message is important to me.",
        'sentiment' => 'NEUTRAL',
        'confidence' => 0.5,
        'error' => 'Service temporarily unavailable'
    ];
    
    http_response_code(503);
    echo json_encode($fallbackResponse);
}

// Function to call AI API
function callAIAPI($data) {
    $jsonData = json_encode($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, AI_API_URL . '/chat');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData),
        'User-Agent: MindMate-PHP/1.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, AI_API_TIMEOUT);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for development
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    // Log API call details
    logMessage("AI API call - HTTP: $httpCode, Time: {$info['total_time']}s", 'INFO');
    
    if ($error) {
        logMessage("CURL error: $error", 'ERROR');
        return false;
    }
    
    if ($httpCode !== 200) {
        logMessage("AI API returned HTTP $httpCode: $response", 'ERROR');
        return false;
    }
    
    $result = json_decode($response, true);
    
    if (!$result) {
        logMessage("Failed to decode AI API response: $response", 'ERROR');
        return false;
    }
    
    if (isset($result['error'])) {
        logMessage("AI API error: " . $result['error'], 'ERROR');
        return false;
    }
    
    // Validate response structure
    if (!isset($result['reply'])) {
        logMessage("Invalid AI API response structure", 'ERROR');
        return false;
    }
    
    return $result;
}

// Function to get recent chat history
function getRecentChatHistory($user_id, $limit = 5) {
    return getRecentChats($user_id, $limit);
}

// Function to check AI API health
function checkAIAPIHealth() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, AI_API_URL . '/health');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

// Health check endpoint
if (isset($_GET['health'])) {
    $isHealthy = checkAIAPIHealth();
    http_response_code($isHealthy ? 200 : 503);
    echo json_encode([
        'status' => $isHealthy ? 'healthy' : 'unhealthy',
        'ai_api_url' => AI_API_URL,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}
?>

