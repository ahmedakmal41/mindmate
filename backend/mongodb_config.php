<?php
// MindMate - Cosmos DB MongoDB Configuration (Native Driver)

// MongoDB connection string
$mongodb_connection_string = getenv('MONGODB_CONNECTION_STRING') ?: 'mongodb://mindmate-cdb:undefined@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@';
$cosmos_database = getenv('COSMOS_DATABASE') ?: 'mindmate';

// Check if MongoDB extension is loaded
if (!extension_loaded('mongodb')) {
    error_log("MongoDB extension not loaded. Please install it with: pecl install mongodb");
    die("MongoDB extension is required. Please install it with: pecl install mongodb\n\nFor macOS:\n1. pecl install mongodb\n2. Add 'extension=mongodb.so' to your php.ini\n3. Restart your server\n\nYour php.ini location: " . php_ini_loaded_file());
}

// Collection names
$collections = [
    'users' => 'users',
    'chats' => 'chats',
    'mood_checks' => 'mood_checks',
    'rate_limits' => 'rate_limits',
    'user_sessions' => 'user_sessions'
];

class MongoDBConnection {
    private $manager;
    private $database_name;
    private $collections;
    
    public function __construct($connection_string, $database_name, $collections) {
        $this->manager = new MongoDB\Driver\Manager($connection_string);
        $this->database_name = $database_name;
        $this->collections = $collections;
    }
    
    public function getManager() {
        return $this->manager;
    }
    
    public function getDatabaseName() {
        return $this->database_name;
    }
    
    public function getCollectionName($collection_name) {
        return $this->collections[$collection_name];
    }
    
    public function getFullCollectionName($collection_name) {
        return $this->database_name . '.' . $this->collections[$collection_name];
    }
    
    public function createIndexes() {
        // Create indexes for better performance using native driver
        // Note: Some index types may not be supported in Cosmos DB
        try {
            // Users indexes - Critical for authentication
            $this->createIndex('users', ['email' => 1], ['unique' => true]);
            
        } catch (Exception $e) {
            error_log("Warning: Could not create email index - " . $e->getMessage());
        }
        
        try {
            $this->createIndex('users', ['username' => 1], ['unique' => true]);
        } catch (Exception $e) {
            error_log("Warning: Could not create username index - " . $e->getMessage());
        }
        
        try {
            // Chats indexes - For query performance
            $this->createIndex('chats', ['user_id' => 1, 'timestamp' => -1]);
        } catch (Exception $e) {
            error_log("Warning: Could not create chats compound index - " . $e->getMessage());
        }
        
        try {
            // Mood checks indexes
            $this->createIndex('mood_checks', ['user_id' => 1, 'timestamp' => -1]);
        } catch (Exception $e) {
            error_log("Warning: Could not create mood_checks index - " . $e->getMessage());
        }
        
        try {
            // Rate limits indexes (no TTL for Cosmos DB compatibility)
            $this->createIndex('rate_limits', ['user_id' => 1, 'action' => 1]);
        } catch (Exception $e) {
            error_log("Warning: Could not create rate_limits index - " . $e->getMessage());
        }
        
        try {
            // User sessions indexes
            $this->createIndex('user_sessions', ['user_id' => 1]);
        } catch (Exception $e) {
            error_log("Warning: Could not create user_sessions index - " . $e->getMessage());
        }
    }
    
    private function createIndex($collection_name, $keys, $options = []) {
        $command = new MongoDB\Driver\Command([
            'createIndexes' => $this->collections[$collection_name],
            'indexes' => [
                [
                    'key' => $keys,
                    'name' => $this->generateIndexName($keys),
                ] + $options
            ]
        ]);
        
        try {
            $this->manager->executeCommand($this->database_name, $command);
        } catch (Exception $e) {
            // Index might already exist, which is fine
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }
    
    private function generateIndexName($keys) {
        $parts = [];
        foreach ($keys as $field => $direction) {
            $parts[] = $field . '_' . $direction;
        }
        return implode('_', $parts);
    }
}

// Initialize MongoDB connection
try {
    $mongodb = new MongoDBConnection($mongodb_connection_string, $cosmos_database, $collections);
    
    // Try to create indexes (may fail if credentials are invalid)
    try {
        $mongodb->createIndexes();
    } catch (Exception $e) {
        error_log("Warning: Could not create indexes - " . $e->getMessage());
        // Continue anyway - indexes will be created on first successful connection
    }
} catch (Exception $e) {
    error_log("MongoDB Connection Error: " . $e->getMessage());
    die("Failed to connect to MongoDB. Please check your connection string.\n\nError: " . $e->getMessage() . "\n\nTo fix:\n1. Go to Azure Portal\n2. Navigate to your Cosmos DB account 'mindmate-cdb'\n3. Go to 'Connection String' section\n4. Copy the PRIMARY CONNECTION STRING\n5. Update MONGODB_CONNECTION_STRING in start-local.sh or .env\n");
}

// MongoDB functions using native driver
function createUserMongoDB($userData) {
    global $mongodb;
    
    $document = [
        'username' => $userData['username'],
        'email' => $userData['email'],
        'password_hash' => $userData['password_hash'],
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'updated_at' => new MongoDB\BSON\UTCDateTime(),
        'last_login' => null,
        'is_active' => true
    ];
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $insertedId = $bulk->insert($document);
    
    $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('users'), $bulk);
    
