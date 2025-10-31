<?php
// Azure App Service Environment Debug Script
// Access this at: https://your-app.azurewebsites.net/azure_debug.php

header('Content-Type: text/plain');

echo "☁️  Azure App Service Environment Debug\n";
echo "======================================\n\n";

// Basic environment info
echo "📊 Azure App Service Information:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "   Working Directory: " . getcwd() . "\n";
echo "   Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "   HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'Yes' : 'No') . "\n\n";

// Check if we're on Azure App Service
echo "🌐 Azure App Service Detection:\n";
$website_site_name = getenv('WEBSITE_SITE_NAME');
$website_resource_group = getenv('WEBSITE_RESOURCE_GROUP');
if ($website_site_name) {
    echo "✅ Running on Azure App Service\n";
    echo "   Site Name: $website_site_name\n";
    echo "   Resource Group: " . ($website_resource_group ?: 'Unknown') . "\n";
    echo "   Instance ID: " . (getenv('WEBSITE_INSTANCE_ID') ?: 'Unknown') . "\n";
} else {
    echo "❓ Not detected as Azure App Service\n";
}
echo "\n";

// Check environment variables
echo "🔍 Environment Variables:\n";
$env_vars = [
    'DB_TYPE',
    'MONGODB_CONNECTION_STRING', 
    'COSMOS_DATABASE',
    'AI_API_URL',
    'WEBSITE_SITE_NAME',
    'WEBSITE_RESOURCE_GROUP',
    'WEBSITE_INSTANCE_ID'
];

foreach ($env_vars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        if ($var === 'MONGODB_CONNECTION_STRING') {
            if (strpos($value, 'mongodb://') === 0) {
                echo "✅ $var: [SET - MongoDB format detected]\n";
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
?>/
/ Check PHP extensions
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
    echo "   → Set MONGODB_CONNECTION_STRING in Azure Portal\n";
} else {
    try {
        $manager = new MongoDB\Driver\Manager($connection_string);
        echo "✅ MongoDB Manager created\n";
        
        $command = new MongoDB\Driver\Command(['ping' => 1]);
        $cursor = $manager->executeCommand($database_name, $command);
        $result = $cursor->toArray();
        
        if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
            echo "✅ MongoDB ping successful\n";
            echo "✅ Database: $database_name\n";
            
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
        echo "   → Update MONGODB_CONNECTION_STRING in Azure Portal\n";
        
    } catch (Exception $e) {
        echo "❌ Connection failed: " . $e->getMessage() . "\n";
        echo "   → Error type: " . get_class($e) . "\n";
    }
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
                echo "✅ AZURE APP SERVICE READY!\n";
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
echo "2. Update environment variables in Azure Portal\n";
echo "3. Restart your App Service if needed\n";
echo "4. Test your application endpoints\n";
?>