<?php
// Render Environment Setup Helper
// This helps configure environment variables for Render deployment

echo "🐳 Render Environment Setup Helper\n";
echo "==================================\n\n";

echo "This script helps you configure environment variables for Render deployment.\n\n";

// Check current environment
echo "1. Current Environment Check:\n";
echo "   Running on: " . (getenv('RENDER_SERVICE_NAME') ? 'Render' : 'Local') . "\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   MongoDB Extension: " . (extension_loaded('mongodb') ? 'Loaded' : 'Missing') . "\n\n";

// Show required environment variables
echo "2. Required Environment Variables for Render:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$required_vars = [
    'DB_TYPE' => [
        'value' => 'mongodb',
        'description' => 'Database type (always mongodb for this app)',
        'secret' => false
    ],
    'MONGODB_CONNECTION_STRING' => [
        'value' => 'mongodb://mindmate-cdb:[KEY]@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@',
        'description' => 'Azure Cosmos DB connection string (get from Azure Portal)',
        'secret' => true
    ],
    'COSMOS_DATABASE' => [
        'value' => 'mindmate',
        'description' => 'Database name in Cosmos DB',
        'secret' => false
    ],
    'AI_API_URL' => [
        'value' => 'https://aiengine-sable.vercel.app',
        'description' => 'AI Engine API endpoint',
        'secret' => false
    ],
    'PORT' => [
        'value' => '8080',
        'description' => 'Port for the application (Render sets this automatically)',
        'secret' => false
    ]
];

foreach ($required_vars as $var => $config) {
    $current_value = getenv($var);
    $status = $current_value ? '✅ SET' : '❌ NOT SET';
    $secret_icon = $config['secret'] ? '🔒' : '🔓';
    
    echo "$secret_icon $var: $status\n";
    echo "   Description: {$config['description']}\n";
    echo "   Expected: {$config['value']}\n";
    
    if ($current_value && $var !== 'MONGODB_CONNECTION_STRING') {
        echo "   Current: $current_value\n";
    } elseif ($current_value && $var === 'MONGODB_CONNECTION_STRING') {
        echo "   Current: [HIDDEN - Connection string is set]\n";
    }
    echo "\n";
}

echo "3. Render Dashboard Configuration Steps:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Go to https://dashboard.render.com\n";
echo "2. Find your 'mindmate' service\n";
echo "3. Click on the service name\n";
echo "4. Go to 'Environment' tab\n";
echo "5. Add/Update these environment variables:\n\n";

foreach ($required_vars as $var => $config) {
    echo "   Variable: $var\n";
    echo "   Value: {$config['value']}\n";
    echo "   Secret: " . ($config['secret'] ? 'YES (click 🔒 icon)' : 'NO') . "\n";
    echo "   ────────────────────────────────────────\n";
}

echo "\n4. Getting MongoDB Connection String:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Go to Azure Portal: https://portal.azure.com\n";
echo "2. Search for 'mindmate-cdb' (your Cosmos DB account)\n";
echo "3. Click on the Cosmos DB account\n";
echo "4. In left menu, click 'Connection String'\n";
echo "5. Copy the 'PRIMARY CONNECTION STRING' (MongoDB format)\n";
echo "6. Paste it as MONGODB_CONNECTION_STRING in Render\n\n";

echo "5. Deployment Process:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Set all environment variables in Render Dashboard\n";
echo "2. Click 'Manual Deploy' → 'Deploy latest commit'\n";
echo "3. Wait for deployment to complete\n";
echo "4. Test your app at: https://your-app-name.onrender.com\n";
echo "5. Check debug endpoint: https://your-app-name.onrender.com/render_debug.php\n\n";

echo "6. Troubleshooting:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• If you get 'Invalid key' error → Update MONGODB_CONNECTION_STRING\n";
echo "• If environment variables aren't loading → Check Render Dashboard settings\n";
echo "• If MongoDB extension missing → Check Dockerfile (should be installed)\n";
echo "• If connection timeout → Check Azure Cosmos DB firewall settings\n\n";

// Test current configuration if on Render
if (getenv('RENDER_SERVICE_NAME')) {
    echo "7. Current Render Configuration Test:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $all_set = true;
    foreach ($required_vars as $var => $config) {
        $value = getenv($var);
        if (!$value) {
            echo "❌ $var: Not set\n";
            $all_set = false;
        } else {
            echo "✅ $var: Set\n";
        }
    }
    
    if ($all_set) {
        echo "\n🧪 Testing MongoDB connection...\n";
        try {
            $connection_string = getenv('MONGODB_CONNECTION_STRING');
            $database_name = getenv('COSMOS_DATABASE');
            
            $manager = new MongoDB\Driver\Manager($connection_string);
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $cursor = $manager->executeCommand($database_name, $command);
            $result = $cursor->toArray();
            
            if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
                echo "✅ MongoDB connection successful!\n";
                echo "🎉 Your Render deployment is configured correctly!\n";
            } else {
                echo "❌ MongoDB ping failed\n";
            }
        } catch (Exception $e) {
            echo "❌ MongoDB connection failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n❌ Some environment variables are missing.\n";
        echo "Please set them in Render Dashboard and redeploy.\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📋 Summary:\n";
echo "1. Set environment variables in Render Dashboard\n";
echo "2. Get fresh MongoDB connection string from Azure\n";
echo "3. Redeploy your service\n";
echo "4. Test with render_debug.php\n";
echo "5. Your MindMate app should work!\n";
?>