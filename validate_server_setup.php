<?php
// Validate Server Setup for MongoDB

header('Content-Type: text/plain');

echo "🖥️  MindMate Server Setup Validation\n";
echo "====================================\n\n";

// Basic PHP info
echo "📊 Server Information:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "   Current Path: " . __DIR__ . "\n\n";

// Check required files
echo "📁 Required Files Check:\n";
$required_files = [
    '.env',
    'backend/load_env.php',
    'backend/mongodb_config.php',
    'backend/db_abstraction.php',
    'backend/config.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file - Found\n";
    } else {
        echo "❌ $file - Missing\n";
    }
}

// Check PHP extensions
echo "\n🔌 PHP Extensions:\n";
$required_extensions = ['mongodb', 'curl', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext - Loaded\n";
    } else {
        echo "❌ $ext - Missing\n";
    }
}

// Load environment
echo "\n🔧 Environment Loading:\n";
try {
    if (file_exists('backend/load_env.php')) {
        require_once 'backend/load_env.php';
        echo "✅ Environment loader included\n";
    } else {
        echo "❌ Environment loader not found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Environment loading failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check environment variables
echo "\n🔍 Environment Variables:\n";
$db_type = getenv('DB_TYPE');
$connection_string = getenv('MONGODB_CONNECTION_STRING');
$database_name = getenv('COSMOS_DATABASE');

echo "   DB_TYPE: " . ($db_type ?: 'Not set') . "\n";
echo "   MONGODB_CONNECTION_STRING: " . ($connection_string ? 'Set (' . strlen($connection_string) . ' chars)' : 'Not set') . "\n";
echo "   COSMOS_DATABASE: " . ($database_name ?: 'Not set') . "\n";

// Test MongoDB connection
echo "\n🔗 MongoDB Connection Test:\n";
if (!$connection_string) {
    echo "❌ No connection string available\n";
    echo "\n🛠️  Fix Required:\n";
    echo "1. Check if .env file exists and is readable\n";
    echo "2. Ensure MONGODB_CONNECTION_STRING is set in .env\n";
    echo "3. Get fresh connection string from Azure Portal\n";
    exit(1);
}

try {
    $manager = new MongoDB\Driver\Manager($connection_string);
    echo "✅ MongoDB Manager created\n";
    
    // Test ping
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $cursor = $manager->executeCommand($database_name ?: 'mindmate', $command);
    $result = $cursor->toArray();
    
    if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
        echo "✅ MongoDB ping successful\n";
        echo "✅ Database connection working!\n";
        
        // Test a simple query
        $query = new MongoDB\Driver\Query([], ['limit' => 1]);
        $cursor = $manager->executeQuery(($database_name ?: 'mindmate') . '.users', $query);
        echo "✅ Database query test passed\n";
        
    } else {
        echo "❌ MongoDB ping failed\n";
    }
    
} catch (MongoDB\Driver\Exception\AuthenticationException $e) {
    echo "❌ Authentication failed: " . $e->getMessage() . "\n";
    echo "\n🔑 Authentication Error!\n";
    echo "The connection string key is invalid or expired.\n";
    echo "\n🛠️  How to fix:\n";
    echo "1. Go to Azure Portal (https://portal.azure.com)\n";
    echo "2. Navigate to your Cosmos DB account 'mindmate-cdb'\n";
    echo "3. Go to 'Connection String' section\n";
    echo "4. Copy the PRIMARY CONNECTION STRING\n";
    echo "5. Update MONGODB_CONNECTION_STRING in .env file\n";
    echo "6. Restart web server\n";
    
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
}

// Test application components
echo "\n🧪 Application Components Test:\n";
try {
    if (file_exists('backend/db_abstraction.php')) {
        require_once 'backend/db_abstraction.php';
        echo "✅ Database abstraction loaded\n";
        
        // Test a simple function
        if (function_exists('getUserByEmail')) {
            echo "✅ Database functions available\n";
        } else {
            echo "❌ Database functions not available\n";
        }
    } else {
        echo "❌ Database abstraction not found\n";
    }
} catch (Exception $e) {
    echo "❌ Application component test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Status Summary:\n";

if (extension_loaded('mongodb') && $connection_string) {
    try {
        $manager = new MongoDB\Driver\Manager($connection_string);
        $command = new MongoDB\Driver\Command(['ping' => 1]);
        $cursor = $manager->executeCommand($database_name ?: 'mindmate', $command);
        $result = $cursor->toArray();
        
        if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
            echo "✅ SERVER READY - MongoDB connection is working!\n";
            echo "Your MindMate application should work properly.\n";
        } else {
            echo "❌ SERVER NOT READY - MongoDB connection issues\n";
        }
    } catch (Exception $e) {
        echo "❌ SERVER NOT READY - " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ SERVER NOT READY - Missing requirements\n";
}

echo "\nRun this script after making any changes to verify the fix.\n";
?>