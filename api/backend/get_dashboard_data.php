<?php
// MindMate - Get Dashboard Data

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

try {
    $user_id = $_SESSION['user_id'];
    
    // Get statistics
    $statistics = getStatistics($user_id);
    
    // Get recent chats
    $recentChats = getRecentChats($user_id, 5);
    
    // Get mood data
    $moodData = getMoodData($user_id);
    
    // Get mood checks
    $moodChecks = getMoodChecks($user_id, 7);
    
    echo json_encode([
        'success' => true,
        'statistics' => $statistics,
        'recentChats' => $recentChats,
        'moodData' => $moodData,
        'moodChecks' => $moodChecks,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    logMessage("Error getting dashboard data: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function getStatistics($user_id) {
    global $conn;
    
    // Total chats
    $stmt = $conn->prepare("SELECT COUNT(*) as total_chats FROM chats WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $totalChats = $stmt->get_result()->fetch_assoc()['total_chats'];
    
    // Chats this week
    $stmt = $conn->prepare("
        SELECT COUNT(*) as weekly_chats 
        FROM chats 
        WHERE user_id = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $weeklyChats = $stmt->get_result()->fetch_assoc()['weekly_chats'];
    
    // Chats this month
    $stmt = $conn->prepare("
        SELECT COUNT(*) as monthly_chats 
        FROM chats 
        WHERE user_id = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $monthlyChats = $stmt->get_result()->fetch_assoc()['monthly_chats'];
    
    // Average sentiment
    $stmt = $conn->prepare("
        SELECT 
            AVG(CASE 
                WHEN sentiment = 'POSITIVE' THEN 1 
                WHEN sentiment = 'NEGATIVE' THEN -1 
                ELSE 0 
            END) as avg_sentiment
        FROM chats 
        WHERE user_id = ? AND sentiment IS NOT NULL
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $avgSentiment = $stmt->get_result()->fetch_assoc()['avg_sentiment'];
    
    // Days since last chat
    $stmt = $conn->prepare("
        SELECT DATEDIFF(NOW(), MAX(timestamp)) as days_since_last_chat 
        FROM chats 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $daysSinceLastChat = $stmt->get_result()->fetch_assoc()['days_since_last_chat'] ?? 0;
    
    return [
        'total_chats' => (int)$totalChats,
        'weekly_chats' => (int)$weeklyChats,
        'monthly_chats' => (int)$monthlyChats,
        'avg_sentiment' => round($avgSentiment, 2),
        'days_since_last_chat' => (int)$daysSinceLastChat
    ];
}

function getRecentChats($user_id, $limit = 5) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            id,
            user_message,
            ai_response,
            sentiment,
            timestamp
        FROM chats 
        WHERE user_id = ? 
        ORDER BY timestamp DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $chats = [];
    while ($row = $result->fetch_assoc()) {
        $chats[] = [
            'id' => $row['id'],
            'user_message' => $row['user_message'],
            'ai_response' => $row['ai_response'],
            'sentiment' => $row['sentiment'],
            'timestamp' => $row['timestamp']
        ];
    }
    
    return $chats;
}

function getMoodData($user_id) {
    global $conn;
    
    // Get sentiment distribution
    $stmt = $conn->prepare("
        SELECT 
            sentiment,
            COUNT(*) as count
        FROM chats 
        WHERE user_id = ? 
        AND sentiment IS NOT NULL 
        AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY sentiment
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $moodData = [];
    while ($row = $result->fetch_assoc()) {
        $moodData[$row['sentiment']] = (int)$row['count'];
    }
    
    return $moodData;
}

function getMoodChecks($user_id, $days = 7) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            mood,
            notes,
            timestamp
        FROM mood_checks 
        WHERE user_id = ? 
        AND timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ORDER BY timestamp DESC
    ");
    $stmt->bind_param("ii", $user_id, $days);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $moodChecks = [];
    while ($row = $result->fetch_assoc()) {
        $moodChecks[] = [
            'mood' => $row['mood'],
            'notes' => $row['notes'],
            'timestamp' => $row['timestamp']
        ];
    }
    
    return $moodChecks;
}
?>

