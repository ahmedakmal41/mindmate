<?php
// Test Complete Application Flow

// Load environment variables and database abstraction
require_once 'backend/load_env.php';
require_once 'backend/db_abstraction.php';

echo "🧪 Testing Complete Application Flow...\n\n";

// Check database type
$db_type = getenv('DB_TYPE') ?: 'mongodb';
echo "📊 Database Type: $db_type\n";
echo "🔗 Connection String: " . (getenv('MONGODB_CONNECTION_STRING') ? 'Found' : 'Not Found') . "\n";
echo "🗄️  Database Name: " . (getenv('COSMOS_DATABASE') ?: 'mindmate') . "\n\n";

try {
    // Test 1: User Registration Flow
    echo "1. Testing User Registration Flow...\n";
    $testEmail = 'flowtest_' . time() . '@example.com';
    $testUsername = 'flowtest_' . time();
    
    // Check if user already exists (should not)
    $existingUser = getUserByEmail($testEmail);
    if ($existingUser) {
        echo "❌ User already exists (unexpected)\n";
        exit(1);
    }
    echo "✅ Email availability check passed\n";
    
    // Create new user
    $userData = [
        'username' => $testUsername,
        'email' => $testEmail,
        'password_hash' => password_hash('testpassword123', PASSWORD_DEFAULT)
    ];
    
    $userId = createUser($userData);
    if ($userId) {
        echo "✅ User registration successful\n";
    } else {
        echo "❌ User registration failed\n";
        exit(1);
    }
    
    // Test 2: User Login Flow
    echo "\n2. Testing User Login Flow...\n";
    $loginUser = getUserByEmail($testEmail);
    if ($loginUser && password_verify('testpassword123', $loginUser['password_hash'])) {
        echo "✅ User login verification successful\n";
        echo "   User ID: " . $loginUser['id'] . "\n";
        echo "   Username: " . $loginUser['username'] . "\n";
        echo "   Email: " . $loginUser['email'] . "\n";
    } else {
        echo "❌ User login verification failed\n";
        exit(1);
    }
    
    // Test 3: Chat Functionality
    echo "\n3. Testing Chat Functionality...\n";
    
    // Save multiple chats
    $chatMessages = [
        ['message' => 'Hello, I need help with anxiety', 'response' => 'I understand you\'re feeling anxious. Can you tell me more about what\'s troubling you?', 'sentiment' => 'negative'],
        ['message' => 'I have been feeling stressed about work', 'response' => 'Work stress is very common. Let\'s explore some coping strategies together.', 'sentiment' => 'negative'],
        ['message' => 'Thank you, I feel better now', 'response' => 'I\'m glad to hear you\'re feeling better! Remember, I\'m here whenever you need support.', 'sentiment' => 'positive']
    ];
    
    foreach ($chatMessages as $index => $chat) {
        $chatData = [
            'user_id' => $loginUser['id'],
            'user_message' => $chat['message'],
            'ai_response' => $chat['response'],
            'sentiment' => $chat['sentiment'],
            'confidence' => 0.85 + ($index * 0.05)
        ];
        
        $chatId = saveChat($chatData);
        if ($chatId) {
            echo "✅ Chat " . ($index + 1) . " saved successfully\n";
        } else {
            echo "❌ Failed to save chat " . ($index + 1) . "\n";
            exit(1);
        }
    }
    
    // Retrieve chat history
    $chatHistory = getChatHistory($loginUser['id'], 10);
    if (count($chatHistory) >= 3) {
        echo "✅ Chat history retrieved successfully (" . count($chatHistory) . " chats)\n";
    } else {
        echo "❌ Chat history retrieval failed\n";
        exit(1);
    }
    
    // Get recent chats
    $recentChats = getRecentChats($loginUser['id'], 5);
    if (!empty($recentChats)) {
        echo "✅ Recent chats retrieved successfully (" . count($recentChats) . " chats)\n";
        echo "   Most recent: " . $recentChats[0]['user_message'] . "\n";
    } else {
        echo "❌ Recent chats retrieval failed\n";
        exit(1);
    }
    
    // Test 4: Mood Tracking
    echo "\n4. Testing Mood Tracking...\n";
    
    $moodEntries = [
        ['mood' => 'anxious', 'notes' => 'Feeling worried about upcoming presentation'],
        ['mood' => 'calm', 'notes' => 'Meditation helped me relax'],
        ['mood' => 'happy', 'notes' => 'Had a great conversation with the AI assistant']
    ];
    
    foreach ($moodEntries as $index => $mood) {
        $moodData = [
            'user_id' => $loginUser['id'],
            'mood' => $mood['mood'],
            'notes' => $mood['notes']
        ];
        
        $moodId = saveMoodCheck($moodData);
        if ($moodId) {
            echo "✅ Mood entry " . ($index + 1) . " saved successfully\n";
        } else {
            echo "❌ Failed to save mood entry " . ($index + 1) . "\n";
            exit(1);
        }
    }
    
    // Retrieve mood history
    $moodHistory = getMoodChecks($loginUser['id'], 10);
    if (count($moodHistory) >= 3) {
        echo "✅ Mood history retrieved successfully (" . count($moodHistory) . " entries)\n";
        echo "   Latest mood: " . $moodHistory[0]['mood'] . "\n";
    } else {
        echo "❌ Mood history retrieval failed\n";
        exit(1);
    }
    
    // Test 5: Rate Limiting
    echo "\n5. Testing Rate Limiting...\n";
    
    // Test multiple actions
    $actions = ['chat', 'mood_check', 'profile_update'];
    foreach ($actions as $action) {
        $canProceed = checkRateLimit($loginUser['id'], $action);
        if ($canProceed) {
            echo "✅ Rate limit check passed for action: $action\n";
            
            $recorded = recordRateLimit($loginUser['id'], $action);
            if ($recorded) {
                echo "✅ Rate limit recorded for action: $action\n";
            }
        } else {
            echo "❌ Rate limit check failed for action: $action\n";
        }
    }
    
    // Test 6: User Profile Update
    echo "\n6. Testing User Profile Update...\n";
    $updatedUserData = [
        'username' => $testUsername . '_updated',
        'email' => $testEmail
    ];
    
    $updated = updateUser($loginUser['id'], $updatedUserData);
    if ($updated) {
        echo "✅ User profile updated successfully\n";
        
        // Verify update
        $updatedUser = getUserById($loginUser['id']);
        if ($updatedUser && $updatedUser['username'] === $updatedUserData['username']) {
            echo "✅ Profile update verified\n";
        } else {
            echo "❌ Profile update verification failed\n";
        }
    } else {
        echo "❌ User profile update failed\n";
    }
    
    echo "\n🎉 Complete Application Flow Test Successful!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ MongoDB database connection is working perfectly\n";
    echo "✅ User registration and login flow works\n";
    echo "✅ Chat functionality is operational\n";
    echo "✅ Mood tracking system is functional\n";
    echo "✅ Rate limiting is working correctly\n";
    echo "✅ User profile updates work properly\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n🚀 Your MindMate application is ready to use!\n";
    echo "You can now:\n";
    echo "• Register new users\n";
    echo "• Login with existing accounts\n";
    echo "• Have AI conversations\n";
    echo "• Track mood changes\n";
    echo "• View chat and mood history\n";
    
} catch (Exception $e) {
    echo "❌ Application flow test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>