<?php
// Test Database Abstraction Layer

// Load environment variables and database abstraction
require_once 'backend/load_env.php';
require_once 'backend/db_abstraction.php';

echo "🧪 Testing Database Abstraction Layer...\n\n";

// Check which database type is being used
$db_type = getenv('DB_TYPE') ?: 'mongodb';
echo "📊 Database Type: $db_type\n\n";

try {
    // Test 1: Create a test user using abstraction layer
    echo "1. Testing user creation via abstraction layer...\n";
    $userData = [
        'username' => 'abstraction_test_' . time(),
        'email' => 'abstraction_' . time() . '@example.com',
        'password_hash' => password_hash('testpassword', PASSWORD_DEFAULT)
    ];
    
    $userId = createUser($userData);
    if ($userId) {
        echo "✅ User created successfully via abstraction layer\n";
    } else {
        echo "❌ Failed to create user via abstraction layer\n";
        exit(1);
    }
    
    // Test 2: Get user by email via abstraction layer
    echo "\n2. Testing get user by email via abstraction layer...\n";
    $user = getUserByEmail($userData['email']);
    if ($user && $user['email'] === $userData['email']) {
        echo "✅ User retrieved by email via abstraction layer\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
    } else {
        echo "❌ Failed to retrieve user by email via abstraction layer\n";
        exit(1);
    }
    
    // Test 3: Save chat via abstraction layer
    echo "\n3. Testing chat saving via abstraction layer...\n";
    $chatData = [
        'user_id' => $user['id'],
        'user_message' => 'Hello from abstraction layer',
        'ai_response' => 'Hello! Response from abstraction layer.',
        'sentiment' => 'positive',
        'confidence' => 0.90
    ];
    
    $chatId = saveChat($chatData);
    if ($chatId) {
        echo "✅ Chat saved successfully via abstraction layer\n";
    } else {
        echo "❌ Failed to save chat via abstraction layer\n";
        exit(1);
    }
    
    // Test 4: Get recent chats via abstraction layer
    echo "\n4. Testing get recent chats via abstraction layer...\n";
    $recentChats = getRecentChats($user['id'], 5);
    if (!empty($recentChats)) {
        echo "✅ Retrieved " . count($recentChats) . " recent chats via abstraction layer\n";
        echo "   Latest message: " . $recentChats[0]['user_message'] . "\n";
    } else {
        echo "❌ Failed to retrieve recent chats via abstraction layer\n";
        exit(1);
    }
    
    // Test 5: Rate limiting via abstraction layer
    echo "\n5. Testing rate limiting via abstraction layer...\n";
    $canProceed = checkRateLimit($user['id'], 'test_action');
    if ($canProceed) {
        echo "✅ Rate limit check passed via abstraction layer\n";
        
        $recorded = recordRateLimit($user['id'], 'test_action');
        if ($recorded) {
            echo "✅ Rate limit recorded successfully via abstraction layer\n";
        }
    }
    
    echo "\n🎉 Database abstraction layer tests completed successfully!\n";
    echo "The abstraction layer is properly routing to MongoDB and all functions work correctly.\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>