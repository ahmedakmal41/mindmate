# 🚀 MindMate Azure Deployment Guide

This guide provides multiple deployment options for the MindMate application on Azure App Service.

## 📋 Prerequisites

- Azure CLI installed and configured
- Azure subscription with appropriate permissions
- Application packages ready (`mindmate-web.zip` and `mindmate-ai.zip`)

## 🗄️ Database Options

### Option 1: MySQL Database (Recommended for traditional SQL)
- **Pros**: Familiar SQL syntax, ACID compliance, mature ecosystem
- **Cons**: More complex scaling, higher cost for large scale
- **Best for**: Traditional web applications, complex queries

### Option 2: Cosmos DB (Recommended for NoSQL)
- **Pros**: Global distribution, automatic scaling, serverless option
- **Cons**: Different query syntax, eventual consistency
- **Best for**: Global applications, high-scale scenarios

## 🚀 Deployment Methods

### Method 1: Update Existing App Services (Environment Variables Only)

If your App Services are already created, use this script to update environment variables:

```bash
# For MySQL database
./update-app-settings.sh

# For Cosmos DB
./cosmos-deploy.sh
```

### Method 2: Complete Deployment with MySQL

```bash
# 1. Create all Azure resources
./azure-deploy.sh

# 2. Deploy applications
az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-web \
    --src mindmate-web.zip

az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-ai \
    --src mindmate-ai.zip

# 3. Initialize MySQL database
# Connect to: mindmate-mysql.mysql.database.azure.com
# Username: mindmateadmin
# Password: MindMate2024!Secure
# Run the SQL schema from init_database.php
```

### Method 3: Complete Deployment with Cosmos DB

```bash
# 1. Create App Services first
az group create --name mindmate-rg --location "East US"
az appservice plan create --name mindmate-plan --resource-group mindmate-rg --location "East US" --sku B1 --is-linux
az webapp create --resource-group mindmate-rg --plan mindmate-plan --name mindmate-web --runtime "PHP|8.2"
az webapp create --resource-group mindmate-rg --plan mindmate-plan --name mindmate-ai --runtime "PYTHON|3.9"

# 2. Deploy Cosmos DB and update settings
./cosmos-deploy.sh

# 3. Deploy applications
az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-web \
    --src mindmate-web.zip

az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-ai \
    --src mindmate-ai.zip
```

## ⚙️ Environment Variables

### Web App Settings

**For MySQL:**
```
DB_HOST=mindmate-mysql.mysql.database.azure.com
DB_USER=mindmateadmin
DB_PASS=MindMate2024!Secure
DB_NAME=mindmate
DB_TYPE=mysql
AI_API_URL=https://mindmate-ai.azurewebsites.net
APP_ENV=production
```

**For Cosmos DB:**
```
COSMOS_ENDPOINT=https://mindmate-cosmos.documents.azure.com:443/
COSMOS_KEY=your-cosmos-primary-key
COSMOS_DATABASE=mindmate
DB_TYPE=cosmosdb
AI_API_URL=https://mindmate-ai.azurewebsites.net
APP_ENV=production
```

### AI App Settings

```
AZURE_API_KEY=91d0gBcVt4oAJ5VNaVtyKWdzgeBp4n8QmMe2LPUy9xShQK1vHE7vJQQJ99BGACYeBjFXJ3w3AAAAACOGpqEe
AZURE_ENDPOINT=https://zuse1-ai-foundry-t1-01.cognitiveservices.azure.com/
DEPLOYMENT_NAME=gpt-4.1
FLASK_ENV=production
LOG_LEVEL=INFO
MAX_TOKENS=500
TEMPERATURE=0.7
```

## 🔧 Database Configuration

### MySQL Setup

1. **Create Database:**
   ```sql
   CREATE DATABASE mindmate;
   ```

2. **Run Schema:**
   ```sql
   -- Copy and run the SQL from init_database.php
   ```

3. **Test Connection:**
   ```bash
   mysql -h mindmate-mysql.mysql.database.azure.com -u mindmateadmin -p mindmate
   ```

### Cosmos DB Setup

1. **Containers are auto-created:**
   - `users` - User accounts
   - `chats` - Chat messages
   - `mood_checks` - Mood tracking data
   - `rate_limits` - API rate limiting
   - `user_sessions` - User sessions

2. **No manual schema required** - Cosmos DB is schema-less

## 🧪 Testing Deployment

### 1. Test Web App
```bash
curl https://mindmate-web.azurewebsites.net
```

### 2. Test AI API
```bash
curl https://mindmate-ai.azurewebsites.net/health
```

### 3. Test Chat Functionality
```bash
curl -X POST https://mindmate-ai.azurewebsites.net/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello, I need help with anxiety", "user_id": "test"}'
```

### 4. Test Full Application
1. Open https://mindmate-web.azurewebsites.net
2. Register a new account
3. Login and test chat functionality
4. Check mood tracking on dashboard

## 📊 Monitoring and Logs

### View Application Logs
```bash
# Web App logs
az webapp log tail --resource-group mindmate-rg --name mindmate-web

# AI App logs
az webapp log tail --resource-group mindmate-rg --name mindmate-ai
```

### Monitor Performance
1. Go to Azure Portal
2. Navigate to your App Services
3. Check "Metrics" and "Logs" sections
4. Set up alerts for errors and performance issues

## 🔒 Security Considerations

### 1. Database Security
- **MySQL**: Enable SSL, use strong passwords, restrict IP access
- **Cosmos DB**: Use connection strings, enable firewall rules

### 2. Application Security
- Enable HTTPS only
- Configure proper CORS settings
- Implement rate limiting
- Use Azure Key Vault for secrets

### 3. Network Security
- Use Azure Application Gateway
- Configure WAF rules
- Enable DDoS protection

## 💰 Cost Optimization

### MySQL Database
- **B1ms**: ~$12/month (1 vCore, 2GB RAM)
- **B2s**: ~$24/month (2 vCores, 4GB RAM)

### Cosmos DB
- **Serverless**: Pay per request (good for low traffic)
- **Provisioned**: Fixed throughput (good for consistent traffic)

### App Service
- **B1**: ~$13/month (1 vCore, 1.75GB RAM)
- **B2**: ~$26/month (2 vCores, 3.5GB RAM)

## 🆘 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check firewall rules
   - Verify connection strings
   - Ensure SSL is properly configured

2. **AI API Not Responding**
   - Check environment variables
   - Verify Azure OpenAI configuration
   - Check application logs

3. **CORS Issues**
   - Verify CORS configuration
   - Check allowed origins
   - Ensure proper headers

### Debug Commands

```bash
# Check app settings
az webapp config appsettings list --resource-group mindmate-rg --name mindmate-web

# Check app status
az webapp show --resource-group mindmate-rg --name mindmate-web --query state

# Check logs
az webapp log download --resource-group mindmate-rg --name mindmate-web
```

## 📈 Scaling Considerations

### Horizontal Scaling
- Configure auto-scaling rules
- Use multiple instances
- Implement load balancing

### Database Scaling
- **MySQL**: Read replicas, connection pooling
- **Cosmos DB**: Automatic scaling, global distribution

### Caching
- Implement Redis for session storage
- Use Azure CDN for static content
- Cache API responses where appropriate

## 🔄 Continuous Deployment

### GitHub Actions Example
```yaml
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

---

**🎉 Congratulations!** Your MindMate application is now deployed on Azure App Service with your chosen database backend!
