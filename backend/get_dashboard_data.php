<?php
// MindMate - Get Dashboard Data

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

try {
    $user_id = $_SESSION['user_id'];
    
    // Get statistics
    $statistics = getStatistics($user_id);
    
    // Get recent chats
    $recentChats = getRecentChatsForDashboard($user_id, 5);
    
    // Get mood data
    $moodData = getMoodData($user_id);
    
    // Get mood checks
    $moodChecks = getMoodChecksForDashboard($user_id, 7);
    
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
    // Get all chats for the user
    $allChats = getChatHistory($user_id, 1000); // Get a large number to calculate stats
    
    $totalChats = count($allChats);
    $weeklyChats = 0;
    $monthlyChats = 0;
    $sentimentSum = 0;
    $sentimentCount = 0;
    $lastChatTime = null;
    
    $oneWeekAgo = strtotime('-7 days');
    $oneMonthAgo = strtotime('-30 days');
    
    foreach ($allChats as $chat) {
        $chatTime = strtotime($chat['timestamp']);
        
        if ($chatTime >= $oneWeekAgo) {
            $weeklyChats++;
        }
        if ($chatTime >= $oneMonthAgo) {
            $monthlyChats++;
        }
        
        // Calculate sentiment average
        if (!empty($chat['sentiment'])) {
            $sentimentValue = 0;
            switch (strtoupper($chat['sentiment'])) {
                case 'POSITIVE':
                    $sentimentValue = 1;
                    break;
                case 'NEGATIVE':
                    $sentimentValue = -1;
                    break;
                default:
                    $sentimentValue = 0;
            }
            $sentimentSum += $sentimentValue;
            $sentimentCount++;
        }
        
        // Track last chat time
        if (!$lastChatTime || $chatTime > $lastChatTime) {
            $lastChatTime = $chatTime;
        }
    }
    
    $avgSentiment = $sentimentCount > 0 ? $sentimentSum / $sentimentCount : 0;
    $daysSinceLastChat = $lastChatTime ? floor((time() - $lastChatTime) / (24 * 60 * 60)) : 0;
    
    return [
        'total_chats' => $totalChats,
        'weekly_chats' => $weeklyChats,
        'monthly_chats' => $monthlyChats,
        'avg_sentiment' => round($avgSentiment, 2),
        'days_since_last_chat' => $daysSinceLastChat
    ];
}

function getRecentChatsForDashboard($user_id, $limit = 5) {
    return getRecentChats($user_id, $limit);
}

function getMoodData($user_id) {
    // Get recent chats and analyze sentiment distribution
    $recentChats = getChatHistory($user_id, 100); // Get recent chats for analysis
    $moodData = [];
    
    $thirtyDaysAgo = strtotime('-30 days');
    
    foreach ($recentChats as $chat) {
        $chatTime = strtotime($chat['timestamp']);
        if ($chatTime >= $thirtyDaysAgo && !empty($chat['sentiment'])) {
            $sentiment = $chat['sentiment'];
            if (!isset($moodData[$sentiment])) {
                $moodData[$sentiment] = 0;
            }
            $moodData[$sentiment]++;
        }
    }
    
    return $moodData;
}

function getMoodChecksForDashboard($user_id, $days = 7) {
    // Get mood checks using abstraction layer
    $allMoodChecks = getMoodChecks($user_id, 50); // Get more than needed
    
    // Filter by days
    $cutoffTime = strtotime("-$days days");
    $filteredMoodChecks = [];
    
    foreach ($allMoodChecks as $moodCheck) {
        $checkTime = strtotime($moodCheck['timestamp']);
        if ($checkTime >= $cutoffTime) {
            $filteredMoodChecks[] = $moodCheck;
        }
    }
    
    return $filteredMoodChecks;
}
?>

