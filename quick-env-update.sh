#!/bin/bash

# Quick Environment Variables Update Script
# Simple script to update environment variables for MindMate Azure App Services

RESOURCE_GROUP="mindmate-rg"
WEB_APP_NAME="mindmate-web"
AI_APP_NAME="mindmate-ai"

echo "🔧 Updating MindMate Environment Variables..."

# Update Web App Environment Variables
echo "Updating Web App environment variables..."
az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $WEB_APP_NAME \
    --settings \
        DB_HOST="mindmate-mysql.mysql.database.azure.com" \
        DB_USER="mindmateadmin" \
        DB_PASS="MindMate2024!Secure" \
        DB_NAME="mindmate" \
        AI_API_URL="https://$AI_APP_NAME.azurewebsites.net"

# Update AI App Environment Variables
echo "Updating AI App environment variables..."
az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $AI_APP_NAME \
    --settings \
        AZURE_API_KEY="91d0gBcVt4oAJ5VNaVtyKWdzgeBp4n8QmMe2LPUy9xShQK1vHE7vJQQJ99BGACYeBjFXJ3w3AAAAACOGpqEe" \
        AZURE_ENDPOINT="https://zuse1-ai-foundry-t1-01.cognitiveservices.azure.com/" \
        DEPLOYMENT_NAME="gpt-4.1" \
        FLASK_ENV="production"

# Configure CORS
echo "Configuring CORS..."
az webapp cors add \
    --resource-group $RESOURCE_GROUP \
    --name $AI_APP_NAME \
    --allowed-origins "https://$WEB_APP_NAME.azurewebsites.net"

# Restart applications
echo "Restarting applications..."
az webapp restart --resource-group $RESOURCE_GROUP --name $WEB_APP_NAME
az webapp restart --resource-group $RESOURCE_GROUP --name $AI_APP_NAME

echo "✅ Environment variables updated successfully!"
echo "Web App: https://$WEB_APP_NAME.azurewebsites.net"
echo "AI API: https://$AI_APP_NAME.azurewebsites.net"
