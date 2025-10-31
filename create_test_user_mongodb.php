<?php
// MindMate - Create Test User for MongoDB Script

require_once 'backend/load_env.php';
require_once 'backend/db_abstraction.php';

echo "🚀 Creating test user in MongoDB...\n\n";

// Test user credentials
$username = "testuser";
$email = "test@mindmate.com";
$password = "password123";
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if user already exists
$existingUser = getUserByEmail($email);

if ($existingUser) {
    echo "✅ User already exists!\n";
    echo "   ID: " . $existingUser['id'] . "\n";
    echo "   Username: " . $existingUser['username'] . "\n";
    echo "   Email: " . $existingUser['email'] . "\n";
    echo "   Created: " . $existingUser['created_at'] . "\n\n";
    
    // Update password hash in case it changed
    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $filter = ['email' => $email];
        $update = [
            '$set' => [
                'password_hash' => $password_hash,
                'updated_at' => new MongoDB\BSON\UTCDateTime(),
                'is_active' => true
            ]
        ];
        $bulk->update($filter, $update);
        
        $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('users'), $bulk);
        
        if ($result->getModifiedCount() > 0) {
            echo "🔄 Password updated successfully!\n\n";
        } else {
            echo "ℹ️  Password unchanged (already up to date)\n\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Could not update password: " . $e->getMessage() . "\n\n";
    }
} else {
    // Create new user
    try {
        $userData = [
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash
        ];
        
        $result = createUser($userData);
        
        if ($result) {
            echo "✅ Test user created successfully!\n";
            echo "   User ID: " . (string)$result . "\n\n";
        } else {
            echo "❌ Failed to create user\n\n";
            exit(1);
        }
    } catch (Exception $e) {
        echo "❌ Error creating user: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}

echo "🎉 Test User Credentials:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Email:    test@mindmate.com\n";
echo "Password: password123\n";
echo "Username: testuser\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "✨ You can now sign in at: http://localhost:8000/login.php\n";

?>

