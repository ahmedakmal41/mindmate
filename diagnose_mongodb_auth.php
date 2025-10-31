<?php
// Diagnose MongoDB Authentication Issues

require_once 'backend/load_env.php';

echo "🔍 MongoDB Authentication Diagnostics\n";
echo "=====================================\n\n";

// Get connection details
$connection_string = getenv('MONGODB_CONNECTION_STRING');
$database_name = getenv('COSMOS_DATABASE') ?: 'mindmate';

if (!$connection_string) {
    echo "❌ MONGODB_CONNECTION_STRING not found in environment\n";
    exit(1);
}

// Parse connection string to show details (without exposing the full key)
if (preg_match('/mongodb:\/\/([^:]+):([^@]+)@([^\/]+)\/?\?(.*)/', $connection_string, $matches)) {
    $username = $matches[1];
    $password = $matches[2];
    $host = $matches[3];
    $params = $matches[4];
    
    echo "📊 Connection Details:\n";
    echo "   Username: $username\n";
    echo "   Host: $host\n";
    echo "   Database: $database_name\n";
    echo "   Password: " . substr($password, 0, 10) . "..." . substr($password, -10) . " (truncated)\n";
    echo "   Parameters: $params\n\n";
} else {
    echo "❌ Could not parse connection string format\n";
    exit(1);
}

// Test different connection approaches
echo "🧪 Testing Connection Methods:\n\n";

// Test 1: Basic connection test
echo "1. Testing basic connection...\n";
try {
    $manager = new MongoDB\Driver\Manager($connection_string);
    echo "✅ Manager created successfully\n";
} catch (Exception $e) {
    echo "❌ Manager creation failed: " . $e->getMessage() . "\n";
    
    // Check if it's an authentication error
    if (strpos($e->getMessage(), 'Authentication') !== false || 
        strpos($e->getMessage(), 'Invalid key') !== false) {
        echo "\n🔧 Authentication Error Detected!\n";
        echo "This usually means:\n";
        echo "1. The connection string key has expired\n";
        echo "2. The key was regenerated in Azure Portal\n";
        echo "3. The username or password is incorrect\n";
        echo "4. The Cosmos DB account is not accessible\n\n";
        
        echo "🛠️  How to fix:\n";
        echo "1. Go to Azure Portal (https://portal.azure.com)\n";
        echo "2. Navigate to your Cosmos DB account 'mindmate-cdb'\n";
        echo "3. Go to 'Connection String' or 'Keys' section\n";
        echo "4. Copy the PRIMARY CONNECTION STRING\n";
        echo "5. Update the MONGODB_CONNECTION_STRING in your .env file\n";
        echo "6. Make sure to use the MongoDB connection string, not SQL\n\n";
        
        echo "📝 Expected format:\n";
        echo "mongodb://[username]:[password]@[host]:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@[account-name]@\n\n";
    }
    
    exit(1);
}

// Test 2: Simple ping command
echo "\n2. Testing ping command...\n";
try {
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $cursor = $manager->executeCommand($database_name, $command);
    $result = $cursor->toArray();
    
    if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
        echo "✅ Ping successful - connection is working!\n";
    } else {
        echo "❌ Ping failed - unexpected response\n";
    }
} catch (Exception $e) {
    echo "❌ Ping failed: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Authentication') !== false) {
        echo "\n🔑 Authentication failed during ping\n";
        echo "The connection string credentials are invalid.\n";
    }
    
    exit(1);
}

// Test 3: List collections (requires read permissions)
echo "\n3. Testing collection access...\n";
try {
    $command = new MongoDB\Driver\Command(['listCollections' => 1]);
    $cursor = $manager->executeCommand($database_name, $command);
    $collections = $cursor->toArray();
    
    echo "✅ Collection access successful\n";
    echo "   Found " . count($collections) . " collections\n";
    
    if (!empty($collections)) {
        echo "   Collections: ";
        $names = array_map(function($col) { return $col->name; }, $collections);
        echo implode(', ', $names) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Collection access failed: " . $e->getMessage() . "\n";
}

// Test 4: Simple query test
echo "\n4. Testing simple query...\n";
try {
    $query = new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectId('000000000000000000000000')]);
    $cursor = $manager->executeQuery($database_name . '.users', $query);
    $result = $cursor->toArray();
    
    echo "✅ Query test successful (no results expected)\n";
} catch (Exception $e) {
    echo "❌ Query test failed: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Authentication') !== false) {
        echo "   This confirms the authentication issue\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Summary:\n";
echo "If you see authentication errors above, you need to:\n";
echo "1. Get a fresh connection string from Azure Portal\n";
echo "2. Update your .env file with the new connection string\n";
echo "3. Restart your web server\n";
echo "4. Test again\n";
?>