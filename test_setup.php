<?php
// MindMate - Setup Test Script
// Run this to verify your installation

echo "<h1>🧠 MindMate Setup Test</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:40px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

$errors = [];
$warnings = [];

// Test 1: PHP Version
echo "<h2>1. PHP Version Check</h2>";
$phpVersion = phpversion();
if (version_compare($phpVersion, '8.0.0', '>=')) {
    echo "<p class='success'>✅ PHP $phpVersion - OK</p>";
} else {
    $errors[] = "PHP 8.0+ required, found $phpVersion";
    echo "<p class='error'>❌ PHP $phpVersion - Requires 8.0+</p>";
}

// Test 2: Required Extensions
echo "<h2>2. PHP Extensions Check</h2>";
$required_extensions = ['mysqli', 'curl', 'json', 'session'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ $ext extension - OK</p>";
    } else {
        $errors[] = "Missing $ext extension";
        echo "<p class='error'>❌ $ext extension - Missing</p>";
    }
}

// Test 3: File Permissions
echo "<h2>3. File Permissions Check</h2>";
$directories = ['logs', 'uploads'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if (is_writable($dir)) {
        echo "<p class='success'>✅ $dir directory - Writable</p>";
    } else {
        $errors[] = "$dir directory not writable";
        echo "<p class='error'>❌ $dir directory - Not writable</p>";
    }
}

// Test 4: Database Connection
echo "<h2>4. Database Connection Test</h2>";
try {
    require_once 'backend/config.php';
    if ($conn->connect_error) {
        $errors[] = "Database connection failed: " . $conn->connect_error;
        echo "<p class='error'>❌ Database connection failed</p>";
    } else {
        echo "<p class='success'>✅ Database connection - OK</p>";
        
        // Test table creation
        $tables = ['users', 'chats', 'mood_checks', 'rate_limits'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "<p class='success'>✅ $table table exists</p>";
            } else {
                echo "<p class='warning'>⚠️ $table table - Will be created on first use</p>";
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Database test failed: " . $e->getMessage();
    echo "<p class='error'>❌ Database test failed</p>";
}

// Test 5: AI Engine Connection
echo "<h2>5. AI Engine Connection Test</h2>";
$ai_url = AI_API_URL . '/health';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ai_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "<p class='success'>✅ AI Engine connection - OK</p>";
} else {
    $warnings[] = "AI Engine not running (HTTP $httpCode)";
    echo "<p class='warning'>⚠️ AI Engine connection - Not running</p>";
    echo "<p><small>Start the AI engine with: <code>cd ai_engine && python app.py</code></small></p>";
}

// Test 6: Configuration Files
echo "<h2>6. Configuration Files Check</h2>";
$config_files = [
    'backend/config.php',
    'backend/db_connect.php',
    'backend/api_bridge.php',
    'backend/save_chat.php',
    'ai_engine/app.py',
    'ai_engine/requirements.txt'
];

foreach ($config_files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file - Exists</p>";
    } else {
        $errors[] = "Missing $file";
        echo "<p class='error'>❌ $file - Missing</p>";
    }
}

// Test 7: Frontend Files
echo "<h2>7. Frontend Files Check</h2>";
$frontend_files = [
    'index.html',
    'login.php',
    'register.php',
    'chat.php',
    'dashboard.php',
    'assets/css/style.css',
    'assets/js/chat.js',
    'assets/js/dashboard.js'
];

foreach ($frontend_files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file - Exists</p>";
    } else {
        $errors[] = "Missing $file";
        echo "<p class='error'>❌ $file - Missing</p>";
    }
}

// Summary
echo "<h2>📊 Test Summary</h2>";
if (empty($errors)) {
    echo "<div style='background:#d4edda;padding:20px;border-radius:5px;border:1px solid #c3e6cb;'>";
    echo "<h3 class='success'>🎉 All Tests Passed!</h3>";
    echo "<p>Your MindMate installation is ready to use.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Start the AI engine: <code>cd ai_engine && python app.py</code></li>";
    echo "<li>Access the application: <a href='index.html'>Open MindMate</a></li>";
    echo "<li>Register a new account to get started</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;padding:20px;border-radius:5px;border:1px solid #f5c6cb;'>";
    echo "<h3 class='error'>❌ Issues Found</h3>";
    echo "<p>Please fix the following issues:</p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li class='error'>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($warnings)) {
    echo "<div style='background:#fff3cd;padding:20px;border-radius:5px;border:1px solid #ffeaa7;margin-top:20px;'>";
    echo "<h3 class='warning'>⚠️ Warnings</h3>";
    echo "<ul>";
    foreach ($warnings as $warning) {
        echo "<li class='warning'>$warning</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>MindMate Setup Test - " . date('Y-m-d H:i:s') . "</small></p>";
?>
