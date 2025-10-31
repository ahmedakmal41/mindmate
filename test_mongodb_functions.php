<?php
// Test MongoDB Functions with Native Driver

// Load environment variables and MongoDB config
require_once 'backend/load_env.php';
require_once 'backend/mongodb_config.php';

echo "🧪 Testing MongoDB Functions...\n\n";

try {
    // Test 1: Create a test user
    echo "1. Testing user creation...\n";
    $userData = [
        'username' => 'testuser_' . time(),
        'email' => 'test_' . time() . '@example.com',
        'password_hash' => password_hash('testpassword', PASSWORD_DEFAULT)
    ];
    
    $userId = createUserMongoDB($userData);
    if ($userId) {
        echo "✅ User created successfully with ID: " . (string)$userId . "\n";
    } else {
        echo "❌ Failed to create user\n";
        exit(1);
    }
    
    // Test 2: Get user by email
    echo "\n2. Testing get user by email...\n";
    $user = getUserByEmailMongoDB($userData['email']);
    if ($user && $user['email'] === $userData['email']) {
        echo "✅ User retrieved by email successfully\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Created: " . $user['created_at'] . "\n";
    } else {
        echo "❌ Failed to retrieve user by email\n";
        exit(1);
    }
    
    // Test 3: Get user by ID
    echo "\n3. Testing get user by ID...\n";
    $userById = getUserByIdMongoDB($user['id']);
    if ($userById && $userById['id'] === $user['id']) {
        echo "✅ User retrieved by ID successfully\n";
    } else {
        echo "❌ Failed to retrieve user by ID\n";
        exit(1);
    }
    
    // Test 4: Save a chat
    echo "\n4. Testing chat saving...\n";
    $chatData = [
        'user_id' => $user['id'],
        'user_message' => 'Hello, this is a test message',
        'ai_response' => 'Hello! This is a test AI response.',
        'sentiment' => 'positive',
        'confidence' => 0.85
    ];
    
    $chatId = saveChatMongoDB($chatData);
    if ($chatId) {
        echo "✅ Chat saved successfully with ID: " . (string)$chatId . "\n";
    } else {
        echo "❌ Failed to save chat\n";
        exit(1);
    }
    
    // Test 5: Get recent chats
    echo "\n5. Testing get recent chats...\n";
    $recentChats = getRecentChatsMongoDB($user['id'], 5);
    if (!empty($recentChats)) {
        echo "✅ Retrieved " . count($recentChats) . " recent chats\n";
        echo "   Latest message: " . $recentChats[0]['user_message'] . "\n";
    } else {
        echo "❌ Failed to retrieve recent chats\n";
        exit(1);
    }
    
    // Test 6: Save mood check
    echo "\n6. Testing mood check saving...\n";
    $moodData = [
        'user_id' => $user['id'],
        'mood' => 'happy',
        'notes' => 'Feeling great today!'
    ];
    
    $moodId = saveMoodCheckMongoDB($moodData);
    if ($moodId) {
        echo "✅ Mood check saved successfully with ID: " . (string)$moodId . "\n";
    } else {
        echo "❌ Failed to save mood check\n";
        exit(1);
    }
    
    // Test 7: Get mood checks
    echo "\n7. Testing get mood checks...\n";
    $moodChecks = getMoodChecksMongoDB($user['id'], 10);
    if (!empty($moodChecks)) {
        echo "✅ Retrieved " . count($moodChecks) . " mood checks\n";
        echo "   Latest mood: " . $moodChecks[0]['mood'] . "\n";
    } else {
        echo "❌ Failed to retrieve mood checks\n";
        exit(1);
    }
    
    // Test 8: Rate limiting
    echo "\n8. Testing rate limiting...\n";
    $canProceed = checkRateLimitMongoDB($user['id'], 'chat');
    if ($canProceed) {
        echo "✅ Rate limit check passed\n";
        
        $recorded = recordRateLimitMongoDB($user['id'], 'chat');
        if ($recorded) {
            echo "✅ Rate limit recorded successfully\n";
        } else {
            echo "❌ Failed to record rate limit\n";
        }
    } else {
        echo "❌ Rate limit check failed\n";
    }
    
    // Test 9: Session management
    echo "\n9. Testing session management...\n";
    $sessionId = 'test_session_' . time();
    $saved = saveUserSession($sessionId, $user['id'], '127.0.0.1', 'Test User Agent');
    if ($saved) {
        echo "✅ User session saved successfully\n";
        
        $session = getUserSession($sessionId);
        if ($session && $session['user_id'] === $user['id']) {
            echo "✅ User session retrieved successfully\n";
            
            $updated = updateUserSessionActivity($sessionId);
            if ($updated) {
                echo "✅ Session activity updated successfully\n";
            }
        }
    }
    
    echo "\n🎉 All MongoDB function tests completed successfully!\n";
    echo "Your MongoDB database connection and functions are working properly.\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>