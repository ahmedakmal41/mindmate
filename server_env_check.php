<?php
// Server Environment Check for MongoDB Connection

echo "🖥️  Server Environment Check\n";
echo "============================\n\n";

// Check if we're running on a web server
echo "📍 Environment Info:\n";
echo "   PHP SAPI: " . php_sapi_name() . "\n";
echo "   Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not set') . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
echo "   Script Path: " . __FILE__ . "\n";
echo "   Working Directory: " . getcwd() . "\n\n";

// Check .env file existence and permissions
echo "📄 .env File Check:\n";
$env_file = '.env';
if (file_exists($env_file)) {
    echo "✅ .env file exists\n";
    echo "   Path: " . realpath($env_file) . "\n";
    echo "   Size: " . filesize($env_file) . " bytes\n";
    echo "   Permissions: " . substr(sprintf('%o', fileperms($env_file)), -4) . "\n";
    echo "   Readable: " . (is_readable($env_file) ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ .env file not found\n";
    echo "   Looking for: " . realpath('.') . '/.env' . "\n";
}

// Try to load environment variables
echo "\n🔧 Environment Variable Loading:\n";
try {
    require_once 'backend/load_env.php';
    echo "✅ load_env.php loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Failed to load load_env.php: " . $e->getMessage() . "\n";
}

// Check environment variables
echo "\n🔍 Environment Variables:\n";
$env_vars = ['DB_TYPE', 'MONGODB_CONNECTION_STRING', 'COSMOS_DATABASE', 'AI_API_URL', 'PORT'];

foreach ($env_vars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        if ($var === 'MONGODB_CONNECTION_STRING') {
            // Show only first and last 10 characters for security
            $display_value = substr($value, 0, 20) . '...' . substr($value, -20);
        } else {
            $display_value = $value;
        }
        echo "✅ $var: $display_value\n";
    } else {
        echo "❌ $var: Not set\n";
    }
}

// Check if MongoDB extension is loaded
echo "\n🔌 PHP Extensions:\n";
if (extension_loaded('mongodb')) {
    echo "✅ MongoDB extension loaded\n";
    $version = phpversion('mongodb');
    echo "   Version: $version\n";
} else {
    echo "❌ MongoDB extension not loaded\n";
}

// Test MongoDB connection if possible
echo "\n🔗 MongoDB Connection Test:\n";
$connection_string = getenv('MONGODB_CONNECTION_STRING');
$database_name = getenv('COSMOS_DATABASE') ?: 'mindmate';

if ($connection_string) {
    try {
        $manager = new MongoDB\Driver\Manager($connection_string);
        
        // Test ping
        $command = new MongoDB\Driver\Command(['ping' => 1]);
        $cursor = $manager->executeCommand($database_name, $command);
        $result = $cursor->toArray();
        
        if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
            echo "✅ MongoDB connection successful\n";
        } else {
            echo "❌ MongoDB ping failed\n";
        }
    } catch (Exception $e) {
        echo "❌ MongoDB connection failed: " . $e->getMessage() . "\n";
        
        if (strpos($e->getMessage(), 'Authentication') !== false || 
            strpos($e->getMessage(), 'Invalid key') !== false) {
            echo "\n🔑 Authentication Error Detected!\n";
            echo "The connection string key is invalid or expired.\n";
        }
    }
} else {
    echo "❌ No connection string found\n";
}

// Show current .env content (safely)
echo "\n📝 Current .env Content (safe view):\n";
if (file_exists('.env')) {
    $content = file_get_contents('.env');
    $lines = explode("\n", $content);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, 'MONGODB_CONNECTION_STRING') === 0) {
            echo "MONGODB_CONNECTION_STRING=mongodb://[username]:[HIDDEN]@[host]...\n";
        } else {
            echo $line . "\n";
        }
    }
} else {
    echo "❌ .env file not accessible\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Next Steps:\n";
echo "1. If .env file is missing, create it with proper values\n";
echo "2. If connection string is invalid, update it from Azure Portal\n";
echo "3. Ensure web server has read permissions on .env file\n";
echo "4. Restart web server after making changes\n";
?>