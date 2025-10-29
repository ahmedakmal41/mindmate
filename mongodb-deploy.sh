#!/bin/bash

# MindMate - MongoDB (Cosmos DB) Deployment Script
# This script updates app settings for existing App Services with MongoDB connection

# Configuration
RESOURCE_GROUP="mindmate-rg"
WEB_APP_NAME="mindmate-web"
AI_APP_NAME="mindmate-ai"
MONGODB_CONNECTION_STRING="mongodb://mindmate-cdb:undefined@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@"
COSMOS_DATABASE="mindmate"

echo "🚀 Starting MindMate MongoDB Deployment..."

# Check if user is logged in
if ! az account show &> /dev/null; then
    echo "❌ Please login to Azure first:"
    echo "   az login"
    exit 1
fi

echo "✅ Azure CLI authenticated"

# Update Web App Environment Variables for MongoDB
echo "🌐 Updating Web App ($WEB_APP_NAME) environment variables for MongoDB..."

az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $WEB_APP_NAME \
    --settings \
        MONGODB_CONNECTION_STRING="$MONGODB_CONNECTION_STRING" \
        COSMOS_DATABASE="$COSMOS_DATABASE" \
        DB_TYPE="mongodb" \
        AI_API_URL="https://$AI_APP_NAME.azurewebsites.net" \
        APP_ENV="production" \
        SESSION_TIMEOUT="604800" \
        MAX_UPLOAD_SIZE="10485760"

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
echo "🎉 MongoDB deployment completed successfully!"
echo ""
echo "📋 Application URLs:"
echo "   Web App: https://$WEB_APP_NAME.azurewebsites.net"
echo "   AI API:  https://$AI_APP_NAME.azurewebsites.net"
echo ""
echo "🔧 MongoDB Configuration:"
echo "   Connection String: $MONGODB_CONNECTION_STRING"
echo "   Database: $COSMOS_DATABASE"
echo ""
echo "📝 Next Steps:"
echo "   1. Deploy your application code"
echo "   2. Install MongoDB PHP driver: composer require mongodb/mongodb"
echo "   3. Test the application endpoints"
echo "   4. Monitor application logs for any issues"
echo ""
echo "💡 Note: MongoDB collections will be created automatically when first used!"
