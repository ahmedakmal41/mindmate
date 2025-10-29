#!/bin/bash

# MindMate Azure Deployment Script
# This script creates all necessary Azure resources for the MindMate application

# Configuration
RESOURCE_GROUP="mindmate-rg"
LOCATION="East US"
APP_SERVICE_PLAN="mindmate-plan"
WEB_APP_NAME="mindmate-web"
AI_APP_NAME="mindmate-ai"
MYSQL_SERVER="mindmate-mysql"
MYSQL_DATABASE="mindmate"
MYSQL_ADMIN_USER="mindmateadmin"
MYSQL_ADMIN_PASSWORD="MindMate2024!Secure"

echo "🚀 Starting MindMate Azure Deployment..."

# 1. Create Resource Group
echo "📦 Creating Resource Group..."
az group create \
    --name $RESOURCE_GROUP \
    --location "$LOCATION"

# 2. Create App Service Plan
echo "🏗️ Creating App Service Plan..."
az appservice plan create \
    --name $APP_SERVICE_PLAN \
    --resource-group $RESOURCE_GROUP \
    --location "$LOCATION" \
    --sku B1 \
    --is-linux

# 3. Create MySQL Flexible Server
echo "🗄️ Creating MySQL Database..."
az mysql flexible-server create \
    --resource-group $RESOURCE_GROUP \
    --name $MYSQL_SERVER \
    --location "$LOCATION" \
    --admin-user $MYSQL_ADMIN_USER \
    --admin-password $MYSQL_ADMIN_PASSWORD \
    --sku-name Standard_B1ms \
    --tier Burstable \
    --public-access 0.0.0.0 \
    --storage-size 20

# 4. Create MySQL Database
echo "📊 Creating Database..."
az mysql flexible-server db create \
    --resource-group $RESOURCE_GROUP \
    --server-name $MYSQL_SERVER \
    --database-name $MYSQL_DATABASE

# 5. Create Web App (PHP)
echo "🌐 Creating Web Application..."
az webapp create \
    --resource-group $RESOURCE_GROUP \
    --plan $APP_SERVICE_PLAN \
    --name $WEB_APP_NAME \
    --runtime "PHP|8.2"

# 6. Create AI App (Python)
echo "🤖 Creating AI Application..."
az webapp create \
    --resource-group $RESOURCE_GROUP \
    --plan $APP_SERVICE_PLAN \
    --name $AI_APP_NAME \
    --runtime "PYTHON|3.9"

# 7. Configure Web App Environment Variables
echo "⚙️ Configuring Web App Environment Variables..."
az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $WEB_APP_NAME \
    --settings \
        DB_HOST="$MYSQL_SERVER.mysql.database.azure.com" \
        DB_USER="$MYSQL_ADMIN_USER" \
        DB_PASS="$MYSQL_ADMIN_PASSWORD" \
        DB_NAME="$MYSQL_DATABASE" \
        AI_API_URL="https://$AI_APP_NAME.azurewebsites.net"

# 8. Configure AI App Environment Variables
echo "⚙️ Configuring AI App Environment Variables..."
az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $AI_APP_NAME \
    --settings \
        AZURE_API_KEY="91d0gBcVt4oAJ5VNaVtyKWdzgeBp4n8QmMe2LPUy9xShQK1vHE7vJQQJ99BGACYeBjFXJ3w3AAAAACOGpqEe" \
        AZURE_ENDPOINT="https://zuse1-ai-foundry-t1-01.cognitiveservices.azure.com/" \
        DEPLOYMENT_NAME="gpt-4.1" \
        FLASK_ENV="production"

# 9. Configure CORS for AI App
echo "🔗 Configuring CORS..."
az webapp cors add \
    --resource-group $RESOURCE_GROUP \
    --name $AI_APP_NAME \
    --allowed-origins "https://$WEB_APP_NAME.azurewebsites.net"

# 10. Get deployment URLs
echo "✅ Deployment Complete!"
echo ""
echo "🌐 Web Application URL: https://$WEB_APP_NAME.azurewebsites.net"
echo "🤖 AI Application URL: https://$AI_APP_NAME.azurewebsites.net"
echo "🗄️ Database Server: $MYSQL_SERVER.mysql.database.azure.com"
echo ""
echo "📋 Next Steps:"
echo "1. Deploy your code to both applications"
echo "2. Initialize the database with the schema"
echo "3. Test the application"
echo ""
echo "🔧 Database Connection Details:"
echo "Host: $MYSQL_SERVER.mysql.database.azure.com"
echo "Username: $MYSQL_ADMIN_USER"
echo "Password: $MYSQL_ADMIN_PASSWORD"
echo "Database: $MYSQL_DATABASE"