    return $result->getInsertedCount() > 0 ? $insertedId : false;
}

function getUserByEmailMongoDB($email) {
    global $mongodb;
    
    $filter = ['email' => $email];
    $query = new MongoDB\Driver\Query($filter);
    
    $cursor = $mongodb->getManager()->executeQuery($mongodb->getFullCollectionName('users'), $query);
    $users = $cursor->toArray();
    
    if (!empty($users)) {
        $user = $users[0];
        return [
            'id' => (string)$user->_id,
            'username' => $user->username,
            'email' => $user->email,
            'password_hash' => $user->password_hash,
            'created_at' => $user->created_at->toDateTime()->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at->toDateTime()->format('Y-m-d H:i:s'),
            'last_login' => isset($user->last_login) && $user->last_login ? $user->last_login->toDateTime()->format('Y-m-d H:i:s') : null,
            'is_active' => $user->is_active
        ];
    }
    
    return null;
}

function getUserByIdMongoDB($id) {
    global $mongodb;
    
    try {
        $filter = ['_id' => new MongoDB\BSON\ObjectId($id)];
        $query = new MongoDB\Driver\Query($filter);
        
        $cursor = $mongodb->getManager()->executeQuery($mongodb->getFullCollectionName('users'), $query);
        $users = $cursor->toArray();
        
        if (!empty($users)) {
            $user = $users[0];
            return [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'email' => $user->email,
                'password_hash' => $user->password_hash,
                'created_at' => $user->created_at->toDateTime()->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->toDateTime()->format('Y-m-d H:i:s'),
                'last_login' => isset($user->last_login) && $user->last_login ? $user->last_login->toDateTime()->format('Y-m-d H:i:s') : null,
                'is_active' => $user->is_active
            ];
        }
    } catch (Exception $e) {
        // Invalid ObjectId
        return null;
    }
    
    return null;
}

