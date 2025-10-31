<?php
// MindMate - Logout

session_start();
require_once 'backend/db_abstraction.php';

// Check if user is logged in
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    
    // Log the logout
    logMessage("User logged out - User ID: $user_id", 'INFO');
    
    // Clean up session data
    session_unset();
    session_destroy();
    
    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

// Redirect to login page
header('Location: login.php');
exit();
?>

