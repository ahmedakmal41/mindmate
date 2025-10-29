# 🚀 MindMate Azure Deployment Guide

This guide will help you deploy the MindMate application to Azure App Service.

## 📋 Prerequisites

1. **Azure CLI** installed and configured
2. **Git** installed
3. **Azure subscription** with appropriate permissions
4. **Docker** (optional, for local testing)

## 🏗️ Step 1: Create Azure Resources

Run the deployment script to create all necessary Azure resources:

```bash
# Make the script executable
chmod +x azure-deploy.sh

# Run the deployment script
./azure-deploy.sh
```

This script will create:
- Resource Group: `mindmate-rg`
- App Service Plan: `mindmate-plan`
- Web App (PHP): `mindmate-web`
- AI App (Python): `mindmate-ai`
- MySQL Database: `mindmate-mysql`

## 📦 Step 2: Deploy the Applications

### Deploy Web Application (PHP)

```bash
# Navigate to the project root
cd /Users/champion/Documents/Mindmate

# Deploy to Azure Web App
az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-web \
    --src mindmate-web.zip
```

### Deploy AI Application (Python)

```bash
# Create a zip file for the AI engine
cd ai_engine
zip -r ../mindmate-ai.zip .

# Deploy to Azure AI App
az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-ai \
    --src mindmate-ai.zip
```

## 🗄️ Step 3: Initialize Database

1. **Connect to MySQL Database:**
   - Host: `mindmate-mysql.mysql.database.azure.com`
   - Username: `mindmateadmin`
   - Password: `MindMate2024!Secure`
   - Database: `mindmate`

2. **Run the initialization script:**
   ```sql
   -- Create users table
   CREATE TABLE IF NOT EXISTS users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) NOT NULL UNIQUE,
       email VARCHAR(100) NOT NULL UNIQUE,
       password_hash VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       last_login TIMESTAMP NULL,
       is_active BOOLEAN DEFAULT TRUE
   );

   -- Create chats table
   CREATE TABLE IF NOT EXISTS chats (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       user_message TEXT NOT NULL,
       ai_response TEXT NOT NULL,
       sentiment VARCHAR(50),
       confidence DECIMAL(5,4),
       timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );

   -- Create mood_checks table
   CREATE TABLE IF NOT EXISTS mood_checks (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       mood VARCHAR(20) NOT NULL,
       notes TEXT,
       timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );

   -- Create rate_limits table
   CREATE TABLE IF NOT EXISTS rate_limits (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       action VARCHAR(50) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );

   -- Create user_sessions table
   CREATE TABLE IF NOT EXISTS user_sessions (
       id VARCHAR(128) PRIMARY KEY,
       user_id INT NOT NULL,
       ip_address VARCHAR(45),
       user_agent TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );
   ```

## 🔧 Step 4: Configure Application Settings

### Web App Settings
- `DB_HOST`: `mindmate-mysql.mysql.database.azure.com`
- `DB_USER`: `mindmateadmin`
- `DB_PASS`: `MindMate2024!Secure`
- `DB_NAME`: `mindmate`
- `AI_API_URL`: `https://mindmate-ai.azurewebsites.net`

### AI App Settings
- `AZURE_API_KEY`: `91d0gBcVt4oAJ5VNaVtyKWdzgeBp4n8QmMe2LPUy9xShQK1vHE7vJQQJ99BGACYeBjFXJ3w3AAAAACOGpqEe`
- `AZURE_ENDPOINT`: `https://zuse1-ai-foundry-t1-01.cognitiveservices.azure.com/`
- `DEPLOYMENT_NAME`: `gpt-4.1`
- `FLASK_ENV`: `production`

## 🌐 Step 5: Access Your Application

- **Web Application**: https://mindmate-web.azurewebsites.net
- **AI API**: https://mindmate-ai.azurewebsites.net

## 🧪 Step 6: Test the Deployment

1. **Test Web App Health:**
   ```bash
   curl https://mindmate-web.azurewebsites.net
   ```

2. **Test AI API Health:**
   ```bash
   curl https://mindmate-ai.azurewebsites.net/health
   ```

3. **Test AI Chat:**
   ```bash
   curl -X POST https://mindmate-ai.azurewebsites.net/chat \
     -H "Content-Type: application/json" \
     -d '{"message": "Hello, I need help with anxiety", "user_id": "test"}'
   ```

## 🔒 Security Considerations

1. **Database Security:**
   - Use Azure Key Vault for sensitive data
   - Enable SSL/TLS for database connections
   - Implement proper access controls

2. **Application Security:**
   - Enable HTTPS only
   - Configure proper CORS settings
   - Implement rate limiting
   - Use Azure Application Gateway for additional security

3. **API Security:**
   - Implement API authentication
   - Use Azure Active Directory for user management
   - Monitor API usage and implement throttling

## 📊 Monitoring and Logging

1. **Application Insights:**
   - Enable Application Insights for both apps
   - Monitor performance and errors
   - Set up alerts for critical issues

2. **Log Analytics:**
   - Configure log collection
   - Set up custom queries
   - Create dashboards for monitoring

## 🔄 Continuous Deployment

Set up GitHub Actions or Azure DevOps for automated deployment:

```yaml
# .github/workflows/deploy.yml
name: Deploy to Azure
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    - name: Deploy to Azure Web App
      uses: azure/webapps-deploy@v2
      with:
        app-name: 'mindmate-web'
        publish-profile: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}
```

## 🆘 Troubleshooting

### Common Issues:

1. **Database Connection Issues:**
   - Check firewall rules
   - Verify connection string
   - Ensure SSL is properly configured

2. **AI API Not Responding:**
   - Check environment variables
   - Verify Azure OpenAI configuration
   - Check application logs

3. **CORS Issues:**
   - Verify CORS configuration
   - Check allowed origins
   - Ensure proper headers

### Getting Help:

- Check Azure App Service logs
- Use Azure Application Insights
- Review deployment logs
- Check Azure Monitor for metrics

## 📈 Scaling Considerations

1. **Horizontal Scaling:**
   - Configure auto-scaling rules
   - Use multiple instances
   - Implement load balancing

2. **Database Scaling:**
   - Consider read replicas
   - Implement connection pooling
   - Monitor database performance

3. **Caching:**
   - Implement Redis for session storage
   - Use Azure CDN for static content
   - Cache API responses where appropriate

---

**🎉 Congratulations!** Your MindMate application is now deployed on Azure App Service!
