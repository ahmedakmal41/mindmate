# 🚀 Azure Deployment Scripts for MindMate

This directory contains several scripts to help you deploy and manage your MindMate application on Azure App Service.

## 📋 Available Scripts

### 1. `deploy-to-azure.sh` - Complete Deployment
**Purpose**: Deploys the entire MindMate application from scratch
**What it does**:
- Creates Azure Resource Group
- Creates App Service Plan
- Creates MySQL Database
- Creates Web App (PHP) and AI App (Python)
- Configures environment variables
- Deploys application code
- Tests the deployment

**Usage**:
```bash
./deploy-to-azure.sh
```

### 2. `update-env-vars.sh` - Environment Variables Manager
**Purpose**: Updates environment variables for existing Azure App Services
**What it does**:
- Updates Web App environment variables
- Updates AI App environment variables
- Configures CORS settings
- Restarts applications
- Shows current environment variables
- Tests application health

**Usage**:
```bash
./update-env-vars.sh
```

### 3. `quick-env-update.sh` - Quick Environment Update
**Purpose**: Simple script for quick environment variable updates
**What it does**:
- Updates basic environment variables
- Configures CORS
- Restarts applications

**Usage**:
```bash
./quick-env-update.sh
```

### 4. `azure-deploy.sh` - Original Deployment Script
**Purpose**: Creates Azure resources only (doesn't deploy code)
**What it does**:
- Creates all Azure resources
- Configures basic settings
- Provides deployment instructions

**Usage**:
```bash
./azure-deploy.sh
```

## 🔧 Prerequisites

1. **Azure CLI installed and configured**
   ```bash
   az --version
   ```

2. **Logged in to Azure**
   ```bash
   az login
   ```

3. **Deployment packages created**
   - `mindmate-web.zip` (PHP web application)
   - `mindmate-ai.zip` (Python AI engine)

## 🚀 Quick Start

### Option 1: Complete Deployment (Recommended)
```bash
# 1. Login to Azure
az login

# 2. Run complete deployment
./deploy-to-azure.sh
```

### Option 2: Step-by-Step Deployment
```bash
# 1. Create Azure resources
./azure-deploy.sh

# 2. Deploy applications manually
az webapp deployment source config-zip \
  --resource-group mindmate-rg \
  --name mindmate-web \
  --src mindmate-web.zip

az webapp deployment source config-zip \
  --resource-group mindmate-rg \
  --name mindmate-ai \
  --src mindmate-ai.zip

# 3. Update environment variables
./update-env-vars.sh
```

## 📊 Environment Variables

### Web App Environment Variables
- `DB_HOST`: MySQL server hostname
- `DB_USER`: MySQL username
- `DB_PASS`: MySQL password
- `DB_NAME`: Database name
- `AI_API_URL`: AI application URL
- `APP_ENV`: Application environment (production)

### AI App Environment Variables
- `AZURE_API_KEY`: Azure OpenAI API key
- `AZURE_ENDPOINT`: Azure OpenAI endpoint
- `DEPLOYMENT_NAME`: GPT deployment name
- `FLASK_ENV`: Flask environment (production)

## 🔍 Troubleshooting

### Common Issues

1. **"az: command not found"**
   - Install Azure CLI: `brew install azure-cli`

2. **"Not logged in to Azure CLI"**
   - Run: `az login`

3. **"Deployment packages not found"**
   - Ensure `mindmate-web.zip` and `mindmate-ai.zip` exist in the current directory

4. **"Resource group not found"**
   - Run the deployment script first to create resources

5. **"App Service not found"**
   - Check if the apps were created successfully
   - Verify the resource group name

### Debug Commands

```bash
# Check Azure login status
az account show

# List resource groups
az group list

# List app services
az webapp list --resource-group mindmate-rg

# Check app service status
az webapp show --resource-group mindmate-rg --name mindmate-web

# View application logs
az webapp log tail --resource-group mindmate-rg --name mindmate-web
az webapp log tail --resource-group mindmate-rg --name mindmate-ai

# Test application health
curl https://mindmate-ai.azurewebsites.net/health
curl https://mindmate-web.azurewebsites.net
```

## 📈 Monitoring and Maintenance

### Application Health Checks
```bash
# Test AI API
curl -X POST https://mindmate-ai.azurewebsites.net/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello", "user_id": "test"}'

# Test Web App
curl https://mindmate-web.azurewebsites.net
```

### View Logs
```bash
# Web App logs
az webapp log tail --resource-group mindmate-rg --name mindmate-web

# AI App logs
az webapp log tail --resource-group mindmate-rg --name mindmate-ai
```

### Restart Applications
```bash
# Restart Web App
az webapp restart --resource-group mindmate-rg --name mindmate-web

# Restart AI App
az webapp restart --resource-group mindmate-rg --name mindmate-ai
```

## 🔄 Updating Applications

### Update Environment Variables
```bash
./update-env-vars.sh
```

### Redeploy Applications
```bash
# Redeploy Web App
az webapp deployment source config-zip \
  --resource-group mindmate-rg \
  --name mindmate-web \
  --src mindmate-web.zip

# Redeploy AI App
az webapp deployment source config-zip \
  --resource-group mindmate-rg \
  --name mindmate-ai \
  --src mindmate-ai.zip
```

## 💰 Cost Management

### Estimated Monthly Costs
- **App Service Plan (B1)**: ~$13/month
- **MySQL Flexible Server (B1ms)**: ~$12/month
- **Total**: ~$25/month

### Cost Optimization
- Use smaller instance sizes for development
- Implement auto-scaling for production
- Monitor usage with Azure Cost Management

## 🆘 Support

If you encounter issues:

1. **Check the logs** using the debug commands above
2. **Verify environment variables** are set correctly
3. **Test individual components** (database, AI API, web app)
4. **Check Azure service health** in the Azure Portal
5. **Review the deployment logs** in Azure App Service

## 📚 Additional Resources

- [Azure App Service Documentation](https://docs.microsoft.com/en-us/azure/app-service/)
- [Azure CLI Documentation](https://docs.microsoft.com/en-us/cli/azure/)
- [MySQL Flexible Server Documentation](https://docs.microsoft.com/en-us/azure/mysql/flexible-server/)
- [Azure OpenAI Documentation](https://docs.microsoft.com/en-us/azure/cognitive-services/openai/)

---

**🎉 Happy Deploying!** Your MindMate application is ready to help users with their mental health journey.
