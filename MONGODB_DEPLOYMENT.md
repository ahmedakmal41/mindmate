# 🍃 MindMate MongoDB Deployment Guide

This guide covers deploying MindMate with MongoDB (Cosmos DB MongoDB API) on Azure App Service.

## 📋 Prerequisites

- Azure CLI installed and configured
- Cosmos DB with MongoDB API created
- MongoDB connection string ready
- Application packages ready (`mindmate-web.zip` and `mindmate-ai.zip`)

## 🔧 MongoDB Configuration

### Connection String
```
mongodb://mindmate-cdb:undefined@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@
```

### Database Structure
- **Database**: `mindmate`
- **Collections**:
  - `users` - User accounts
  - `chats` - Chat messages
  - `mood_checks` - Mood tracking data
  - `rate_limits` - API rate limiting
  - `user_sessions` - User sessions

## 🚀 Deployment Steps

### Step 1: Update Environment Variables

Run the MongoDB deployment script to update your existing App Services:

```bash
./mongodb-deploy.sh
```

This script will:
- Update Web App environment variables for MongoDB
- Update AI App environment variables
- Configure CORS settings
- Restart both applications

### Step 2: Deploy Application Code

Deploy your application packages to Azure:

```bash
# Deploy Web App
az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-web \
    --src mindmate-web.zip

# Deploy AI App
az webapp deployment source config-zip \
    --resource-group mindmate-rg \
    --name mindmate-ai \
    --src mindmate-ai.zip
```

### Step 3: Install MongoDB PHP Driver

The MongoDB PHP driver is included in `composer.json` and will be installed automatically during deployment.

## ⚙️ Environment Variables

### Web App Settings
```
MONGODB_CONNECTION_STRING=mongodb://mindmate-cdb:undefined@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@
COSMOS_DATABASE=mindmate
DB_TYPE=mongodb
AI_API_URL=https://mindmate-ai.azurewebsites.net
APP_ENV=production
SESSION_TIMEOUT=604800
MAX_UPLOAD_SIZE=10485760
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

## 🗄️ MongoDB Collections

### Users Collection
```javascript
{
  _id: ObjectId,
  username: String,
  email: String,
  password_hash: String,
  created_at: Date,
  updated_at: Date,
  last_login: Date,
  is_active: Boolean
}
```

### Chats Collection
```javascript
{
  _id: ObjectId,
  user_id: String,
  user_message: String,
  ai_response: String,
  sentiment: String,
  confidence: Number,
  timestamp: Date
}
```

### Mood Checks Collection
```javascript
{
  _id: ObjectId,
  user_id: String,
  mood: String,
  notes: String,
  timestamp: Date
}
```

### Rate Limits Collection
```javascript
{
  _id: ObjectId,
  user_id: String,
  action: String,
  created_at: Date
}
```

### User Sessions Collection
```javascript
{
  _id: String,
  user_id: String,
  ip_address: String,
  user_agent: String,
  created_at: Date,
  last_activity: Date
}
```

## 🔍 Indexes

The following indexes are automatically created for optimal performance:

### Users Collection
- `email` (unique)
- `username` (unique)

### Chats Collection
- `user_id + timestamp` (compound)
- `timestamp` (descending)

### Mood Checks Collection
- `user_id + timestamp` (compound)
- `timestamp` (descending)

### Rate Limits Collection
- `user_id + action + created_at` (compound)
- `created_at` (TTL - 1 hour)

### User Sessions Collection
- `user_id`
- `last_activity` (TTL - 7 days)

## 🧪 Testing Deployment

### 1. Test Web App
```bash
curl https://mindmate-web.azurewebsites.net
```

### 2. Test AI API
```bash
curl https://mindmate-ai.azurewebsites.net/health
```

### 3. Test MongoDB Connection
```bash
# Test user registration
curl -X POST https://mindmate-web.azurewebsites.net/register.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=testuser&email=test@example.com&password=password123"
```

### 4. Test Chat Functionality
```bash
# Login first, then test chat
curl -X POST https://mindmate-ai.azurewebsites.net/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello, I need help with anxiety", "user_id": "test"}'
```

## 📊 Monitoring

### View Application Logs
```bash
# Web App logs
az webapp log tail --resource-group mindmate-rg --name mindmate-web

# AI App logs
az webapp log tail --resource-group mindmate-rg --name mindmate-ai
```

### MongoDB Metrics
1. Go to Azure Portal
2. Navigate to your Cosmos DB account
3. Check "Metrics" for throughput, latency, and errors
4. Monitor "Data Explorer" for collection usage

## 🔒 Security Considerations

### 1. Connection Security
- SSL/TLS enabled by default
- Connection string includes authentication
- Firewall rules can be configured

### 2. Data Security
- Password hashing (bcrypt)
- Input validation and sanitization
- Rate limiting per user

### 3. Network Security
- Azure App Service integration
- CORS configuration
- HTTPS enforcement

## 💰 Cost Optimization

### Cosmos DB Pricing
- **Provisioned Throughput**: Fixed RU/s per collection
- **Serverless**: Pay per request (good for variable workloads)
- **Autoscale**: Automatically scale between min/max RU/s

### Recommended Settings
- **Users Collection**: 400 RU/s (low write, high read)
- **Chats Collection**: 400 RU/s (high write, medium read)
- **Mood Checks Collection**: 400 RU/s (low write, medium read)
- **Rate Limits Collection**: 400 RU/s (high write, short TTL)
- **User Sessions Collection**: 400 RU/s (medium write, TTL)

## 🆘 Troubleshooting

### Common Issues

1. **MongoDB Connection Failed**
   - Check connection string format
   - Verify Cosmos DB account is active
   - Check firewall rules

2. **Collection Not Found**
   - Collections are created automatically
   - Check database name in connection string
   - Verify user permissions

3. **Index Creation Failed**
   - Check MongoDB driver installation
   - Verify connection permissions
   - Check for duplicate index names

### Debug Commands

```bash
# Check app settings
az webapp config appsettings list --resource-group mindmate-rg --name mindmate-web

# Check MongoDB connection
# Use MongoDB Compass or mongo shell with connection string

# Check application logs
az webapp log download --resource-group mindmate-rg --name mindmate-web
```

## 📈 Performance Optimization

### 1. Index Optimization
- Monitor slow queries
- Add compound indexes for common queries
- Use TTL indexes for temporary data

### 2. Connection Pooling
- MongoDB driver handles connection pooling
- Monitor connection usage
- Adjust pool size if needed

### 3. Query Optimization
- Use projection to limit returned fields
- Implement pagination for large datasets
- Use aggregation pipelines for complex queries

## 🔄 Backup and Recovery

### Cosmos DB Backup
- Automatic backups enabled by default
- Point-in-time recovery available
- Geo-redundant storage option

### Application Backup
- Code stored in version control
- Environment variables in Azure
- Database backups handled by Cosmos DB

---

**🎉 Congratulations!** Your MindMate application is now deployed with MongoDB on Azure Cosmos DB!
