// MindMate - Chat JavaScript

class ChatManager {
    constructor() {
        this.chatMessages = document.getElementById('chatMessages');
        this.messageInput = document.getElementById('messageInput');
        this.chatForm = document.getElementById('chatForm');
        this.sendButton = document.getElementById('sendButton');
        this.typingIndicator = document.getElementById('typingIndicator');
        
        this.isTyping = false;
        this.crisisKeywords = [
            'suicide', 'kill myself', 'end it all', 'not worth living',
            'want to die', 'hurt myself', 'self harm', 'crisis'
        ];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.scrollToBottom();
        this.focusInput();
    }
    
    setupEventListeners() {
        // Form submission
        this.chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });
        
        // Enter key handling
        this.messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Input focus
        this.messageInput.addEventListener('focus', () => {
            this.messageInput.parentElement.classList.add('focused');
        });
        
        this.messageInput.addEventListener('blur', () => {
            this.messageInput.parentElement.classList.remove('focused');
        });
    }
    
    async sendMessage() {
        const message = this.messageInput.value.trim();
        if (!message) return;
        
        // Check for crisis keywords
        if (this.detectCrisis(message)) {
            this.showCrisisModal();
            return;
        }
        
        // Add user message to chat
        this.addMessage(message, 'user');
        this.messageInput.value = '';
        this.focusInput();
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Disable input while processing
        this.setInputState(false);
        
        try {
            // Send to AI
            const response = await this.getAIResponse(message);
            
            // Hide typing indicator
            this.hideTypingIndicator();
            
            // Add AI response
            this.addMessage(response.reply, 'ai', response.sentiment);
            
        } catch (error) {
            console.error('Error getting AI response:', error);
            this.hideTypingIndicator();
            this.addMessage('I apologize, but I\'m having trouble connecting right now. Please try again in a moment.', 'ai');
        } finally {
            this.setInputState(true);
        }
    }
    
    addMessage(content, sender, sentiment = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message mb-3`;
        
        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';
        
        const messageBubble = document.createElement('div');
        messageBubble.className = 'message-bubble';
        messageBubble.innerHTML = this.formatMessage(content);
        
        const messageTime = document.createElement('small');
        messageTime.className = 'message-time text-muted';
        messageTime.textContent = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        messageContent.appendChild(messageBubble);
        messageContent.appendChild(messageTime);
        messageDiv.appendChild(messageContent);
        
        // Add sentiment badge for AI messages
        if (sender === 'ai' && sentiment) {
            const sentimentBadge = document.createElement('span');
            sentimentBadge.className = `badge bg-${this.getSentimentColor(sentiment)} ms-2`;
            sentimentBadge.textContent = sentiment;
            messageBubble.appendChild(sentimentBadge);
        }
        
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
        
        // Save to database
        this.saveMessage(content, sender, sentiment);
    }
    
    formatMessage(content) {
        // Convert line breaks to HTML
        return content.replace(/\n/g, '<br>');
    }
    
    getSentimentColor(sentiment) {
        switch (sentiment) {
            case 'POSITIVE': return 'success';
            case 'NEGATIVE': return 'danger';
            default: return 'secondary';
        }
    }
    
    showTypingIndicator() {
        this.typingIndicator.style.display = 'block';
        this.scrollToBottom();
    }
    
    hideTypingIndicator() {
        this.typingIndicator.style.display = 'none';
    }
    
    async getAIResponse(message) {
        const response = await fetch('backend/api_bridge.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                action: 'chat'
            })
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
    }
    
    async saveMessage(content, sender, sentiment) {
        try {
            await fetch('backend/save_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: content,
                    sender: sender,
                    sentiment: sentiment
                })
            });
        } catch (error) {
            console.error('Error saving message:', error);
        }
    }
    
    detectCrisis(message) {
        const lowerMessage = message.toLowerCase();
        return this.crisisKeywords.some(keyword => lowerMessage.includes(keyword));
    }
    
    showCrisisModal() {
        const crisisModal = new bootstrap.Modal(document.getElementById('crisisModal'));
        crisisModal.show();
    }
    
    setInputState(enabled) {
        this.messageInput.disabled = !enabled;
        this.sendButton.disabled = !enabled;
        
        if (enabled) {
            this.sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
        } else {
            this.sendButton.innerHTML = '<div class="spinner"></div>';
        }
    }
    
    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }
    
    focusInput() {
        this.messageInput.focus();
    }
    
    // Clear chat history
    clearChat() {
        this.chatMessages.innerHTML = `
            <div class="welcome-message text-center py-5">
                <i class="fas fa-comments display-1 text-primary mb-3"></i>
                <h4 class="text-muted">Welcome to your safe space</h4>
                <p class="text-muted">I'm here to listen and support you. How are you feeling today?</p>
            </div>
        `;
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new ChatManager();
});

// Additional chat utilities
const ChatUtils = {
    // Format timestamp
    formatTimestamp: function(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        
        if (diffInHours < 1) {
            return 'Just now';
        } else if (diffInHours < 24) {
            return `${Math.floor(diffInHours)}h ago`;
        } else {
            return date.toLocaleDateString();
        }
    },
    
    // Generate typing animation
    createTypingAnimation: function() {
        const dots = document.querySelectorAll('.typing-dots span');
        dots.forEach((dot, index) => {
            dot.style.animationDelay = `${index * 0.2}s`;
        });
    },
    
    // Auto-resize textarea
    autoResize: function(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    },
    
    // Check if message is empty
    isEmpty: function(message) {
        return !message || message.trim().length === 0;
    },
    
    // Sanitize message content
    sanitize: function(content) {
        const div = document.createElement('div');
        div.textContent = content;
        return div.innerHTML;
    }
};

// Export for global use
window.ChatUtils = ChatUtils;

