<?php
// MindMate - Database Abstraction Layer
// Supports both MySQL and Cosmos DB

require_once 'config.php';

// Determine database type
$db_type = getenv('DB_TYPE') ?: 'mysql';

if ($db_type === 'cosmosdb') {
    require_once 'cosmos_config.php';
} elseif ($db_type === 'mongodb') {
    require_once 'mongodb_config.php';
} else {
    require_once 'db_connect.php';
}

// Database abstraction functions
class DatabaseAbstraction {
    private $db_type;
    private $mysql_conn;
    private $cosmos;
    private $mongodb;
    
    public function __construct() {
        global $db_type, $conn, $cosmos, $mongodb;
        
        $this->db_type = $db_type;
        
        if ($db_type === 'mysql') {
            $this->mysql_conn = $conn;
        } elseif ($db_type === 'cosmosdb') {
            $this->cosmos = $cosmos;
        } elseif ($db_type === 'mongodb') {
            $this->mongodb = $mongodb;
        }
    }
    
    // User functions
    public function createUser($userData) {
        if ($this->db_type === 'mysql') {
            return $this->createUserMySQL($userData);
        } elseif ($this->db_type === 'cosmosdb') {
            return createUser($userData);
        } else {
            return createUser($userData);
        }
    }
    
    public function getUserByEmail($email) {
        if ($this->db_type === 'mysql') {
            return $this->getUserByEmailMySQL($email);
        } elseif ($this->db_type === 'cosmosdb') {
            return getUserByEmail($email);
        } else {
            return getUserByEmail($email);
        }
    }
    
    public function getUserById($id) {
        if ($this->db_type === 'mysql') {
            return $this->getUserByIdMySQL($id);
        } elseif ($this->db_type === 'cosmosdb') {
            return getUserById($id);
        } else {
            return getUserById($id);
        }
    }
    
    public function updateUser($id, $userData) {
        if ($this->db_type === 'mysql') {
            return $this->updateUserMySQL($id, $userData);
        } elseif ($this->db_type === 'cosmosdb') {
            return $this->updateUserCosmos($id, $userData);
        } else {
            return updateUser($id, $userData);
        }
    }
    
    // Chat functions
    public function saveChat($chatData) {
        if ($this->db_type === 'mysql') {
            return $this->saveChatMySQL($chatData);
        } elseif ($this->db_type === 'cosmosdb') {
            return saveChat($chatData);
        } else {
            return saveChat($chatData);
        }
    }
    
    public function getRecentChats($userId, $limit = 5) {
        if ($this->db_type === 'mysql') {
            return $this->getRecentChatsMySQL($userId, $limit);
        } elseif ($this->db_type === 'cosmosdb') {
            return getRecentChats($userId, $limit);
        } else {
            return getRecentChats($userId, $limit);
        }
    }
    
    public function getChatHistory($userId, $limit = 50) {
        if ($this->db_type === 'mysql') {
            return $this->getChatHistoryMySQL($userId, $limit);
        } elseif ($this->db_type === 'cosmosdb') {
            return $this->getChatHistoryCosmos($userId, $limit);
        } else {
            return getChatHistory($userId, $limit);
        }
    }
    
    // Mood check functions
    public function saveMoodCheck($moodData) {
        if ($this->db_type === 'mysql') {
            return $this->saveMoodCheckMySQL($moodData);
        } elseif ($this->db_type === 'cosmosdb') {
            return saveMoodCheck($moodData);
        } else {
            return saveMoodCheck($moodData);
        }
    }
    
    public function getMoodChecks($userId, $limit = 30) {
        if ($this->db_type === 'mysql') {
            return $this->getMoodChecksMySQL($userId, $limit);
        } elseif ($this->db_type === 'cosmosdb') {
            return getMoodChecks($userId, $limit);
        } else {
            return getMoodChecks($userId, $limit);
        }
    }
    
    // Rate limiting functions
    public function checkRateLimit($userId, $action) {
        if ($this->db_type === 'mysql') {
            return checkRateLimit($userId, $action);
        } elseif ($this->db_type === 'cosmosdb') {
            return checkRateLimit($userId, $action);
        } else {
            return checkRateLimit($userId, $action);
        }
    }
    
    public function recordRateLimit($userId, $action) {
        if ($this->db_type === 'mysql') {
            return $this->recordRateLimitMySQL($userId, $action);
        } elseif ($this->db_type === 'cosmosdb') {
            return recordRateLimit($userId, $action);
        } else {
            return recordRateLimit($userId, $action);
        }
    }
    
