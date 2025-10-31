<?php
session_start();
require_once 'backend/db_abstraction.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get recent chats
$recent_chats = getRecentChats($user_id, 5);
$total_chats = count(getChatHistory($user_id));

// Calculate mood distribution from recent chats
$mood_distribution = [];
$all_chats = getChatHistory($user_id);
foreach ($all_chats as $chat) {
    if (isset($chat['sentiment']) && !empty($chat['sentiment'])) {
        $sentiment = $chat['sentiment'];
        if (!isset($mood_distribution[$sentiment])) {
            $mood_distribution[$sentiment] = 0;
        }
        $mood_distribution[$sentiment]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MindMate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">
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
                        <li><a class="dropdown-item" href="chat.php"><i class="fas fa-comments me-2"></i>Chat</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card bg-primary text-white p-4 rounded-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
                            <p class="mb-0 opacity-75">How are you feeling today? Your mental health journey continues here.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="chat.php" class="btn btn-light btn-lg">
                                <i class="fas fa-comments me-2"></i>Start Chat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-white p-4 rounded-3 shadow-sm h-100">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary text-white rounded-circle me-3">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo $total_chats; ?></h3>
                            <p class="text-muted mb-0">Total Conversations</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-white p-4 rounded-3 shadow-sm h-100">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success text-white rounded-circle me-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo date('j'); ?></h3>
                            <p class="text-muted mb-0">Days This Month</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-white p-4 rounded-3 shadow-sm h-100">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info text-white rounded-circle me-3">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">100%</h3>
                            <p class="text-muted mb-0">Privacy Protected</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Mood Analytics -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Mood Analytics
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mood_distribution)): ?>
                            <canvas id="moodChart" width="400" height="200"></canvas>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chart-pie display-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No mood data yet</h5>
                                <p class="text-muted">Start chatting to see your mood analytics</p>
                                <a href="chat.php" class="btn btn-primary">Start Chat</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Conversations -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Recent Conversations
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_chats)): ?>
                            <div class="conversation-list">
                                <?php foreach ($recent_chats as $chat): ?>
                                    <div class="conversation-item mb-3 p-3 bg-light rounded">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-<?php 
                                                echo $chat['sentiment'] === 'POSITIVE' ? 'success' : 
                                                    ($chat['sentiment'] === 'NEGATIVE' ? 'danger' : 'secondary'); 
                                            ?>">
                                                <?php echo $chat['sentiment'] ?? 'Neutral'; ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo date('M j, H:i', strtotime($chat['timestamp'])); ?>
                                            </small>
                                        </div>
                                        <p class="mb-0 text-truncate">
                                            <?php echo htmlspecialchars(substr($chat['user_message'], 0, 100)); ?>
                                            <?php echo strlen($chat['user_message']) > 100 ? '...' : ''; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center">
                                <a href="chat.php" class="btn btn-outline-primary">View All Chats</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments display-4 text-muted mb-3"></i>
                                <h6 class="text-muted">No conversations yet</h6>
                                <p class="text-muted small">Start your first conversation with our AI counsellor</p>
                                <a href="chat.php" class="btn btn-primary">Start Chat</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="chat.php" class="btn btn-outline-primary w-100 p-3">
                                    <i class="fas fa-comments d-block mb-2"></i>
                                    Start New Chat
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-success w-100 p-3" onclick="showMoodCheck()">
                                    <i class="fas fa-heart d-block mb-2"></i>
                                    Mood Check
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-info w-100 p-3" onclick="showResources()">
                                    <i class="fas fa-book d-block mb-2"></i>
                                    Resources
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-warning w-100 p-3" onclick="showSettings()">
                                    <i class="fas fa-cog d-block mb-2"></i>
                                    Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mood Check Modal -->
    <div class="modal fade" id="moodCheckModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-heart me-2"></i>How are you feeling?
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mood-options text-center">
                        <div class="row g-3">
                            <div class="col-4">
                                <button class="btn btn-outline-danger w-100 p-3 mood-btn" data-mood="sad">
                                    <i class="fas fa-frown d-block mb-2"></i>
                                    Sad
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 p-3 mood-btn" data-mood="neutral">
                                    <i class="fas fa-meh d-block mb-2"></i>
                                    Neutral
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-success w-100 p-3 mood-btn" data-mood="happy">
                                    <i class="fas fa-smile d-block mb-2"></i>
                                    Happy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
        // Mood chart data
        const moodData = <?php echo json_encode($mood_distribution); ?>;
        
        // Initialize mood chart if data exists
        if (Object.keys(moodData).length > 0) {
            const ctx = document.getElementById('moodChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(moodData),
                    datasets: [{
                        data: Object.values(moodData),
                        backgroundColor: [
                            '#28a745', // Positive - Green
                            '#dc3545', // Negative - Red
                            '#6c757d'  // Neutral - Gray
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>

