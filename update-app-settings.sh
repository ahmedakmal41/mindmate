#!/bin/bash

# MindMate - Update Azure App Service Environment Variables
# This script updates environment variables for existing Azure App Services

# Configuration
RESOURCE_GROUP="mindmate-rg"
WEB_APP_NAME="mindmate-web"
AI_APP_NAME="mindmate-ai"

# Database configuration (choose one)
# Option 1: MySQL
MYSQL_SERVER="mindmate-mysql"
MYSQL_ADMIN_USER="mindmateadmin"
MYSQL_ADMIN_PASSWORD="MindMate2024!Secure"
MYSQL_DATABASE="mindmate"

# Option 2: Cosmos DB (uncomment to use)
# COSMOS_ACCOUNT="mindmate-cosmos"
# COSMOS_DATABASE="mindmate"
# COSMOS_KEY="your-cosmos-primary-key"

echo "🔧 Updating Azure App Service Environment Variables..."

# Check if user is logged in
if ! az account show &> /dev/null; then
    echo "❌ Please login to Azure first:"
    echo "   az login"
    exit 1
fi

echo "✅ Azure CLI authenticated"

# Update Web App Environment Variables
echo "🌐 Updating Web App ($WEB_APP_NAME) environment variables..."

# For MySQL
az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $WEB_APP_NAME \
    --settings \
        DB_HOST="$MYSQL_SERVER.mysql.database.azure.com" \
        DB_USER="$MYSQL_ADMIN_USER" \
        DB_PASS="$MYSQL_ADMIN_PASSWORD" \
        DB_NAME="$MYSQL_DATABASE" \
        DB_TYPE="mysql" \
        AI_API_URL="https://$AI_APP_NAME.azurewebsites.net" \
        APP_ENV="production" \
        SESSION_TIMEOUT="604800" \
        MAX_UPLOAD_SIZE="10485760"

# For Cosmos DB (uncomment to use instead of MySQL)
# az webapp config appsettings set \
#     --resource-group $RESOURCE_GROUP \
#     --name $WEB_APP_NAME \
#     --settings \
#         COSMOS_ENDPOINT="https://$COSMOS_ACCOUNT.documents.azure.com:443/" \
#         COSMOS_KEY="$COSMOS_KEY" \
#         COSMOS_DATABASE="$COSMOS_DATABASE" \
#         DB_TYPE="cosmosdb" \
#         AI_API_URL="https://$AI_APP_NAME.azurewebsites.net" \
#         APP_ENV="production" \
#         SESSION_TIMEOUT="604800" \
#         MAX_UPLOAD_SIZE="10485760"

if [ $? -eq 0 ]; then
    echo "✅ Web App environment variables updated successfully"
else
    echo "❌ Failed to update Web App environment variables"
    exit 1
fi

# Update AI App Environment Variables
echo "🤖 Updating AI App ($AI_APP_NAME) environment variables..."

az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $AI_APP_NAME \
    --settings \
        AZURE_API_KEY="91d0gBcVt4oAJ5VNaVtyKWdzgeBp4n8QmMe2LPUy9xShQK1vHE7vJQQJ99BGACYeBjFXJ3w3AAAAACOGpqEe" \
        AZURE_ENDPOINT="https://zuse1-ai-foundry-t1-01.cognitiveservices.azure.com/" \
        DEPLOYMENT_NAME="gpt-4.1" \
        FLASK_ENV="production" \
        LOG_LEVEL="INFO" \
        MAX_TOKENS="500" \
        TEMPERATURE="0.7"

if [ $? -eq 0 ]; then
    echo "✅ AI App environment variables updated successfully"
else
    echo "❌ Failed to update AI App environment variables"
    exit 1
fi

# Configure CORS for AI App
echo "🔗 Configuring CORS for AI App..."

az webapp cors add \
    --resource-group $RESOURCE_GROUP \
    --name $AI_APP_NAME \
    --allowed-origins "https://$WEB_APP_NAME.azurewebsites.net" "http://localhost:8000"

if [ $? -eq 0 ]; then
    echo "✅ CORS configured successfully"
else
    echo "❌ Failed to configure CORS"
fi

# Restart both apps to apply changes
echo "🔄 Restarting applications..."

az webapp restart --resource-group $RESOURCE_GROUP --name $WEB_APP_NAME
az webapp restart --resource-group $RESOURCE_GROUP --name $AI_APP_NAME

echo ""
echo "🎉 Environment variables updated successfully!"
echo ""
echo "📋 Application URLs:"
echo "   Web App: https://$WEB_APP_NAME.azurewebsites.net"
echo "   AI API:  https://$AI_APP_NAME.azurewebsites.net"
echo ""
echo "🔧 Database Configuration:"
echo "   Type: MySQL"
echo "   Host: $MYSQL_SERVER.mysql.database.azure.com"
echo "   Database: $MYSQL_DATABASE"
echo ""
echo "📝 Next Steps:"
echo "   1. Initialize the database with the schema"
echo "   2. Test the application endpoints"
echo "   3. Monitor application logs for any issues"
