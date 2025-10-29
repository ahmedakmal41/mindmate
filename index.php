<?php
// MindMate - Main Entry Point for Vercel
require_once 'backend/config.php';
require_once 'backend/db_abstraction.php';

// Start session
session_start();

// Get the path from Vercel
$path = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($path, PHP_URL_PATH);

// Route handling
switch ($path) {
    case '/':
        include 'index.html';
        break;
    case '/login':
        include 'login.php';
        break;
    case '/chat':
        include 'chat.html';
        break;
    case '/api/chat':
        include 'backend/api_bridge.php';
        break;
    case '/api/health':
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'ai_url' => AI_API_URL,
            'platform' => 'Vercel'
        ]);
        break;
    case '/api/test-ai':
        // Test AI connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, AI_API_URL . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        header('Content-Type: application/json');
        echo json_encode([
            'ai_status' => $httpCode === 200 ? 'connected' : 'disconnected',
            'ai_response' => $response,
            'ai_url' => AI_API_URL
        ]);
        break;
    default:
        // Check if it's a static file
        if (file_exists($path)) {
            return false; // Let Vercel handle static files
        }
        http_response_code(404);
        echo "Page not found: " . $path;
        break;
}
?>
