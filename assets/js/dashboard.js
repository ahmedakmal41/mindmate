// MindMate - Dashboard JavaScript

class DashboardManager {
    constructor() {
        this.moodChart = null;
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeCharts();
        this.loadDashboardData();
    }
    
    setupEventListeners() {
        // Mood check modal
        const moodCheckModal = document.getElementById('moodCheckModal');
        if (moodCheckModal) {
            const moodButtons = moodCheckModal.querySelectorAll('.mood-btn');
            moodButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const mood = e.currentTarget.dataset.mood;
                    this.handleMoodSelection(mood);
                });
            });
        }
        
        // Quick action buttons
        this.setupQuickActions();
        
        // Auto-refresh data every 5 minutes
        setInterval(() => {
            this.loadDashboardData();
        }, 300000);
    }
    
    setupQuickActions() {
        // Mood check button
        const moodCheckBtn = document.querySelector('[onclick="showMoodCheck()"]');
        if (moodCheckBtn) {
            moodCheckBtn.addEventListener('click', this.showMoodCheck.bind(this));
        }
        
        // Resources button
        const resourcesBtn = document.querySelector('[onclick="showResources()"]');
        if (resourcesBtn) {
            resourcesBtn.addEventListener('click', this.showResources.bind(this));
        }
        
        // Settings button
        const settingsBtn = document.querySelector('[onclick="showSettings()"]');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', this.showSettings.bind(this));
        }
    }
    
    initializeCharts() {
        // Initialize mood chart if data exists
        const moodChartCanvas = document.getElementById('moodChart');
        if (moodChartCanvas && window.moodData) {
            this.createMoodChart();
        }
    }
    
    createMoodChart() {
        const ctx = document.getElementById('moodChart').getContext('2d');
        
        this.moodChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(window.moodData),
                datasets: [{
                    data: Object.values(window.moodData),
                    backgroundColor: [
                        '#28a745', // Positive - Green
                        '#dc3545', // Negative - Red
                        '#6c757d', // Neutral - Gray
                        '#ffc107', // Warning - Yellow
                        '#17a2b8'  // Info - Cyan
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%',
                animation: {
                    animateRotate: true,
                    duration: 1000
                }
            }
        });
    }
    
    async loadDashboardData() {
        try {
            const response = await fetch('backend/get_dashboard_data.php');
            const data = await response.json();
            
            this.updateStatistics(data.statistics);
            this.updateRecentChats(data.recentChats);
            this.updateMoodData(data.moodData);
            
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }
    
    updateStatistics(stats) {
        // Update total chats
        const totalChatsElement = document.querySelector('.stat-card h3');
        if (totalChatsElement && stats.totalChats !== undefined) {
            this.animateNumber(totalChatsElement, stats.totalChats);
        }
    }
    
    updateRecentChats(chats) {
        const conversationList = document.querySelector('.conversation-list');
        if (!conversationList || !chats) return;
        
        conversationList.innerHTML = '';
        
        chats.forEach(chat => {
            const chatElement = this.createChatElement(chat);
            conversationList.appendChild(chatElement);
        });
    }
    
    createChatElement(chat) {
        const div = document.createElement('div');
        div.className = 'conversation-item mb-3 p-3 bg-light rounded';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-${this.getSentimentColor(chat.sentiment)}">
                    ${chat.sentiment || 'Neutral'}
                </span>
                <small class="text-muted">
                    ${this.formatDate(chat.timestamp)}
                </small>
            </div>
            <p class="mb-0 text-truncate">
                ${this.truncateText(chat.user_message, 100)}
            </p>
        `;
        
        // Add click handler to view full conversation
        div.addEventListener('click', () => {
            window.location.href = 'chat.php';
        });
        
        return div;
    }
    
    updateMoodData(moodData) {
        if (this.moodChart && moodData) {
            this.moodChart.data.labels = Object.keys(moodData);
            this.moodChart.data.datasets[0].data = Object.values(moodData);
            this.moodChart.update();
        }
    }
    
    getSentimentColor(sentiment) {
        switch (sentiment) {
            case 'POSITIVE': return 'success';
            case 'NEGATIVE': return 'danger';
            case 'NEUTRAL': return 'secondary';
            default: return 'secondary';
        }
    }
    
    formatDate(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        
        if (diffInHours < 1) {
            return 'Just now';
        } else if (diffInHours < 24) {
            return `${Math.floor(diffInHours)}h ago`;
        } else if (diffInHours < 168) { // 7 days
            return `${Math.floor(diffInHours / 24)}d ago`;
        } else {
            return date.toLocaleDateString();
        }
    }
    
    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
    
    animateNumber(element, targetNumber) {
        const startNumber = parseInt(element.textContent) || 0;
        const duration = 1000;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentNumber = Math.floor(startNumber + (targetNumber - startNumber) * progress);
            element.textContent = currentNumber;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    showMoodCheck() {
        const modal = new bootstrap.Modal(document.getElementById('moodCheckModal'));
        modal.show();
    }
    
    handleMoodSelection(mood) {
        // Save mood selection
        this.saveMoodCheck(mood);
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('moodCheckModal'));
        modal.hide();
        
        // Show confirmation
        this.showNotification(`Mood recorded: ${mood}`, 'success');
        
        // Redirect to chat if user wants to talk
        if (mood === 'sad' || mood === 'neutral') {
            setTimeout(() => {
                if (confirm('Would you like to chat about how you\'re feeling?')) {
                    window.location.href = 'chat.php';
                }
            }, 1000);
        }
    }
    
    async saveMoodCheck(mood) {
        try {
            await fetch('backend/save_mood_check.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ mood: mood })
            });
        } catch (error) {
            console.error('Error saving mood check:', error);
        }
    }
    
    showResources() {
        const resourcesModal = this.createResourcesModal();
        document.body.appendChild(resourcesModal);
        const modal = new bootstrap.Modal(resourcesModal);
        modal.show();
    }
    
    createResourcesModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-book me-2"></i>Mental Health Resources
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="resource-card p-3 border rounded">
                                    <h6><i class="fas fa-phone text-primary me-2"></i>Crisis Hotlines</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>National Suicide Prevention:</strong> 988</li>
                                        <li><strong>Crisis Text Line:</strong> Text HOME to 741741</li>
                                        <li><strong>Emergency:</strong> 911</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="resource-card p-3 border rounded">
                                    <h6><i class="fas fa-globe text-info me-2"></i>Online Resources</h6>
                                    <ul class="list-unstyled">
                                        <li><a href="#" class="text-decoration-none">Mental Health America</a></li>
                                        <li><a href="#" class="text-decoration-none">National Institute of Mental Health</a></li>
                                        <li><a href="#" class="text-decoration-none">Psychology Today</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        return modal;
    }
    
    showSettings() {
        const settingsModal = this.createSettingsModal();
        document.body.appendChild(settingsModal);
        const modal = new bootstrap.Modal(settingsModal);
        modal.show();
    }
    
    createSettingsModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-cog me-2"></i>Settings
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Notification Preferences</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    Email notifications
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Privacy Settings</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="anonymousMode">
                                <label class="form-check-label" for="anonymousMode">
                                    Anonymous mode
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        `;
        return modal;
    }
    
    showNotification(message, type = 'info') {
        if (window.MindMateUtils) {
            window.MindMateUtils.showNotification(message, type);
        }
    }
}

// Global functions for onclick handlers
function showMoodCheck() {
    if (window.dashboardManager) {
        window.dashboardManager.showMoodCheck();
    }
}

function showResources() {
    if (window.dashboardManager) {
        window.dashboardManager.showResources();
    }
}

function showSettings() {
    if (window.dashboardManager) {
        window.dashboardManager.showSettings();
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
});

