<?php
// MindMate - Save Chat Message

session_start();
require_once 'db_connect.php';

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
$required_fields = ['message', 'sender'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

$message = sanitizeInput($input['message']);
$sender = sanitizeInput($input['sender']);
$sentiment = isset($input['sentiment']) ? sanitizeInput($input['sentiment']) : null;
$confidence = isset($input['confidence']) ? floatval($input['confidence']) : null;

// Validate message length
if (strlen($message) > MAX_MESSAGE_LENGTH) {
    http_response_code(400);
    echo json_encode(['error' => 'Message too long']);
    exit();
}

// Validate sender
if (!in_array($sender, ['user', 'ai'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sender']);
    exit();
}

// Check rate limiting
if (!checkRateLimit($_SESSION['user_id'], 'message')) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit();
}

try {
    // If this is a user message, we need to get AI response first
    if ($sender === 'user') {
        // Get AI response
        $aiResponse = getAIResponse($message);
        
        if (!$aiResponse) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get AI response']);
            exit();
        }
        
        // Save both user message and AI response
        $stmt = $conn->prepare("
            INSERT INTO chats (user_id, user_message, ai_response, sentiment, confidence) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $aiMessage = $aiResponse['reply'];
        $aiSentiment = $aiResponse['sentiment'];
        $aiConfidence = isset($aiResponse['confidence']) ? $aiResponse['confidence'] : null;
        
        $stmt->bind_param("isssd", 
            $_SESSION['user_id'], 
            $message, 
            $aiMessage, 
            $aiSentiment, 
            $aiConfidence
        );
        
        if ($stmt->execute()) {
            $chatId = $conn->insert_id;
            
            // Log the interaction
            logMessage("Chat saved - User: {$_SESSION['user_id']}, Chat ID: $chatId", 'INFO');
            
            echo json_encode([
                'success' => true,
                'chat_id' => $chatId,
                'ai_response' => $aiMessage,
                'sentiment' => $aiSentiment,
                'confidence' => $aiConfidence
            ]);
        } else {
            throw new Exception("Failed to save chat: " . $stmt->error);
        }
    } else {
        // This is an AI message (shouldn't happen in normal flow)
        http_response_code(400);
        echo json_encode(['error' => 'AI messages should not be saved directly']);
    }
    
} catch (Exception $e) {
    logMessage("Error saving chat: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

// Function to get AI response
function getAIResponse($message) {
    $data = json_encode([
        'message' => $message,
        'user_id' => $_SESSION['user_id']
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, AI_API_URL . '/chat');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, AI_API_TIMEOUT);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        logMessage("CURL error: $error", 'ERROR');
        return false;
    }
    
    if ($httpCode !== 200) {
        logMessage("AI API returned HTTP $httpCode", 'ERROR');
        return false;
    }
    
    $result = json_decode($response, true);
    
    if (!$result || isset($result['error'])) {
        logMessage("AI API error: " . ($result['error'] ?? 'Unknown error'), 'ERROR');
        return false;
    }
    
    return $result;
}
?>

