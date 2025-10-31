<?php
// Test MongoDB Connection using native driver

// Load environment variables
require_once 'backend/load_env.php';

echo "🧪 Testing MongoDB Connection (Native Driver)...\n\n";

// Check if MongoDB extension is loaded
if (!extension_loaded('mongodb')) {
    echo "❌ MongoDB extension not loaded\n";
    exit(1);
}
echo "✅ MongoDB extension loaded\n";

// Get connection string from environment
$connection_string = getenv('MONGODB_CONNECTION_STRING');
$database_name = getenv('COSMOS_DATABASE') ?: 'mindmate';

if (!$connection_string) {
    echo "❌ MONGODB_CONNECTION_STRING not found in environment\n";
    echo "Please check your .env file\n";
    exit(1);
}
echo "✅ Connection string found\n";

// Test connection using native driver
try {
    echo "🔗 Attempting to connect to MongoDB using native driver...\n";
    
    $manager = new MongoDB\Driver\Manager($connection_string);
    
    // Test connection with a simple command
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $cursor = $manager->executeCommand($database_name, $command);
    $result = $cursor->toArray();
    
    if (!empty($result) && isset($result[0]->ok) && $result[0]->ok == 1) {
        echo "✅ Successfully connected to MongoDB!\n";
        echo "📊 Database: $database_name\n";
        
        // Test inserting a document
        echo "\n🧪 Testing document operations...\n";
        
        $bulk = new MongoDB\Driver\BulkWrite;
        $doc = [
            'test' => true,
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'message' => 'Native driver connection test successful'
        ];
        $insertedId = $bulk->insert($doc);
        
        $result = $manager->executeBulkWrite($database_name . '.test_connection', $bulk);
        
        if ($result->getInsertedCount() > 0) {
            echo "✅ Test document inserted successfully\n";
            
            // Read the document back
            $filter = ['_id' => $insertedId];
            $query = new MongoDB\Driver\Query($filter);
            $cursor = $manager->executeQuery($database_name . '.test_connection', $query);
            $documents = $cursor->toArray();
            
            if (!empty($documents)) {
                echo "✅ Test document retrieved successfully\n";
                
                // Clean up - delete the test document
                $bulk = new MongoDB\Driver\BulkWrite;
                $bulk->delete(['_id' => $insertedId]);
                $result = $manager->executeBulkWrite($database_name . '.test_connection', $bulk);
                
                if ($result->getDeletedCount() > 0) {
                    echo "✅ Test document cleaned up\n";
                }
            }
        }
        
        echo "\n🎉 MongoDB connection test completed successfully!\n";
        echo "Your MongoDB database is ready to use with the native driver.\n";
        
    } else {
        echo "❌ Ping command failed\n";
        exit(1);
    }
    
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