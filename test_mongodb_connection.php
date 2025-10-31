<?php
// Test MongoDB Connection

// Load environment variables
require_once 'backend/load_env.php';

echo "🧪 Testing MongoDB Connection...\n\n";

// Check if MongoDB extension is loaded
if (!extension_loaded('mongodb')) {
    echo "❌ MongoDB extension not loaded\n";
    exit(1);
}
echo "✅ MongoDB extension loaded\n";

// Check if composer autoload exists
if (!file_exists('vendor/autoload.php')) {
    echo "❌ Composer autoload not found. Run: composer install\n";
    exit(1);
}
echo "✅ Composer autoload found\n";

// Load MongoDB library
require_once 'vendor/autoload.php';
use MongoDB\Client;

// Get connection string from environment
$connection_string = getenv('MONGODB_CONNECTION_STRING');
$database_name = getenv('COSMOS_DATABASE') ?: 'mindmate';

if (!$connection_string) {
    echo "❌ MONGODB_CONNECTION_STRING not found in environment\n";
    echo "Please check your .env file\n";
    exit(1);
}
echo "✅ Connection string found\n";

// Test connection
try {
    echo "🔗 Attempting to connect to MongoDB...\n";
    
    $client = new Client($connection_string);
    $database = $client->selectDatabase($database_name);
    
    // Test connection by pinging the database
    $result = $database->command(['ping' => 1]);
    echo "✅ Successfully connected to MongoDB!\n";
    echo "📊 Database: $database_name\n";
    
    // Test creating a simple document
    echo "\n🧪 Testing document operations...\n";
    
    $testCollection = $database->selectCollection('test_connection');
    
    // Insert test document
    $result = $testCollection->insertOne([
        'test' => true,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
        'message' => 'Connection test successful'
    ]);
    
    if ($result->getInsertedId()) {
        echo "✅ Test document inserted successfully\n";
        
        // Read test document
        $document = $testCollection->findOne(['test' => true]);
        if ($document) {
            echo "✅ Test document retrieved successfully\n";
            
            // Clean up test document
            $testCollection->deleteOne(['_id' => $result->getInsertedId()]);
            echo "✅ Test document cleaned up\n";
        }
    }
    
    echo "\n🎉 MongoDB connection test completed successfully!\n";
    echo "Your MongoDB database is ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ MongoDB connection failed: " . $e->getMessage() . "\n\n";
    
    echo "🔧 Troubleshooting tips:\n";
    echo "1. Check your Azure Cosmos DB connection string\n";
    echo "2. Verify your Cosmos DB account is running\n";
    echo "3. Check firewall settings in Azure Portal\n";
    echo "4. Ensure your IP is whitelisted in Cosmos DB\n";
    
    exit(1);
}
?>