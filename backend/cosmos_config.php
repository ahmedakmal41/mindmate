<?php
// MindMate - Cosmos DB MongoDB Configuration

// MongoDB connection string
$mongodb_connection_string = getenv('MONGODB_CONNECTION_STRING') ?: 'mongodb://mindmate-cdb:undefined@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@';
$cosmos_database = getenv('COSMOS_DATABASE') ?: 'mindmate';

// MongoDB client
require_once 'vendor/autoload.php';
use MongoDB\Client;

// Container names
$containers = [
    'users' => 'users',
    'chats' => 'chats',
    'mood_checks' => 'mood_checks',
    'rate_limits' => 'rate_limits',
    'user_sessions' => 'user_sessions'
];

class CosmosDB {
    private $endpoint;
    private $key;
    private $database;
    private $api_version;
    
    public function __construct($endpoint, $key, $database, $api_version) {
        $this->endpoint = $endpoint;
        $this->key = $key;
        $this->database = $database;
        $this->api_version = $api_version;
    }
    
    private function getAuthHeader() {
        $date = gmdate('D, d M Y H:i:s T');
        $stringToSign = "get\n\n\n" . $date . "\n/";
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->key), true));
        return "type=master&ver=1.0&sig=" . $signature;
    }
    
    private function makeRequest($method, $resource, $body = null) {
        $url = $this->endpoint . $resource;
        $headers = [
            'Authorization: ' . $this->getAuthHeader(),
            'x-ms-date: ' . gmdate('D, d M Y H:i:s T'),
            'x-ms-version: ' . $this->api_version,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }
    
    public function createDocument($container, $document) {
        $document['id'] = uniqid();
        $document['_ts'] = time();
        
        $resource = "dbs/{$this->database}/colls/{$container}/docs";
        return $this->makeRequest('POST', $resource, $document);
    }
    
    public function getDocument($container, $id) {
        $resource = "dbs/{$this->database}/colls/{$container}/docs/{$id}";
        return $this->makeRequest('GET', $resource);
    }
    
    public function queryDocuments($container, $query) {
        $resource = "dbs/{$this->database}/colls/{$container}/docs";
        $body = [
            'query' => $query,
            'parameters' => []
        ];
        return $this->makeRequest('POST', $resource, $body);
    }
    
    public function updateDocument($container, $id, $document) {
        $document['id'] = $id;
        $document['_ts'] = time();
        
        $resource = "dbs/{$this->database}/colls/{$container}/docs/{$id}";
        return $this->makeRequest('PUT', $resource, $document);
    }
    
    public function deleteDocument($container, $id) {
        $resource = "dbs/{$this->database}/colls/{$container}/docs/{$id}";
        return $this->makeRequest('DELETE', $resource);
    }
}

// Initialize Cosmos DB connection
$cosmos = new CosmosDB($cosmos_endpoint, $cosmos_key, $cosmos_database, $cosmos_api_version);

// Database functions for Cosmos DB
function createUser($userData) {
    global $cosmos, $containers;
    
    $document = [
        'username' => $userData['username'],
        'email' => $userData['email'],
        'password_hash' => $userData['password_hash'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'last_login' => null,
        'is_active' => true
    ];
    
    return $cosmos->createDocument($containers['users'], $document);
}

function getUserByEmail($email) {
    global $cosmos, $containers;
    
    $query = "SELECT * FROM c WHERE c.email = @email";
    $result = $cosmos->queryDocuments($containers['users'], $query);
    
    if ($result['status'] === 200 && !empty($result['body']['Documents'])) {
        return $result['body']['Documents'][0];
    }
    
    return null;
}

function getUserById($id) {
    global $cosmos, $containers;
    
    $result = $cosmos->getDocument($containers['users'], $id);
    
    if ($result['status'] === 200) {
        return $result['body'];
    }
    
    return null;
}

function saveChat($chatData) {
    global $cosmos, $containers;
    
    $document = [
        'user_id' => $chatData['user_id'],
        'user_message' => $chatData['user_message'],
        'ai_response' => $chatData['ai_response'],
        'sentiment' => $chatData['sentiment'],
        'confidence' => $chatData['confidence'],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    return $cosmos->createDocument($containers['chats'], $document);
}

function getRecentChats($userId, $limit = 5) {
    global $cosmos, $containers;
    
    $query = "SELECT TOP @limit * FROM c WHERE c.user_id = @userId ORDER BY c.timestamp DESC";
    $result = $cosmos->queryDocuments($containers['chats'], $query);
    
    if ($result['status'] === 200) {
        return array_reverse($result['body']['Documents']);
    }
    
    return [];
}

function saveMoodCheck($moodData) {
    global $cosmos, $containers;
    
    $document = [
        'user_id' => $moodData['user_id'],
        'mood' => $moodData['mood'],
        'notes' => $moodData['notes'],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    return $cosmos->createDocument($containers['mood_checks'], $document);
}

function getMoodChecks($userId, $limit = 30) {
    global $cosmos, $containers;
    
    $query = "SELECT TOP @limit * FROM c WHERE c.user_id = @userId ORDER BY c.timestamp DESC";
    $result = $cosmos->queryDocuments($containers['mood_checks'], $query);
    
    if ($result['status'] === 200) {
        return $result['body']['Documents'];
    }
    
    return [];
}

function checkRateLimit($userId, $action) {
    global $cosmos, $containers;
    
    $oneMinuteAgo = date('Y-m-d H:i:s', strtotime('-1 minute'));
    $query = "SELECT COUNT(1) as count FROM c WHERE c.user_id = @userId AND c.action = @action AND c.created_at > @oneMinuteAgo";
    
    $result = $cosmos->queryDocuments($containers['rate_limits'], $query);
    
    if ($result['status'] === 200 && !empty($result['body']['Documents'])) {
        $count = $result['body']['Documents'][0]['count'];
        return $count < 10; // Rate limit: 10 requests per minute
    }
    
    return true;
}

function recordRateLimit($userId, $action) {
    global $cosmos, $containers;
    
    $document = [
        'user_id' => $userId,
        'action' => $action,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    return $cosmos->createDocument($containers['rate_limits'], $document);
}

// Initialize containers if they don't exist
function initializeCosmosContainers() {
    global $cosmos, $containers;
    
    foreach ($containers as $name => $container) {
        // Check if container exists and create if not
        $resource = "dbs/{$cosmos->database}/colls/{$container}";
        $result = $cosmos->makeRequest('GET', $resource);
        
        if ($result['status'] === 404) {
            // Container doesn't exist, create it
            $containerDef = [
                'id' => $container,
                'partitionKey' => [
                    'paths' => ['/id'],
                    'kind' => 'Hash'
                ]
            ];
            
            $cosmos->makeRequest('POST', "dbs/{$cosmos->database}/colls", $containerDef);
        }
    }
}

// Initialize containers on first run
initializeCosmosContainers();

?>
