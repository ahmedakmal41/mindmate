# 🚀 MindMate Setup Guide

## Overview
MindMate is a complete AI-powered psychological counselling web application with a modern, responsive interface and intelligent conversation capabilities.

## ✅ What's Already Implemented

### Frontend (Complete)
- **Landing Page** (`index.html`) - Beautiful hero section with features showcase
- **Authentication** (`login.php`, `register.php`) - Secure user registration and login
- **Chat Interface** (`chat.php`) - Real-time AI conversation with typing indicators
- **Dashboard** (`dashboard.php`) - Mood analytics, statistics, and quick actions
- **Responsive Design** - Mobile-first design with Bootstrap 5

### Backend (Complete)
- **Database Layer** (`backend/config.php`, `backend/db_connect.php`) - MySQL integration with auto-initialization
- **API Bridge** (`backend/api_bridge.php`) - PHP to Python AI communication
- **Chat Management** (`backend/save_chat.php`) - Message persistence and retrieval
- **Security** - Rate limiting, input sanitization, session management

### AI Engine (Complete)
- **Flask API** (`ai_engine/app.py`) - OpenAI GPT integration with sentiment analysis
- **Emotion Detection** - Keyword-based sentiment analysis
- **Crisis Detection** - Safety monitoring for self-harm indicators
- **Conversation Context** - Maintains chat history for better responses

### Styling & JavaScript (Complete)
- **CSS** (`assets/css/style.css`) - Calming color palette, animations, responsive design
- **Chat Logic** (`assets/js/chat.js`) - Real-time messaging, typing indicators
- **Dashboard Logic** (`assets/js/dashboard.js`) - Charts, mood tracking, quick actions

## 🛠️ Installation Steps

### 1. Prerequisites
- **Web Server**: Apache/Nginx with PHP 8.0+
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Python**: Python 3.8+ with pip
- **OpenAI API Key**: Get from [OpenAI Platform](https://platform.openai.com/)

### 2. Database Setup
```sql
-- Create database
CREATE DATABASE mindmate;
USE mindmate;

-- Tables will be auto-created by the application
-- The db_connect.php file includes auto-initialization
```

### 3. PHP Configuration
Update `backend/config.php` with your database credentials:
```php
$host = "localhost";
$user = "your_mysql_user";
$pass = "your_mysql_password";
$db = "mindmate";
```

### 4. Python AI Engine Setup
```bash
# Navigate to AI engine directory
cd ai_engine

# Create virtual environment
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Set OpenAI API key
export OPENAI_API_KEY="your-openai-api-key-here"

# Run the AI engine
python app.py
```

### 5. Web Server Configuration
- Place the MindMate folder in your web server's document root
- Ensure PHP has write permissions to `logs/` and `uploads/` directories
- Configure your web server to serve PHP files

### 6. Environment Variables
Create a `.env` file in the `ai_engine/` directory:
```
OPENAI_API_KEY=your-openai-api-key-here
FLASK_ENV=development
FLASK_DEBUG=True
```

## 🚀 Running the Application

### Start the AI Engine
```bash
cd ai_engine
source venv/bin/activate
python app.py
```
The AI engine will run on `http://127.0.0.1:5000`

### Start the Web Application
- Access via your web server: `http://localhost/mindmate`
- Or if using PHP built-in server: `php -S localhost:8000`

## 🔧 Configuration Options

### Database Settings
- Modify `backend/config.php` for database connection
- Tables are auto-created on first run
- Includes indexes for optimal performance

### AI Engine Settings
- Update `ai_engine/app.py` for different OpenAI models
- Adjust sentiment analysis keywords
- Modify crisis detection patterns

### Security Settings
- Rate limiting: 10 requests per minute per user
- Session timeout: 7 days
- Input sanitization and validation

## 📱 Features Overview

### User Features
- **Secure Registration/Login** - Password hashing, email validation
- **AI Chat Interface** - Real-time conversation with GPT
- **Mood Analytics** - Visual charts of emotional patterns
- **Crisis Detection** - Automatic safety monitoring
- **Responsive Design** - Works on all devices

### Admin Features
- **Comprehensive Logging** - All interactions logged
- **Rate Limiting** - Prevents abuse
- **Database Management** - Auto-initialization and cleanup
- **Health Monitoring** - API status checks

## 🔒 Security Features

- **Password Hashing** - bcrypt encryption
- **SQL Injection Protection** - Prepared statements
- **XSS Prevention** - Input sanitization
- **CSRF Protection** - Session management
- **Rate Limiting** - API abuse prevention
- **Crisis Detection** - Safety monitoring

## 🐛 Troubleshooting

### Common Issues

1. **AI Engine Not Responding**
   - Check if Python Flask is running on port 5000
   - Verify OpenAI API key is set correctly
   - Check logs in `logs/` directory

2. **Database Connection Issues**
   - Verify MySQL credentials in `backend/config.php`
   - Ensure MySQL service is running
   - Check database permissions

3. **Chat Not Working**
   - Verify AI engine is running
   - Check browser console for JavaScript errors
   - Ensure API bridge is accessible

### Debug Mode
Enable debug mode in `ai_engine/app.py`:
```python
app.run(host='127.0.0.1', port=5000, debug=True)
```

## 📊 Monitoring

### Logs
- Application logs: `logs/mindmate_YYYY-MM-DD.log`
- Database errors logged automatically
- API calls tracked with timestamps

### Health Checks
- AI Engine: `http://127.0.0.1:5000/health`
- PHP API: `http://localhost/mindmate/backend/api_bridge.php?health`

## 🚀 Production Deployment

### Security Checklist
- [ ] Change default database credentials
- [ ] Set `session.cookie_secure = 1` for HTTPS
- [ ] Disable `display_errors` in production
- [ ] Set up SSL certificate
- [ ] Configure proper file permissions

### Performance Optimization
- [ ] Enable PHP OPcache
- [ ] Configure MySQL query cache
- [ ] Set up Redis for session storage
- [ ] Use CDN for static assets

## 📈 Future Enhancements

The application is ready for these additions:
- Voice chat integration
- Mobile app development
- Advanced analytics dashboard
- Multi-language support
- Therapist referral system

## 🤝 Support

For technical support or questions:
- Check the logs in `logs/` directory
- Review the code comments for implementation details
- Ensure all prerequisites are installed correctly

---

**MindMate** - Bringing empathy to AI for better mental health support 💙
