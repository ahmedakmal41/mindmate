<?php
// Render Environment Debug Script
// Access this at: https://your-app.onrender.com/render_debug.php

header('Content-Type: text/plain');

echo "🐳 Render Docker Environment Debug\n";
echo "==================================\n\n";

// Basic environment info
echo "📊 Container Information:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Working Directory: " . getcwd() . "\n";
echo "   Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Built-in PHP server') . "\n";
echo "   Port: " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "\n\n";

// Check if we're on Render
echo "🌐 Render Detection:\n";
$render_service = getenv('RENDER_SERVICE_NAME');
$render_instance = getenv('RENDER_INSTANCE_ID');
if ($render_service) {
    echo "✅ Running on Render\n";
    echo "   Service: $render_service\n";
    echo "   Instance: " . ($render_instance ?: 'Unknown') . "\n";
} else {
    echo "❓ Not detected as Render environment\n";
}
echo "\n";

// Check environment variables
echo "🔍 Environment Variables:\n";
$env_vars = [
    'DB_TYPE',
    'MONGODB_CONNECTION_STRING', 
    'COSMOS_DATABASE',
    'AI_API_URL',
    'PORT',
    'RENDER_SERVICE_NAME',
    'RENDER_INSTANCE_ID'
];

foreach ($env_vars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        if ($var === 'MONGODB_CONNECTION_STRING') {
            // Show only connection format for security
            if (strpos($value, 'mongodb://') === 0) {
                echo "✅ $var: [SET - MongoDB format detected]\n";
                
                // Parse connection string safely
                if (preg_match('/mongodb:\/\/([^:]+):([^@]+)@([^\/]+)/', $value, $matches)) {
                    echo "   → Username: " . $matches[1] . "\n";
                    echo "   → Host: " . $matches[3] . "\n";
                    echo "   → Key Length: " . strlen($matches[2]) . " chars\n";
                }
            } else {
                echo "❌ $var: [SET - Invalid format]\n";
            }
        } else {
            echo "✅ $var: $value\n";
        }
    } else {
        echo "❌ $var: [NOT SET]\n";
    }
}

// Check PHP extensions
echo "\n🔌 PHP Extensions:\n";
$required_extensions = ['mongodb', 'curl', 'json', 'openssl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: Loaded\n";
        if ($ext === 'mongodb') {
            echo "   → Version: " . phpversion('mongodb') . "\n";
        }
    } else {
        echo "❌ $ext: Missing\n";
    }
}

// Test MongoDB connection
echo "\n🔗 MongoDB Connection Test:\n";
$connection_string = getenv('MONGODB_CONNECTION_STRING');
$database_name = getenv('COSMOS_DATABASE') ?: 'mindmate';

if (!$connection_string) {
    echo "❌ No connection string found\n";
    echo "   → Set MONGODB_CONNECTION_STRING in Render Dashboard\n";
} else {
    try {
        $manager = new MongoDB\Driver\Manager($connection_string);
        echo "✅ MongoDB Manager created\n";
        
        // Test ping with timeout
        $command = new MongoDB\Driver\Command(['ping' => 1]);
        $cursor = $manager->executeCommand($database_name, $command);
        $result = $cursor->toArray();
        
        if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
            echo "✅ MongoDB ping successful\n";
            echo "✅ Database: $database_name\n";
            
            // Test collections access
            try {
                $command = new MongoDB\Driver\Command(['listCollections' => 1]);
                $cursor = $manager->executeCommand($database_name, $command);
                $collections = $cursor->toArray();
                echo "✅ Collections accessible (" . count($collections) . " found)\n";
            } catch (Exception $e) {
                echo "⚠️  Collections access limited: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "❌ MongoDB ping failed\n";
        }
        
    } catch (MongoDB\Driver\Exception\AuthenticationException $e) {
        echo "❌ Authentication failed: " . $e->getMessage() . "\n";
        echo "   → Update MONGODB_CONNECTION_STRING in Render Dashboard\n";
        echo "   → Get fresh connection string from Azure Portal\n";
        
    } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
        echo "❌ Connection timeout: " . $e->getMessage() . "\n";
        echo "   → Check Azure Cosmos DB firewall settings\n";
        echo "   → Ensure Render IPs are whitelisted\n";
        
    } catch (Exception $e) {
        echo "❌ Connection failed: " . $e->getMessage() . "\n";
        echo "   → Error type: " . get_class($e) . "\n";
    }
}

// Check file system
echo "\n📁 File System Check:\n";
$important_files = [
    '.env',
    'backend/load_env.php',
    'backend/mongodb_config.php',
    'backend/db_abstraction.php',
    'composer.json',
    'vendor/autoload.php'
];

foreach ($important_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file: Found\n";
    } else {
        echo "❌ $file: Missing\n";
    }
}

// Test application loading
echo "\n🧪 Application Loading Test:\n";
try {
    // Test environment loading
    if (file_exists('backend/load_env.php')) {
        require_once 'backend/load_env.php';
        echo "✅ Environment loader: OK\n";
    }
    
    // Test database abstraction
    if (file_exists('backend/db_abstraction.php')) {
        require_once 'backend/db_abstraction.php';
        echo "✅ Database abstraction: OK\n";
        
        if (function_exists('getUserByEmail')) {
            echo "✅ Database functions: Available\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Application loading failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Status Summary:\n";

$issues = [];
if (!getenv('MONGODB_CONNECTION_STRING')) {
    $issues[] = "Missing MONGODB_CONNECTION_STRING";
}
if (!extension_loaded('mongodb')) {
    $issues[] = "MongoDB extension not loaded";
}

if (empty($issues)) {
    try {
        if ($connection_string) {
            $manager = new MongoDB\Driver\Manager($connection_string);
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $cursor = $manager->executeCommand($database_name, $command);
            $result = $cursor->toArray();
            
            if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
                echo "✅ RENDER DEPLOYMENT READY!\n";
                echo "Your MindMate app should work properly.\n";
            } else {
                echo "❌ MongoDB connection issues\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Connection test failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Issues found:\n";
    foreach ($issues as $issue) {
        echo "   • $issue\n";
    }
}

echo "\n📝 Next Steps:\n";
echo "1. Fix any issues shown above\n";
echo "2. Update environment variables in Render Dashboard\n";
echo "3. Redeploy your service\n";
echo "4. Test your application endpoints\n";

// Security note
echo "\n🔒 Security Note:\n";
echo "This debug script shows environment info.\n";
echo "Remove or restrict access in production.\n";
?>