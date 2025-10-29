#!/bin/bash

# MindMate - Cosmos DB Deployment Script
# This script creates Cosmos DB resources and updates app settings

# Configuration
RESOURCE_GROUP="mindmate-rg"
WEB_APP_NAME="mindmate-web"
AI_APP_NAME="mindmate-ai"
COSMOS_ACCOUNT="mindmate-cosmos"
COSMOS_DATABASE="mindmate"
LOCATION="East US"

echo "🚀 Starting MindMate Cosmos DB Deployment..."

# Check if user is logged in
if ! az account show &> /dev/null; then
    echo "❌ Please login to Azure first:"
    echo "   az login"
    exit 1
fi

echo "✅ Azure CLI authenticated"

# Create Cosmos DB Account
echo "🗄️ Creating Cosmos DB Account..."
az cosmosdb create \
    --resource-group $RESOURCE_GROUP \
    --name $COSMOS_ACCOUNT \
    --locations regionName="$LOCATION" failoverPriority=0 isZoneRedundant=False \
    --capabilities EnableServerless \
    --default-consistency-level Session

if [ $? -eq 0 ]; then
    echo "✅ Cosmos DB Account created successfully"
else
    echo "❌ Failed to create Cosmos DB Account"
    exit 1
fi

# Create Cosmos DB Database
echo "📊 Creating Cosmos DB Database..."
az cosmosdb sql database create \
    --resource-group $RESOURCE_GROUP \
    --account-name $COSMOS_ACCOUNT \
    --name $COSMOS_DATABASE

if [ $? -eq 0 ]; then
    echo "✅ Cosmos DB Database created successfully"
else
    echo "❌ Failed to create Cosmos DB Database"
    exit 1
fi

# Create Containers
echo "📦 Creating Cosmos DB Containers..."

# Users container
az cosmosdb sql container create \
    --resource-group $RESOURCE_GROUP \
    --account-name $COSMOS_ACCOUNT \
    --database-name $COSMOS_DATABASE \
    --name users \
    --partition-key-path "/id" \
    --throughput 400

# Chats container
az cosmosdb sql container create \
    --resource-group $RESOURCE_GROUP \
    --account-name $COSMOS_ACCOUNT \
    --database-name $COSMOS_DATABASE \
    --name chats \
    --partition-key-path "/id" \
    --throughput 400

# Mood checks container
az cosmosdb sql container create \
    --resource-group $RESOURCE_GROUP \
    --account-name $COSMOS_ACCOUNT \
    --database-name $COSMOS_DATABASE \
    --name mood_checks \
    --partition-key-path "/id" \
    --throughput 400

# Rate limits container
az cosmosdb sql container create \
    --resource-group $RESOURCE_GROUP \
    --account-name $COSMOS_ACCOUNT \
    --database-name $COSMOS_DATABASE \
    --name rate_limits \
    --partition-key-path "/id" \
    --throughput 400

# User sessions container
az cosmosdb sql container create \
    --resource-group $RESOURCE_GROUP \
    --account-name $COSMOS_ACCOUNT \
    --database-name $COSMOS_DATABASE \
    --name user_sessions \
    --partition-key-path "/id" \
    --throughput 400

if [ $? -eq 0 ]; then
    echo "✅ Cosmos DB Containers created successfully"
else
    echo "❌ Failed to create Cosmos DB Containers"
    exit 1
fi

# Get Cosmos DB connection details
echo "🔑 Getting Cosmos DB connection details..."
COSMOS_ENDPOINT=$(az cosmosdb show --resource-group $RESOURCE_GROUP --name $COSMOS_ACCOUNT --query documentEndpoint -o tsv)
COSMOS_KEY=$(az cosmosdb keys list --resource-group $RESOURCE_GROUP --name $COSMOS_ACCOUNT --query primaryMasterKey -o tsv)

# Update Web App Environment Variables for Cosmos DB
echo "🌐 Updating Web App ($WEB_APP_NAME) environment variables for Cosmos DB..."

az webapp config appsettings set \
    --resource-group $RESOURCE_GROUP \
    --name $WEB_APP_NAME \
    --settings \
        COSMOS_ENDPOINT="$COSMOS_ENDPOINT" \
        COSMOS_KEY="$COSMOS_KEY" \
        COSMOS_DATABASE="$COSMOS_DATABASE" \
        DB_TYPE="cosmosdb" \
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
echo "🎉 Cosmos DB deployment completed successfully!"
echo ""
echo "📋 Application URLs:"
echo "   Web App: https://$WEB_APP_NAME.azurewebsites.net"
echo "   AI API:  https://$AI_APP_NAME.azurewebsites.net"
echo ""
echo "🔧 Cosmos DB Configuration:"
echo "   Account: $COSMOS_ACCOUNT"
echo "   Database: $COSMOS_DATABASE"
echo "   Endpoint: $COSMOS_ENDPOINT"
echo ""
echo "📝 Next Steps:"
echo "   1. Deploy your application code"
echo "   2. Test the application endpoints"
echo "   3. Monitor application logs for any issues"
echo ""
echo "💡 Note: Cosmos DB containers are automatically created and ready to use!"
