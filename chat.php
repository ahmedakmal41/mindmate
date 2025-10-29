<?php
session_start();
require_once 'backend/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get chat history
$stmt = $conn->prepare("SELECT * FROM chats WHERE user_id = ? ORDER BY timestamp ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$chat_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - MindMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="chat-body">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="dashboard.php">
                <i class="fas fa-brain me-2"></i>MindMate
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($username); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Chat Interface -->
            <div class="col-12 d-flex flex-column">
                <!-- Chat Header -->
                <div class="chat-header bg-primary text-white p-3">
                    <div class="d-flex align-items-center">
                        <div class="ai-avatar me-3">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">AI Counsellor</h5>
                            <small class="opacity-75">Always here to listen and support you</small>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div class="chat-messages flex-grow-1 p-3" id="chatMessages">
                    <?php if (empty($chat_history)): ?>
                        <div class="welcome-message text-center py-5">
                            <i class="fas fa-comments display-1 text-primary mb-3"></i>
                            <h4 class="text-muted">Welcome to your safe space</h4>
                            <p class="text-muted">I'm here to listen and support you. How are you feeling today?</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($chat_history as $message): ?>
                            <div class="message user-message mb-3">
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($message['user_message'])); ?>
                                    </div>
                                    <small class="message-time text-muted">
                                        <?php echo date('H:i', strtotime($message['timestamp'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="message ai-message mb-3">
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($message['ai_response'])); ?>
                                    </div>
                                    <small class="message-time text-muted">
                                        <?php echo date('H:i', strtotime($message['timestamp'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Typing indicator (hidden by default) -->
                    <div class="message ai-message typing-indicator" id="typingIndicator" style="display: none;">
                        <div class="message-content">
                            <div class="message-bubble">
                                <div class="typing-dots">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Input -->
                <div class="chat-input bg-white border-top p-3">
                    <form id="chatForm" class="d-flex">
                        <div class="input-group">
                            <input type="text" class="form-control" id="messageInput" 
                                   placeholder="Type your message here..." autocomplete="off">
                            <button type="submit" class="btn btn-primary" id="sendButton">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your conversations are private and secure
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Crisis Modal -->
    <div class="modal fade" id="crisisModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Support Available
                    </h5>
                </div>
                <div class="modal-body">
                    <p>I'm concerned about your well-being. Please know that help is available:</p>
                    <ul>
                        <li><strong>National Suicide Prevention Lifeline:</strong> 988</li>
                        <li><strong>Crisis Text Line:</strong> Text HOME to 741741</li>
                        <li><strong>Emergency Services:</strong> 911</li>
                    </ul>
                    <p class="mb-0">You're not alone, and there are people who care about you and want to help.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">I understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/chat.js"></script>
</body>
</html>

