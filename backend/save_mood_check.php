<?php
// MindMate - Save Mood Check

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
if (!isset($input['mood']) || empty(trim($input['mood']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Mood is required']);
    exit();
}

$mood = sanitizeInput($input['mood']);
$notes = isset($input['notes']) ? sanitizeInput($input['notes']) : null;

// Validate mood value
$validMoods = ['happy', 'sad', 'neutral', 'anxious', 'angry', 'excited', 'calm', 'confused'];
if (!in_array($mood, $validMoods)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid mood value']);
    exit();
}

// Check rate limiting
if (!checkRateLimit($_SESSION['user_id'], 'mood_check')) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit();
}

try {
    // Save mood check using database abstraction
    $moodData = [
        'user_id' => $_SESSION['user_id'],
        'mood' => $mood,
        'notes' => $notes
    ];
    
    $moodCheckId = saveMoodCheck($moodData);
    
    if ($moodCheckId) {
        
        // Log the mood check
        logMessage("Mood check saved - User: {$_SESSION['user_id']}, Mood: $mood, ID: $moodCheckId", 'INFO');
        
        // Get mood insights
        $insights = getMoodInsights($_SESSION['user_id'], $mood);
        
        echo json_encode([
            'success' => true,
            'mood_check_id' => (string)$moodCheckId,
            'mood' => $mood,
            'insights' => $insights,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception("Failed to save mood check using database abstraction");
    }
    
} catch (Exception $e) {
    logMessage("Error saving mood check: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function getMoodInsights($user_id, $currentMood) {
    $insights = [];
    
    // Get recent mood checks using abstraction layer
    $recentMoodChecks = getMoodChecks($user_id, 30); // Get last 30 mood checks
    
    if (count($recentMoodChecks) >= 3) {
        $recentMoods = array_slice(array_column($recentMoodChecks, 'mood'), 0, 7); // Last 7 moods
        $positiveMoods = ['happy', 'excited', 'calm'];
        $negativeMoods = ['sad', 'anxious', 'angry'];
        
        $positiveCount = count(array_filter($recentMoods, function($mood) use ($positiveMoods) {
            return in_array($mood, $positiveMoods);
        }));
        
        if ($positiveCount >= 4) {
            $insights[] = "You've been feeling more positive lately!";
        } elseif ($positiveCount == 0) {
            $insights[] = "You might want to consider talking to someone about how you're feeling.";
        }
    }
    
    // Mood-specific insights
    switch ($currentMood) {
        case 'sad':
            $insights[] = "It's okay to feel sad sometimes. Consider reaching out to a friend or family member.";
            break;
        case 'anxious':
            $insights[] = "Anxiety can be overwhelming. Try some deep breathing exercises or meditation.";
            break;
        case 'angry':
            $insights[] = "Anger is a valid emotion. Consider what might be causing it and how to address it constructively.";
            break;
        case 'happy':
            $insights[] = "Great to see you're feeling happy! What's contributing to this positive mood?";
            break;
        case 'excited':
            $insights[] = "Excitement is wonderful! Channel this energy into something productive.";
            break;
        case 'calm':
            $insights[] = "Feeling calm is a great state to be in. Enjoy this peaceful moment.";
            break;
        case 'confused':
            $insights[] = "Confusion is normal. Take time to process your thoughts and feelings.";
            break;
    }
    
    // Suggest actions based on mood
    $suggestions = getMoodSuggestions($currentMood);
    if ($suggestions) {
        $insights = array_merge($insights, $suggestions);
    }
    
    return $insights;
}

function getMoodSuggestions($mood) {
    $suggestions = [
        'sad' => [
            "Consider journaling about your feelings",
            "Listen to uplifting music",
            "Take a walk in nature"
        ],
        'anxious' => [
            "Try the 4-7-8 breathing technique",
            "Practice mindfulness meditation",
            "Write down your worries"
        ],
        'angry' => [
            "Take deep breaths and count to 10",
            "Go for a run or do physical exercise",
            "Write a letter you don't send"
        ],
        'happy' => [
            "Share your joy with others",
            "Do something creative",
            "Practice gratitude"
        ],
        'excited' => [
            "Channel your energy into a project",
            "Share your excitement with friends",
            "Plan something fun"
        ],
        'calm' => [
            "Enjoy this peaceful moment",
            "Practice meditation or yoga",
            "Read a good book"
        ],
        'confused' => [
            "Take time to reflect",
            "Talk to someone you trust",
            "Write down your thoughts"
        ],
        'neutral' => [
            "Check in with yourself",
            "Do something you enjoy",
            "Connect with others"
        ]
    ];
    
    return $suggestions[$mood] ?? [];
}
?>

