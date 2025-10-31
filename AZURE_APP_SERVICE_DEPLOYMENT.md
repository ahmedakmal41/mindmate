# 🌐 Azure App Service Deployment Guide

## Why Azure App Service?

Since you're already using Azure Cosmos DB, deploying on Azure App Service provides:
- ✅ **Better Integration** - Same Azure region, lower latency
- ✅ **Easier Management** - Single Azure portal for everything
- ✅ **Better Security** - VNet integration, managed identity options
- ✅ **Cost Effective** - No egress charges between services
- ✅ **Auto-scaling** - Built-in scaling capabilities

## 🚀 Quick Deployment (10 minutes)

### Option 1: Automated Script Deployment

1. **Run the deployment script:**
   ```bash
   ./azure-deploy.sh
   ```

2. **Set MongoDB connection string:**
   ```bash
   az webapp config appsettings set \
     --name mindmate-app \
     --resource-group mindmate-rg \
     --settings MONGODB_CONNECTION_STRING='your-connection-string-from-azure'
   ```

3. **Deploy your code:**
   ```bash
   zip -r mindmate.zip . -x "*.git*" "node_modules/*" "tests/*"
   az webapp deployment source config-zip \
     --name mindmate-app \
     --resource-group mindmate-rg \
     --src mindmate.zip
   ```

### Option 2: Manual Azure Portal Deployment

1. **Create App Service:**
   - Go to [Azure Portal](https://portal.azure.com)
   - Create Resource → Web App
   - Name: `mindmate-app`
   - Runtime: PHP 8.2
   - Region: Same as your Cosmos DB

2. **Configure Environment Variables:**
   - Go to Configuration → Application settings
   - Add these variables:
     ```
     DB_TYPE = mongodb
     COSMOS_DATABASE = mindmate
     AI_API_URL = https://aiengine-sable.vercel.app
     MONGODB_CONNECTION_STRING = [Get from Cosmos DB]
     ```

3. **Deploy Code:**
   - Use GitHub Actions (see `azure-app-service-deploy.yml`)
   - Or use VS Code Azure extension
   - Or upload ZIP file

## 📁 Files Created for Azure Deployment

| File | Purpose |
|------|---------|
| `azure-deploy.sh` | Automated deployment script |
| `web.config` | IIS configuration for PHP |
| `azure-app-service-deploy.yml` | GitHub Actions workflow |
| `azure-app-service-config.json` | App Service configuration |
| `azure_debug.php` | Environment diagnostics |

## ⚙️ Configuration Details

### App Service Settings
```json
{
  "DB_TYPE": "mongodb",
  "COSMOS_DATABASE": "mindmate", 
  "AI_API_URL": "https://aiengine-sable.vercel.app",
  "MONGODB_CONNECTION_STRING": "[FROM_COSMOS_DB]"
}
```

### PHP Extensions Required
- ✅ mongodb (automatically installed)
- ✅ curl (built-in)
- ✅ json (built-in)
- ✅ openssl (built-in)

## 🔧 Getting MongoDB Connection String

1. Go to Azure Portal
2. Find your `mindmate-cdb` Cosmos DB account
3. Go to "Connection String" section
4. Copy **PRIMARY CONNECTION STRING** (MongoDB format)
5. Should look like:
   ```
   mongodb://mindmate-cdb:[KEY]@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@
   ```

## 🚀 Deployment Methods

### Method 1: GitHub Actions (Recommended)
1. Add `azure-app-service-deploy.yml` to `.github/workflows/`
2. Set up publish profile secret in GitHub
3. Push to main branch → auto-deploy

### Method 2: Azure CLI
```bash
# Deploy from ZIP
az webapp deployment source config-zip \
  --name mindmate-app \
  --resource-group mindmate-rg \
  --src mindmate.zip
```

### Method 3: VS Code Extension
1. Install Azure App Service extension
2. Right-click project → Deploy to Web App
3. Select your App Service

## 🔍 Testing Your Deployment

### 1. Debug Endpoint
Visit: `https://mindmate-app.azurewebsites.net/azure_debug.php`

Should show:
- ✅ Azure App Service detection
- ✅ Environment variables loaded
- ✅ MongoDB connection working
- ✅ PHP extensions loaded

### 2. Application Endpoints
- Login: `https://mindmate-app.azurewebsites.net/login.php`
- Register: `https://mindmate-app.azurewebsites.net/register.php`
- Dashboard: `https://mindmate-app.azurewebsites.net/dashboard.php`

## 🛠️ Troubleshooting

### Issue 1: MongoDB Extension Missing
**Solution:** Azure App Service PHP 8.2 includes MongoDB extension by default

### Issue 2: Connection String Not Set
**Solution:** 
```bash
az webapp config appsettings set \
  --name mindmate-app \
  --resource-group mindmate-rg \
  --settings MONGODB_CONNECTION_STRING='your-connection-string'
```

### Issue 3: File Permissions
**Solution:** Azure App Service handles permissions automatically

### Issue 4: HTTPS Redirect Issues
**Solution:** The `web.config` handles HTTPS redirects properly

## 💰 Cost Estimation

**Basic Tier (B1):**
- ~$13/month
- 1.75 GB RAM
- 10 GB storage
- Custom domains
- SSL certificates

**Standard Tier (S1):**
- ~$56/month  
- 1.75 GB RAM
- 50 GB storage
- Auto-scaling
- Staging slots

## 🔒 Security Features

- ✅ HTTPS only (enforced)
- ✅ Security headers in web.config
- ✅ Environment variables encrypted
- ✅ Azure AD integration available
- ✅ VNet integration possible

## 📊 Monitoring & Logging

Azure App Service provides:
- Application Insights integration
- Live metrics
- Log streaming
- Performance monitoring
- Error tracking

## 🎯 Next Steps After Deployment

1. **Test the debug endpoint**
2. **Configure custom domain** (optional)
3. **Set up Application Insights** (recommended)
4. **Configure backup** (recommended)
5. **Set up staging slots** (for production)

## 🆚 Azure vs Render Comparison

| Feature | Azure App Service | Render |
|---------|------------------|--------|
| Integration with Cosmos DB | ✅ Native | ❌ External |
| Cost | ~$13/month | ~$7/month |
| Scaling | ✅ Advanced | ✅ Basic |
| Monitoring | ✅ Built-in | ❌ Limited |
| Custom domains | ✅ Free SSL | ✅ Free SSL |
| Deployment | ✅ Multiple options | ✅ Git-based |

Azure App Service is the better choice for your setup since you're already using Azure Cosmos DB!