    // MySQL specific implementations
    private function createUserMySQL($userData) {
        $stmt = $this->mysql_conn->prepare("
            INSERT INTO users (username, email, password_hash, created_at, updated_at, is_active) 
            VALUES (?, ?, ?, NOW(), NOW(), TRUE)
        ");
        $stmt->bind_param("sss", 
            $userData['username'], 
            $userData['email'], 
            $userData['password_hash']
        );
        return $stmt->execute();
    }
    
    private function getUserByEmailMySQL($email) {
        $stmt = $this->mysql_conn->prepare("
            SELECT id, username, email, password_hash, created_at, updated_at, last_login, is_active 
            FROM users WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    private function getUserByIdMySQL($id) {
        $stmt = $this->mysql_conn->prepare("
            SELECT id, username, email, password_hash, created_at, updated_at, last_login, is_active 
            FROM users WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    private function updateUserMySQL($id, $userData) {
        $stmt = $this->mysql_conn->prepare("
            UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?
        ");
        $stmt->bind_param("ssi", 
            $userData['username'], 
            $userData['email'], 
            $id
        );
        return $stmt->execute();
    }
    
    private function saveChatMySQL($chatData) {
        $stmt = $this->mysql_conn->prepare("
            INSERT INTO chats (user_id, user_message, ai_response, sentiment, confidence, timestamp) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isssd", 
            $chatData['user_id'], 
            $chatData['user_message'], 
            $chatData['ai_response'], 
            $chatData['sentiment'], 
            $chatData['confidence']
        );
        return $stmt->execute();
    }
    
    private function getRecentChatsMySQL($userId, $limit) {
        $stmt = $this->mysql_conn->prepare("
            SELECT user_message, ai_response, sentiment, timestamp 
            FROM chats 
            WHERE user_id = ? 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $chats = [];
        while ($row = $result->fetch_assoc()) {
            $chats[] = $row;
        }
        
        return array_reverse($chats);
    }
    
    private function getChatHistoryMySQL($userId, $limit) {
        $stmt = $this->mysql_conn->prepare("
            SELECT user_message, ai_response, sentiment, timestamp 
            FROM chats 
            WHERE user_id = ? 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $chats = [];
        while ($row = $result->fetch_assoc()) {
            $chats[] = $row;
        }
        
        return $chats;
    }
    
    private function saveMoodCheckMySQL($moodData) {
        $stmt = $this->mysql_conn->prepare("
            INSERT INTO mood_checks (user_id, mood, notes, timestamp) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("iss", 
            $moodData['user_id'], 
            $moodData['mood'], 
            $moodData['notes']
        );
        return $stmt->execute();
    }
    
    private function getMoodChecksMySQL($userId, $limit) {
        $stmt = $this->mysql_conn->prepare("
            SELECT mood, notes, timestamp 
            FROM mood_checks 
            WHERE user_id = ? 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $moods = [];
        while ($row = $result->fetch_assoc()) {
            $moods[] = $row;
        }
        
        return $moods;
    }
    
    private function recordRateLimitMySQL($userId, $action) {
        $stmt = $this->mysql_conn->prepare("
            INSERT INTO rate_limits (user_id, action, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->bind_param("is", $userId, $action);
        return $stmt->execute();
    }
    
    // Cosmos DB specific implementations
    private function updateUserCosmos($id, $userData) {
        $document = [
            'username' => $userData['username'],
            'email' => $userData['email'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->cosmos->updateDocument('users', $id, $document);
    }
    
    private function getChatHistoryCosmos($userId, $limit) {
        $query = "SELECT TOP @limit * FROM c WHERE c.user_id = @userId ORDER BY c.timestamp DESC";
        $result = $this->cosmos->queryDocuments('chats', $query);
        
        if ($result['status'] === 200) {
            return $result['body']['Documents'];
        }
        
        return [];
    }
}

// Create global database abstraction instance
$db = new DatabaseAbstraction();

// Convenience functions
function createUser($userData) {
    global $db;
    return $db->createUser($userData);
}

function getUserByEmail($email) {
    global $db;
    return $db->getUserByEmail($email);
}

function getUserById($id) {
    global $db;
    return $db->getUserById($id);
}

function updateUser($id, $userData) {
    global $db;
    return $db->updateUser($id, $userData);
}

function saveChat($chatData) {
    global $db;
    return $db->saveChat($chatData);
}

function getRecentChats($userId, $limit = 5) {
    global $db;
    return $db->getRecentChats($userId, $limit);
}

function getChatHistory($userId, $limit = 50) {
    global $db;
    return $db->getChatHistory($userId, $limit);
}

function saveMoodCheck($moodData) {
    global $db;
    return $db->saveMoodCheck($moodData);
}

function getMoodChecks($userId, $limit = 30) {
    global $db;
    return $db->getMoodChecks($userId, $limit);
}

function checkRateLimit($userId, $action) {
    global $db;
    return $db->checkRateLimit($userId, $action);
}

function recordRateLimit($userId, $action) {
    global $db;
    return $db->recordRateLimit($userId, $action);
}

?>
