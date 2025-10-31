#!/bin/bash

# Azure App Service Deployment Script for MindMate
# This script creates and configures an Azure App Service for the MindMate application

set -e

# Configuration
RESOURCE_GROUP="mindmate-rg"
APP_SERVICE_PLAN="mindmate-plan"
WEB_APP_NAME="mindmate-app"
LOCATION="eastus"
PHP_VERSION="8.2"
SKU="B1"

echo "🚀 Deploying MindMate to Azure App Service"
echo "=========================================="

# Check if Azure CLI is installed
if ! command -v az &> /dev/null; then
    echo "❌ Azure CLI is not installed. Please install it first:"
    echo "   https://docs.microsoft.com/en-us/cli/azure/install-azure-cli"
    exit 1
fi

# Login to Azure (if not already logged in)
echo "🔐 Checking Azure login status..."
if ! az account show &> /dev/null; then
    echo "Please log in to Azure:"
    az login
fi

# Get current subscription
SUBSCRIPTION=$(az account show --query id -o tsv)
echo "📊 Using subscription: $SUBSCRIPTION"

# Create resource group if it doesn't exist
echo "📁 Creating resource group: $RESOURCE_GROUP"
az group create \
    --name $RESOURCE_GROUP \
    --location $LOCATION \
    --output table

# Create App Service Plan
echo "📋 Creating App Service Plan: $APP_SERVICE_PLAN"
az appservice plan create \
    --name $APP_SERVICE_PLAN \
    --resource-group $RESOURCE_GROUP \
    --location $LOCATION \
    --sku $SKU \
    --is-linux \
    --output table

# Create Web App
echo "🌐 Creating Web App: $WEB_APP_NAME"
az webapp create \
    --name $WEB_APP_NAME \
    --resource-group $RESOURCE_GROUP \
    --plan $APP_SERVICE_PLAN \
    --runtime "PHP:$PHP_VERSION" \
    --output table

# Configure App Settings
echo "⚙️  Configuring application settings..."

# Set basic app settings
az webapp config appsettings set \
    --name $WEB_APP_NAME \
    --resource-group $RESOURCE_GROUP \
    --settings \
        DB_TYPE="mongodb" \
        COSMOS_DATABASE="mindmate" \
        AI_API_URL="https://aiengine-sable.vercel.app" \
        WEBSITES_ENABLE_APP_SERVICE_STORAGE="true" \
        PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d:/home/site/ini" \
    --output table

# Enable HTTPS only
echo "🔒 Enabling HTTPS only..."
az webapp update \
    --name $WEB_APP_NAME \
    --resource-group $RESOURCE_GROUP \
    --https-only true \
    --output table

# Configure PHP extensions
echo "🔌 Configuring PHP extensions..."
az webapp config set \
    --name $WEB_APP_NAME \
    --resource-group $RESOURCE_GROUP \
    --startup-file "composer install --no-dev --optimize-autoloader && php -S 0.0.0.0:8080 -t ." \
    --output table

# Get the default hostname
HOSTNAME=$(az webapp show \
    --name $WEB_APP_NAME \
    --resource-group $RESOURCE_GROUP \
    --query defaultHostName \
    --output tsv)

echo ""
echo "✅ Azure App Service created successfully!"
echo "=========================================="
echo "🌐 App URL: https://$HOSTNAME"
echo "📊 Resource Group: $RESOURCE_GROUP"
echo "📋 App Service Plan: $APP_SERVICE_PLAN"
echo "🏷️  Web App Name: $WEB_APP_NAME"
echo ""
echo "🔧 Next Steps:"
echo "1. Set MONGODB_CONNECTION_STRING in Azure Portal:"
echo "   az webapp config appsettings set \\"
echo "     --name $WEB_APP_NAME \\"
echo "     --resource-group $RESOURCE_GROUP \\"
echo "     --settings MONGODB_CONNECTION_STRING='your-connection-string'"
echo ""
echo "2. Deploy your code:"
echo "   - Use GitHub Actions (azure-app-service-deploy.yml)"
echo "   - Or use Azure CLI: az webapp deployment source config-zip"
echo "   - Or use VS Code Azure extension"
echo ""
echo "3. Test your deployment:"
echo "   https://$HOSTNAME/azure_debug.php"
echo ""
echo "4. Configure custom domain (optional):"
echo "   az webapp config hostname add --webapp-name $WEB_APP_NAME --resource-group $RESOURCE_GROUP --hostname your-domain.com"

# Show deployment credentials
echo ""
echo "📋 Deployment Information:"
echo "=========================="
az webapp deployment list-publishing-profiles \
    --name $WEB_APP_NAME \
    --resource-group $RESOURCE_GROUP \
    --query "[?publishMethod=='MSDeploy'].{URL:publishUrl,Username:userName}" \
    --output table

echo ""
echo "🎯 To set the MongoDB connection string:"
echo "========================================"
echo "1. Go to Azure Portal: https://portal.azure.com"
echo "2. Navigate to App Services → $WEB_APP_NAME"
echo "3. Go to Configuration → Application settings"
echo "4. Add/Edit MONGODB_CONNECTION_STRING"
echo "5. Get the connection string from your Cosmos DB account"
echo ""
echo "Or use Azure CLI:"
echo "az webapp config appsettings set \\"
echo "  --name $WEB_APP_NAME \\"
echo "  --resource-group $RESOURCE_GROUP \\"
echo "  --settings MONGODB_CONNECTION_STRING='mongodb://mindmate-cdb:[KEY]@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@'"