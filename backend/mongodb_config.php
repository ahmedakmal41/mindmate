<?php
// MindMate - Cosmos DB MongoDB Configuration

// MongoDB connection string
$mongodb_connection_string = getenv('MONGODB_CONNECTION_STRING') ?: 'mongodb://mindmate-cdb:undefined@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@';
$cosmos_database = getenv('COSMOS_DATABASE') ?: 'mindmate';

// MongoDB client
require_once 'vendor/autoload.php';
use MongoDB\Client;

// Collection names
$collections = [
    'users' => 'users',
    'chats' => 'chats',
    'mood_checks' => 'mood_checks',
    'rate_limits' => 'rate_limits',
    'user_sessions' => 'user_sessions'
];

class MongoDBConnection {
    private $client;
    private $database;
    private $collections;
    
    public function __construct($connection_string, $database_name, $collections) {
        $this->client = new Client($connection_string);
        $this->database = $this->client->selectDatabase($database_name);
        $this->collections = $collections;
    }
    
    public function getCollection($collection_name) {
        return $this->database->selectCollection($this->collections[$collection_name]);
    }
    
    public function createIndexes() {
        // Create indexes for better performance
        $users = $this->getCollection('users');
        $chats = $this->getCollection('chats');
        $mood_checks = $this->getCollection('mood_checks');
        $rate_limits = $this->getCollection('rate_limits');
        $user_sessions = $this->getCollection('user_sessions');
        
        // Users indexes
        $users->createIndex(['email' => 1], ['unique' => true]);
        $users->createIndex(['username' => 1], ['unique' => true]);
        
        // Chats indexes
        $chats->createIndex(['user_id' => 1, 'timestamp' => -1]);
        $chats->createIndex(['timestamp' => -1]);
        
        // Mood checks indexes
        $mood_checks->createIndex(['user_id' => 1, 'timestamp' => -1]);
        $mood_checks->createIndex(['timestamp' => -1]);
        
        // Rate limits indexes
        $rate_limits->createIndex(['user_id' => 1, 'action' => 1, 'created_at' => 1]);
        $rate_limits->createIndex(['created_at' => 1], ['expireAfterSeconds' => 3600]); // TTL for 1 hour
        
        // User sessions indexes
        $user_sessions->createIndex(['user_id' => 1]);
        $user_sessions->createIndex(['last_activity' => 1], ['expireAfterSeconds' => 604800]); // TTL for 7 days
    }
}

// Initialize MongoDB connection
$mongodb = new MongoDBConnection($mongodb_connection_string, $cosmos_database, $collections);

// Create indexes
$mongodb->createIndexes();

// Note: Global convenience functions are defined in db_abstraction.php
// These MongoDB-specific implementations are called by the DatabaseAbstraction class

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
    
    $result = $mongodb->getCollection('users')->insertOne($document);
    return $result->getInsertedId();
}

