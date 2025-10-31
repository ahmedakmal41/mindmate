<?php
// Fix MongoDB Connection Issues

echo "🔧 MongoDB Connection Fix Utility\n";
echo "=================================\n\n";

// Step 1: Check current environment
echo "1. Checking current environment...\n";
require_once 'backend/load_env.php';

$current_connection = getenv('MONGODB_CONNECTION_STRING');
$current_database = getenv('COSMOS_DATABASE') ?: 'mindmate';

if ($current_connection) {
    echo "✅ Current connection string found\n";
    
    // Parse and show details
    if (preg_match('/mongodb:\/\/([^:]+):([^@]+)@([^\/]+)\/?\?(.*)/', $current_connection, $matches)) {
        echo "   Account: " . $matches[1] . "\n";
        echo "   Host: " . $matches[3] . "\n";
        echo "   Database: " . $current_database . "\n";
    }
} else {
    echo "❌ No connection string found\n";
}

// Step 2: Test current connection
echo "\n2. Testing current connection...\n";
if ($current_connection) {
    try {
        $manager = new MongoDB\Driver\Manager($current_connection);
        $command = new MongoDB\Driver\Command(['ping' => 1]);
        $cursor = $manager->executeCommand($current_database, $command);
        $result = $cursor->toArray();
        
        if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
            echo "✅ Current connection works!\n";
            echo "   Your MongoDB connection is already working.\n";
            echo "   The issue might be server-specific or caching.\n\n";
            
            echo "🔄 Try these steps:\n";
            echo "1. Restart your web server\n";
            echo "2. Clear any PHP opcache\n";
            echo "3. Check file permissions on .env\n";
            exit(0);
        }
    } catch (Exception $e) {
        echo "❌ Connection test failed: " . $e->getMessage() . "\n";
        
        if (strpos($e->getMessage(), 'Authentication') !== false || 
            strpos($e->getMessage(), 'Invalid key') !== false) {
            echo "   → Authentication error detected\n";
        }
    }
} else {
    echo "❌ No connection string to test\n";
}

// Step 3: Provide instructions for getting new connection string
echo "\n3. Getting a fresh connection string from Azure:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🌐 Go to Azure Portal: https://portal.azure.com\n";
echo "📁 Navigate to: Resource Groups → Your Resource Group\n";
echo "🗄️  Find: 'mindmate-cdb' (Cosmos DB account)\n";
echo "🔑 Click: 'Connection String' or 'Keys' in left menu\n";
echo "📋 Copy: PRIMARY CONNECTION STRING (MongoDB format)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "4. Expected connection string format:\n";
echo "mongodb://[account-name]:[primary-key]@[account-name].mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@[account-name]@\n\n";

// Step 4: Interactive connection string update
echo "5. Update connection string:\n";
if (php_sapi_name() === 'cli') {
    echo "Enter your new MongoDB connection string (or press Enter to skip): ";
    $handle = fopen("php://stdin", "r");
    $new_connection = trim(fgets($handle));
    fclose($handle);
    
    if (!empty($new_connection) && strpos($new_connection, 'mongodb://') === 0) {
        echo "\n🧪 Testing new connection string...\n";
        
        try {
            $manager = new MongoDB\Driver\Manager($new_connection);
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $cursor = $manager->executeCommand($current_database, $command);
            $result = $cursor->toArray();
            
            if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
                echo "✅ New connection string works!\n";
                
                // Update .env file
                echo "📝 Updating .env file...\n";
                $env_content = file_get_contents('.env');
                $env_content = preg_replace(
                    '/MONGODB_CONNECTION_STRING=.*/',
                    'MONGODB_CONNECTION_STRING=' . $new_connection,
                    $env_content
                );
                
                if (file_put_contents('.env', $env_content)) {
                    echo "✅ .env file updated successfully!\n";
                    echo "🔄 Please restart your web server now.\n";
                } else {
                    echo "❌ Failed to update .env file\n";
                    echo "Please manually update MONGODB_CONNECTION_STRING in .env\n";
                }
            } else {
                echo "❌ New connection string test failed\n";
            }
        } catch (Exception $e) {
            echo "❌ New connection string failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Skipping connection string update.\n";
    }
} else {
    echo "Running in web mode - manual update required.\n";
    echo "Please update the MONGODB_CONNECTION_STRING in your .env file.\n";
}

// Step 5: Create a backup connection test
echo "\n6. Creating connection test script...\n";
$test_script = '<?php
// Quick MongoDB Connection Test
require_once "backend/load_env.php";

$connection = getenv("MONGODB_CONNECTION_STRING");
$database = getenv("COSMOS_DATABASE") ?: "mindmate";

if (!$connection) {
    die("❌ No connection string found\\n");
}

try {
    $manager = new MongoDB\\Driver\\Manager($connection);
    $command = new MongoDB\\Driver\\Command(["ping" => 1]);
    $cursor = $manager->executeCommand($database, $command);
    $result = $cursor->toArray();
    
    if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
        echo "✅ MongoDB connection successful!\\n";
        echo "Database: $database\\n";
        echo "Status: Ready\\n";
    } else {
        echo "❌ Ping failed\\n";
    }
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\\n";
}
?>';

file_put_contents('quick_mongo_test.php', $test_script);
echo "✅ Created 'quick_mongo_test.php' for future testing\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Summary:\n";
echo "1. Get fresh connection string from Azure Portal\n";
echo "2. Update MONGODB_CONNECTION_STRING in .env file\n";
echo "3. Restart web server\n";
echo "4. Run: php quick_mongo_test.php\n";
echo "5. Test your application\n";
?>