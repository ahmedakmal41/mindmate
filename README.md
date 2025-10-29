# 🧠 MindMate – AI-Powered Psychological Counselling Web Application  
> *Your AI Psychologist & Student Support Companion*

---

## 🌟 Overview

**MindMate** is an intelligent and empathetic counselling web application designed to support mental well-being through AI-powered conversations, mood analysis, and emotional insights.  
It combines **HTML**, **PHP**, and **Python (AI engine)** to create a smooth, elegant, and responsive counselling experience.

---

## 🎯 Objectives

- Deliver a **user-friendly, secure** digital counselling platform.  
- Provide **AI-driven emotional support** using state-of-the-art NLP models.  
- Maintain **data privacy** and **user anonymity** for safe interactions.  
- Design a **calm, therapeutic UI/UX** for comfort and focus.  

---

## 🧩 Tech Stack

| Layer | Technology | Purpose |
|-------|-------------|----------|
| **Frontend** | HTML5, CSS3, JavaScript | Elegant, responsive chat and dashboard UI |
| **Backend** | PHP 8.x | Handles routing, sessions, and authentication |
| **AI Engine** | Python (Flask / FastAPI) | Conversation logic and emotion detection |
| **Database** | MySQL | Stores users, chats, and mood analytics |
| **Styling** | Bootstrap 5 / Tailwind CSS | Beautiful responsive UI |
| **Hosting** | Apache / Nginx | Production web server |
| **AI API** | OpenAI GPT-5 / Hugging Face | Empathetic conversational intelligence |

---

## 🧱 Architecture Overview

```
 ┌──────────────────────────────────────────┐
 │           Frontend (HTML/CSS/JS)         │
 │  - Chat Interface                        │
 │  - Login & Dashboard Pages               │
 │  - Mood Visualization                    │
 └─────────────────┬────────────────────────┘
                   │
                   ▼
 ┌──────────────────────────────────────────┐
 │         Backend (PHP Application)        │
 │  - User Sessions & Authentication        │
 │  - DB Communication                      │
 │  - API Bridge (PHP → Python)             │
 └─────────────────┬────────────────────────┘
                   │
                   ▼
 ┌──────────────────────────────────────────┐
 │        AI Engine (Python Flask API)      │
 │  - Emotion Detection                     │
 │  - GPT-5 Counsellor Response Generation  │
 │  - Safety & Crisis Detection             │
 └──────────────────────────────────────────┘
```

---

## 🖋️ UI/UX Design Guidelines

1. **Color Palette:** soft blues, whites, pastel greens — relaxing and neutral.  
2. **Typography:** *Poppins*, *Lato*, or *Nunito Sans* for clarity and warmth.  
3. **Minimalism:** clean layout focused on user dialogue.  
4. **Responsiveness:** mobile, tablet, and desktop friendly.  
5. **Micro-animations:** subtle typing indicators and message fades.

---

## 📂 Folder Structure

```
mindmate/
├── index.html                 # Landing Page
├── login.php                  # Login Page
├── register.php               # Registration Page
├── chat.php                   # Chat Interface
├── dashboard.php              # Mood Dashboard
├── assets/
│   ├── css/style.css          # Global Styling
│   ├── js/main.js             # Frontend Logic
│   └── images/                # Brand & UI Assets
├── backend/
│   ├── config.php             # DB Configuration
│   ├── db_connect.php         # Connection Script
│   ├── save_chat.php          # Stores Conversations
│   └── api_bridge.php         # Calls Python API
├── ai_engine/
│   ├── app.py                 # Flask AI Backend
│   ├── models/emotion_model.pkl
│   └── utils/text_cleaner.py
└── README.md
```

---

## ⚙️ Installation Guide

### **1️⃣ Clone the Repository**
```bash
git clone https://github.com/yourusername/mindmate-ai.git
cd mindmate-ai
```

### **2️⃣ Configure MySQL**
```sql
CREATE DATABASE mindmate;
USE mindmate;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50),
  email VARCHAR(100),
  password_hash VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  user_message TEXT,
  ai_response TEXT,
  sentiment VARCHAR(50),
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Update your `backend/config.php`:
```php
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "mindmate";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>
```

---

### **3️⃣ Setup Python AI Engine**
```bash
cd ai_engine
python3 -m venv venv
source venv/bin/activate
pip install flask openai transformers
```

Create `app.py`:
```python
from flask import Flask, request, jsonify
from transformers import pipeline
import openai

app = Flask(__name__)
sentiment_analyzer = pipeline("sentiment-analysis")
openai.api_key = "YOUR_API_KEY"

@app.route("/chat", methods=["POST"])
def chat():
    data = request.get_json()
    user_input = data.get("message", "")
    sentiment = sentiment_analyzer(user_input)[0]['label']
    response = openai.ChatCompletion.create(
        model="gpt-5",
        messages=[
            {"role": "system", "content": "You are an empathetic AI counsellor."},
            {"role": "user", "content": user_input}
        ]
    )
    ai_message = response.choices[0].message.content
    return jsonify({"reply": ai_message, "sentiment": sentiment})

if __name__ == "__main__":
    app.run(port=5000)
```

---

### **4️⃣ Connect PHP → Python API**

In `backend/api_bridge.php`:
```php
<?php
function getAIResponse($message) {
    $data = json_encode(["message" => $message]);
    $ch = curl_init("http://127.0.0.1:5000/chat");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}
?>
```

---

### **5️⃣ Run the Application**

Start the Python API:
```bash
python ai_engine/app.py
```

Start your PHP server (Apache/XAMPP):
```
http://localhost/mindmate
```

---

## 🎨 Frontend Highlights

- **Calming Landing Page** with mission statement and soft animations.  
- **Chat Interface** styled like a modern messenger with smooth typing effects.  
- **Mood Dashboard** visualized with Chart.js or Plotly.  
- **Dark/Light Mode Toggle** for accessibility.  

---

## 🔐 Privacy & Ethics

- Encrypted user chat data.  
- Anonymous sessions available.  
- Crisis detection for self-harm phrases.  
- GDPR-compliant user data retention.  

---

## 🚀 Future Roadmap

- Mobile App (React Native / Flutter)  
- Voice-enabled counselling (Speech-to-Text)  
- Journaling and Emotion Diary Integration  
- Real Therapist Referral System  

---

## 💰 Funding Overview

| Category | Budget (USD) |
|-----------|---------------|
| MVP Development | $15,000 |
| AI API & Hosting | $10,000 |
| UI/UX & Branding | $5,000 |
| Marketing & Partnerships | $8,000 |

---

## 🧾 License
This project is licensed under the **MIT License** — open for ethical innovation and mental-health advancement.

---

## 🤝 Contributing
We welcome collaboration from **mental-health professionals**, **AI researchers**, and **frontend designers**.  
Please fork the repo, open issues, and submit PRs.

---

## 👨‍💻 Author

**Ahmad Akmal**  
*Cloud & AI Solutions Architect*  
📧 contact@mindmate.ai  
🌍 [https://www.mindmate.ai](https://www.mindmate.ai) *(placeholder)*  

---

> “Technology should heal, not just help. MindMate brings empathy to AI.” 💙