function getUserByEmailMongoDB($email) {
    global $mongodb;
    
    $user = $mongodb->getCollection('users')->findOne(['email' => $email]);
    
    if ($user) {
        // Convert MongoDB document to array
        return [
            'id' => (string)$user['_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'password_hash' => $user['password_hash'],
            'created_at' => $user['created_at']->toDateTime()->format('Y-m-d H:i:s'),
            'updated_at' => $user['updated_at']->toDateTime()->format('Y-m-d H:i:s'),
            'last_login' => $user['last_login'] ? $user['last_login']->toDateTime()->format('Y-m-d H:i:s') : null,
            'is_active' => $user['is_active']
        ];
    }
    
    return null;
}

function getUserByIdMongoDB($id) {
    global $mongodb;
    
    try {
        $user = $mongodb->getCollection('users')->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        
        if ($user) {
            return [
                'id' => (string)$user['_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'password_hash' => $user['password_hash'],
                'created_at' => $user['created_at']->toDateTime()->format('Y-m-d H:i:s'),
                'updated_at' => $user['updated_at']->toDateTime()->format('Y-m-d H:i:s'),
                'last_login' => $user['last_login'] ? $user['last_login']->toDateTime()->format('Y-m-d H:i:s') : null,
                'is_active' => $user['is_active']
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
    
    $update = [
        '$set' => [
            'username' => $userData['username'],
            'email' => $userData['email'],
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];
    
    $result = $mongodb->getCollection('users')->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        $update
    );
    
    return $result->getModifiedCount() > 0;
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
    
    $result = $mongodb->getCollection('chats')->insertOne($document);
    return $result->getInsertedId();
}

function getRecentChatsMongoDB($userId, $limit = 5) {
    global $mongodb;
    
    $chats = $mongodb->getCollection('chats')->find(
        ['user_id' => $userId],
        [
            'sort' => ['timestamp' => -1],
            'limit' => $limit
        ]
    );
    
    $result = [];
    foreach ($chats as $chat) {
        $result[] = [
            'user_message' => $chat['user_message'],
            'ai_response' => $chat['ai_response'],
            'sentiment' => $chat['sentiment'],
            'timestamp' => $chat['timestamp']->toDateTime()->format('Y-m-d H:i:s')
        ];
    }
    
    return array_reverse($result);
}

function getChatHistoryMongoDB($userId, $limit = 50) {
    global $mongodb;
    
    $chats = $mongodb->getCollection('chats')->find(
        ['user_id' => $userId],
        [
            'sort' => ['timestamp' => -1],
            'limit' => $limit
        ]
    );
    
    $result = [];
    foreach ($chats as $chat) {
        $result[] = [
            'user_message' => $chat['user_message'],
            'ai_response' => $chat['ai_response'],
            'sentiment' => $chat['sentiment'],
            'timestamp' => $chat['timestamp']->toDateTime()->format('Y-m-d H:i:s')
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
    
    $result = $mongodb->getCollection('mood_checks')->insertOne($document);
    return $result->getInsertedId();
}

function getMoodChecksMongoDB($userId, $limit = 30) {
    global $mongodb;
    
    $moods = $mongodb->getCollection('mood_checks')->find(
        ['user_id' => $userId],
        [
            'sort' => ['timestamp' => -1],
            'limit' => $limit
        ]
    );
    
    $result = [];
    foreach ($moods as $mood) {
        $result[] = [
            'mood' => $mood['mood'],
            'notes' => $mood['notes'],
            'timestamp' => $mood['timestamp']->toDateTime()->format('Y-m-d H:i:s')
        ];
    }
    
    return $result;
}

function checkRateLimitMongoDB($userId, $action) {
    global $mongodb;
    
    $oneMinuteAgo = new MongoDB\BSON\UTCDateTime(strtotime('-1 minute') * 1000);
    
    $count = $mongodb->getCollection('rate_limits')->countDocuments([
        'user_id' => $userId,
        'action' => $action,
        'created_at' => ['$gt' => $oneMinuteAgo]
    ]);
    
    return $count < 10; // Rate limit: 10 requests per minute
}

function recordRateLimitMongoDB($userId, $action) {
    global $mongodb;
    
    $document = [
        'user_id' => $userId,
        'action' => $action,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $result = $mongodb->getCollection('rate_limits')->insertOne($document);
    return $result->getInsertedId();
}

function saveUserSession($sessionId, $userId, $ipAddress, $userAgent) {
    global $mongodb;
    
    $document = [
        '_id' => $sessionId,
        'user_id' => $userId,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'last_activity' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $result = $mongodb->getCollection('user_sessions')->replaceOne(
        ['_id' => $sessionId],
        $document,
        ['upsert' => true]
    );
    
    return $result->getUpsertedId() || $result->getModifiedCount() > 0;
}

function getUserSession($sessionId) {
    global $mongodb;
    
    $session = $mongodb->getCollection('user_sessions')->findOne(['_id' => $sessionId]);
    
    if ($session) {
        return [
            'id' => (string)$session['_id'],
            'user_id' => $session['user_id'],
            'ip_address' => $session['ip_address'],
            'user_agent' => $session['user_agent'],
            'created_at' => $session['created_at']->toDateTime()->format('Y-m-d H:i:s'),
            'last_activity' => $session['last_activity']->toDateTime()->format('Y-m-d H:i:s')
        ];
    }
    
    return null;
}

function updateUserSessionActivity($sessionId) {
    global $mongodb;
    
    $result = $mongodb->getCollection('user_sessions')->updateOne(
        ['_id' => $sessionId],
        ['$set' => ['last_activity' => new MongoDB\BSON\UTCDateTime()]]
    );
    
    return $result->getModifiedCount() > 0;
}

function deleteUserSession($sessionId) {
    global $mongodb;
    
    $result = $mongodb->getCollection('user_sessions')->deleteOne(['_id' => $sessionId]);
    return $result->getDeletedCount() > 0;
}

?>
