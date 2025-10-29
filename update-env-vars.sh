#!/bin/bash

# MindMate - Update Environment Variables Script
# This script updates environment variables for both Azure App Services

# Configuration
RESOURCE_GROUP="mindmate-rg"
WEB_APP_NAME="mindmate-web"
AI_APP_NAME="mindmate-ai"
MYSQL_SERVER="mindmate-mysql"
MYSQL_ADMIN_USER="mindmateadmin"
MYSQL_ADMIN_PASSWORD="MindMate2024!Secure"
MYSQL_DATABASE="mindmate"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔧 MindMate Environment Variables Update Script${NC}"
echo "=================================================="

# Check if user is logged in to Azure
echo -e "${YELLOW}📋 Checking Azure CLI login status...${NC}"
if ! az account show &> /dev/null; then
    echo -e "${RED}❌ Not logged in to Azure CLI. Please run 'az login' first.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Logged in to Azure CLI${NC}"

# Function to update web app environment variables
update_web_app_env() {
    echo -e "${YELLOW}🌐 Updating Web App Environment Variables...${NC}"
    
    # Database configuration
    az webapp config appsettings set \
        --resource-group $RESOURCE_GROUP \
        --name $WEB_APP_NAME \
        --settings \
            DB_HOST="$MYSQL_SERVER.mysql.database.azure.com" \
            DB_USER="$MYSQL_ADMIN_USER" \
            DB_PASS="$MYSQL_ADMIN_PASSWORD" \
            DB_NAME="$MYSQL_DATABASE" \
            AI_API_URL="https://$AI_APP_NAME.azurewebsites.net" \
            APP_ENV="production" \
            SESSION_TIMEOUT="604800" \
            MAX_MESSAGE_LENGTH="1000" \
            RATE_LIMIT_PER_MINUTE="10"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Web App environment variables updated successfully${NC}"
    else
        echo -e "${RED}❌ Failed to update Web App environment variables${NC}"
        return 1
    fi
}

# Function to update AI app environment variables
update_ai_app_env() {
    echo -e "${YELLOW}🤖 Updating AI App Environment Variables...${NC}"
    
    # Azure OpenAI configuration
    az webapp config appsettings set \
        --resource-group $RESOURCE_GROUP \
        --name $AI_APP_NAME \
        --settings \
            AZURE_API_KEY="91d0gBcVt4oAJ5VNaVtyKWdzgeBp4n8QmMe2LPUy9xShQK1vHE7vJQQJ99BGACYeBjFXJ3w3AAAAACOGpqEe" \
            AZURE_ENDPOINT="https://zuse1-ai-foundry-t1-01.cognitiveservices.azure.com/" \
            DEPLOYMENT_NAME="gpt-4.1" \
            FLASK_ENV="production" \
            PYTHONPATH="/home/site/wwwroot" \
            WEBSITES_ENABLE_APP_SERVICE_STORAGE="false" \
            SCM_DO_BUILD_DURING_DEPLOYMENT="true" \
            ENABLE_ORYX_BUILD="true"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ AI App environment variables updated successfully${NC}"
    else
        echo -e "${RED}❌ Failed to update AI App environment variables${NC}"
        return 1
    fi
}

# Function to configure CORS for AI app
configure_cors() {
    echo -e "${YELLOW}🔗 Configuring CORS for AI App...${NC}"
    
    az webapp cors add \
        --resource-group $RESOURCE_GROUP \
        --name $AI_APP_NAME \
        --allowed-origins "https://$WEB_APP_NAME.azurewebsites.net" "http://localhost:8000" "http://127.0.0.1:8000"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ CORS configured successfully${NC}"
    else
        echo -e "${RED}❌ Failed to configure CORS${NC}"
        return 1
    fi
}

# Function to restart both apps
restart_apps() {
    echo -e "${YELLOW}🔄 Restarting applications...${NC}"
    
    # Restart Web App
    az webapp restart \
        --resource-group $RESOURCE_GROUP \
        --name $WEB_APP_NAME
    
    # Restart AI App
    az webapp restart \
        --resource-group $RESOURCE_GROUP \
        --name $AI_APP_NAME
    
    echo -e "${GREEN}✅ Applications restarted${NC}"
}

# Function to display current environment variables
show_env_vars() {
    echo -e "${BLUE}📋 Current Environment Variables:${NC}"
    echo ""
    
    echo -e "${YELLOW}Web App ($WEB_APP_NAME):${NC}"
    az webapp config appsettings list \
        --resource-group $RESOURCE_GROUP \
        --name $WEB_APP_NAME \
        --query "[].{Name:name, Value:value}" \
        --output table
    
    echo ""
    echo -e "${YELLOW}AI App ($AI_APP_NAME):${NC}"
    az webapp config appsettings list \
        --resource-group $RESOURCE_GROUP \
        --name $AI_APP_NAME \
        --query "[].{Name:name, Value:value}" \
        --output table
}

# Function to test application health
test_apps() {
    echo -e "${YELLOW}🧪 Testing application health...${NC}"
    
    # Test AI App health
    echo "Testing AI App health..."
    AI_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" "https://$AI_APP_NAME.azurewebsites.net/health")
    
    if [ "$AI_HEALTH" = "200" ]; then
        echo -e "${GREEN}✅ AI App is healthy${NC}"
    else
        echo -e "${RED}❌ AI App health check failed (HTTP $AI_HEALTH)${NC}"
    fi
    
    # Test Web App
    echo "Testing Web App..."
    WEB_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" "https://$WEB_APP_NAME.azurewebsites.net")
    
    if [ "$WEB_HEALTH" = "200" ]; then
        echo -e "${GREEN}✅ Web App is healthy${NC}"
    else
        echo -e "${RED}❌ Web App health check failed (HTTP $WEB_HEALTH)${NC}"
    fi
}

# Main execution
main() {
    echo -e "${BLUE}Starting environment variables update...${NC}"
    echo ""
    
    # Update environment variables
    update_web_app_env
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Failed to update Web App environment variables${NC}"
        exit 1
    fi
    
    echo ""
    update_ai_app_env
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Failed to update AI App environment variables${NC}"
        exit 1
    fi
    
    echo ""
    configure_cors
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Failed to configure CORS${NC}"
        exit 1
    fi
    
    echo ""
    restart_apps
    
    echo ""
    echo -e "${GREEN}🎉 Environment variables update completed successfully!${NC}"
    echo ""
    
    # Show current environment variables
    show_env_vars
    
    echo ""
    echo -e "${BLUE}🌐 Application URLs:${NC}"
    echo "Web App: https://$WEB_APP_NAME.azurewebsites.net"
    echo "AI API: https://$AI_APP_NAME.azurewebsites.net"
    echo ""
    
    # Test applications
    test_apps
    
    echo ""
    echo -e "${GREEN}✅ All done! Your MindMate application is ready to use.${NC}"
}

# Check if script is being run directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