function updateUserMongoDB($id, $userData) {
    global $mongodb;
    
    try {
        $filter = ['_id' => new MongoDB\BSON\ObjectId($id)];
        $update = [
            '$set' => [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ];
        
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update($filter, $update);
        
        $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('users'), $bulk);
        
        return $result->getModifiedCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function saveChatMongoDB($chatData) {
    global $mongodb;
    
    $document = [
        'user_id' => $chatData['user_id'],
        'user_message' => $chatData['user_message'],
        'ai_response' => $chatData['ai_response'],
        'sentiment' => $chatData['sentiment'],
        'confidence' => $chatData['confidence'],
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $insertedId = $bulk->insert($document);
    
    $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('chats'), $bulk);
    
    return $result->getInsertedCount() > 0 ? $insertedId : false;
}

function getRecentChatsMongoDB($userId, $limit = 5) {
    global $mongodb;
    
    $filter = ['user_id' => $userId];
    $options = [
        'sort' => ['timestamp' => -1],
        'limit' => $limit
    ];
    $query = new MongoDB\Driver\Query($filter, $options);
    
    $cursor = $mongodb->getManager()->executeQuery($mongodb->getFullCollectionName('chats'), $query);
    $chats = $cursor->toArray();
    
    $result = [];
    foreach ($chats as $chat) {
        $result[] = [
            'user_message' => $chat->user_message,
            'ai_response' => $chat->ai_response,
            'sentiment' => $chat->sentiment,
            'timestamp' => $chat->timestamp->toDateTime()->format('Y-m-d H:i:s')
        ];
    }
    
    return array_reverse($result);
}

function getChatHistoryMongoDB($userId, $limit = 50) {
    global $mongodb;
    
    $filter = ['user_id' => $userId];
    $options = [
        'sort' => ['timestamp' => -1],
        'limit' => $limit
    ];
    $query = new MongoDB\Driver\Query($filter, $options);
    
    $cursor = $mongodb->getManager()->executeQuery($mongodb->getFullCollectionName('chats'), $query);
    $chats = $cursor->toArray();
    
    $result = [];
    foreach ($chats as $chat) {
        $result[] = [
            'user_message' => $chat->user_message,
            'ai_response' => $chat->ai_response,
            'sentiment' => $chat->sentiment,
            'timestamp' => $chat->timestamp->toDateTime()->format('Y-m-d H:i:s')
        ];
    }
    
    return $result;
}

function saveMoodCheckMongoDB($moodData) {
    global $mongodb;
    
    $document = [
        'user_id' => $moodData['user_id'],
        'mood' => $moodData['mood'],
        'notes' => $moodData['notes'],
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $insertedId = $bulk->insert($document);
    
    $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('mood_checks'), $bulk);
    
    return $result->getInsertedCount() > 0 ? $insertedId : false;
}

function getMoodChecksMongoDB($userId, $limit = 30) {
    global $mongodb;
    
    $filter = ['user_id' => $userId];
    $options = [
        'sort' => ['timestamp' => -1],
        'limit' => $limit
    ];
    $query = new MongoDB\Driver\Query($filter, $options);
    
    $cursor = $mongodb->getManager()->executeQuery($mongodb->getFullCollectionName('mood_checks'), $query);
    $moods = $cursor->toArray();
    
    $result = [];
    foreach ($moods as $mood) {
        $result[] = [
            'mood' => $mood->mood,
            'notes' => $mood->notes,
            'timestamp' => $mood->timestamp->toDateTime()->format('Y-m-d H:i:s')
        ];
    }
    
    return $result;
}

function checkRateLimitMongoDB($userId, $action) {
    global $mongodb;
    
    $oneMinuteAgo = new MongoDB\BSON\UTCDateTime(strtotime('-1 minute') * 1000);
    
    $filter = [
        'user_id' => $userId,
        'action' => $action,
        'created_at' => ['$gt' => $oneMinuteAgo]
    ];
    
    $command = new MongoDB\Driver\Command([
        'count' => $mongodb->getCollectionName('rate_limits'),
        'query' => $filter
    ]);
    
    $cursor = $mongodb->getManager()->executeCommand($mongodb->getDatabaseName(), $command);
    $result = $cursor->toArray();
    $count = $result[0]->n ?? 0;
    
    return $count < 10; // Rate limit: 10 requests per minute
}

function recordRateLimitMongoDB($userId, $action) {
    global $mongodb;
    
    $document = [
        'user_id' => $userId,
        'action' => $action,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $insertedId = $bulk->insert($document);
    
    $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('rate_limits'), $bulk);
    
    return $result->getInsertedCount() > 0 ? $insertedId : false;
}

function saveUserSession($sessionId, $userId, $ipAddress, $userAgent) {
    global $mongodb;
    
    $filter = ['_id' => $sessionId];
    $document = [
        '_id' => $sessionId,
        'user_id' => $userId,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'last_activity' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update($filter, $document, ['upsert' => true]);
    
    $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('user_sessions'), $bulk);
    
    return $result->getUpsertedCount() > 0 || $result->getModifiedCount() > 0;
}

function getUserSession($sessionId) {
    global $mongodb;
    
    $filter = ['_id' => $sessionId];
    $query = new MongoDB\Driver\Query($filter);
    
    $cursor = $mongodb->getManager()->executeQuery($mongodb->getFullCollectionName('user_sessions'), $query);
    $sessions = $cursor->toArray();
    
    if (!empty($sessions)) {
        $session = $sessions[0];
        return [
            'id' => (string)$session->_id,
            'user_id' => $session->user_id,
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
            'created_at' => $session->created_at->toDateTime()->format('Y-m-d H:i:s'),
            'last_activity' => $session->last_activity->toDateTime()->format('Y-m-d H:i:s')
        ];
    }
    
    return null;
}

function updateUserSessionActivity($sessionId) {
    global $mongodb;
    
    $filter = ['_id' => $sessionId];
    $update = ['$set' => ['last_activity' => new MongoDB\BSON\UTCDateTime()]];
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update($filter, $update);
    
    $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('user_sessions'), $bulk);
    
    return $result->getModifiedCount() > 0;
}

function deleteUserSession($sessionId) {
    global $mongodb;
    
    $filter = ['_id' => $sessionId];
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete($filter);
    
    $result = $mongodb->getManager()->executeBulkWrite($mongodb->getFullCollectionName('user_sessions'), $bulk);
    
    return $result->getDeletedCount() > 0;
}