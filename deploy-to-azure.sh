#!/bin/bash

# MindMate - Complete Azure Deployment Script
# This script deploys the entire MindMate application to Azure

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

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 MindMate Complete Azure Deployment${NC}"
echo "============================================="

# Check if user is logged in to Azure
echo -e "${YELLOW}📋 Checking Azure CLI login status...${NC}"
if ! az account show &> /dev/null; then
    echo -e "${RED}❌ Not logged in to Azure CLI. Please run 'az login' first.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Logged in to Azure CLI${NC}"

# Function to create resource group
create_resource_group() {
    echo -e "${YELLOW}📦 Creating Resource Group...${NC}"
    az group create \
        --name $RESOURCE_GROUP \
        --location "$LOCATION"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Resource group created successfully${NC}"
    else
        echo -e "${RED}❌ Failed to create resource group${NC}"
        exit 1
    fi
}

# Function to create app service plan
create_app_service_plan() {
    echo -e "${YELLOW}🏗️ Creating App Service Plan...${NC}"
    az appservice plan create \
        --name $APP_SERVICE_PLAN \
        --resource-group $RESOURCE_GROUP \
        --location "$LOCATION" \
        --sku B1 \
        --is-linux
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ App Service Plan created successfully${NC}"
    else
        echo -e "${RED}❌ Failed to create App Service Plan${NC}"
        exit 1
    fi
}

# Function to create MySQL database
create_mysql_database() {
    echo -e "${YELLOW}🗄️ Creating MySQL Database...${NC}"
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
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ MySQL server created successfully${NC}"
    else
        echo -e "${RED}❌ Failed to create MySQL server${NC}"
        exit 1
    fi
    
    # Create database
    echo -e "${YELLOW}📊 Creating Database...${NC}"
    az mysql flexible-server db create \
        --resource-group $RESOURCE_GROUP \
        --server-name $MYSQL_SERVER \
        --database-name $MYSQL_DATABASE
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Database created successfully${NC}"
    else
        echo -e "${RED}❌ Failed to create database${NC}"
        exit 1
    fi
}

# Function to create web app
create_web_app() {
    echo -e "${YELLOW}🌐 Creating Web Application...${NC}"
    az webapp create \
        --resource-group $RESOURCE_GROUP \
        --plan $APP_SERVICE_PLAN \
        --name $WEB_APP_NAME \
        --runtime "PHP|8.2"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Web App created successfully${NC}"
    else
        echo -e "${RED}❌ Failed to create Web App${NC}"
        exit 1
    fi
}

# Function to create AI app
create_ai_app() {
    echo -e "${YELLOW}🤖 Creating AI Application...${NC}"
    az webapp create \
        --resource-group $RESOURCE_GROUP \
        --plan $APP_SERVICE_PLAN \
        --name $AI_APP_NAME \
        --runtime "PYTHON|3.9"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ AI App created successfully${NC}"
    else
        echo -e "${RED}❌ Failed to create AI App${NC}"
        exit 1
    fi
}

# Function to configure environment variables
configure_environment_variables() {
    echo -e "${YELLOW}⚙️ Configuring Environment Variables...${NC}"
    
    # Web App Environment Variables
    az webapp config appsettings set \
        --resource-group $RESOURCE_GROUP \
        --name $WEB_APP_NAME \
        --settings \
            DB_HOST="$MYSQL_SERVER.mysql.database.azure.com" \
            DB_USER="$MYSQL_ADMIN_USER" \
            DB_PASS="$MYSQL_ADMIN_PASSWORD" \
            DB_NAME="$MYSQL_DATABASE" \
            AI_API_URL="https://$AI_APP_NAME.azurewebsites.net" \
            APP_ENV="production"
    
    # AI App Environment Variables
    az webapp config appsettings set \
        --resource-group $RESOURCE_GROUP \
        --name $AI_APP_NAME \
        --settings \
            AZURE_API_KEY="91d0gBcVt4oAJ5VNaVtyKWdzgeBp4n8QmMe2LPUy9xShQK1vHE7vJQQJ99BGACYeBjFXJ3w3AAAAACOGpqEe" \
            AZURE_ENDPOINT="https://zuse1-ai-foundry-t1-01.cognitiveservices.azure.com/" \
            DEPLOYMENT_NAME="gpt-4.1" \
            FLASK_ENV="production"
    
    # Configure CORS
    az webapp cors add \
        --resource-group $RESOURCE_GROUP \
        --name $AI_APP_NAME \
        --allowed-origins "https://$WEB_APP_NAME.azurewebsites.net"
    
    echo -e "${GREEN}✅ Environment variables configured successfully${NC}"
}

# Function to deploy applications
deploy_applications() {
    echo -e "${YELLOW}📦 Deploying Applications...${NC}"
    
    # Deploy Web App
    echo "Deploying Web App..."
    az webapp deployment source config-zip \
        --resource-group $RESOURCE_GROUP \
        --name $WEB_APP_NAME \
        --src mindmate-web.zip
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Web App deployed successfully${NC}"
    else
        echo -e "${RED}❌ Failed to deploy Web App${NC}"
        exit 1
    fi
    
    # Deploy AI App
    echo "Deploying AI App..."
    az webapp deployment source config-zip \
        --resource-group $RESOURCE_GROUP \
        --name $AI_APP_NAME \
        --src mindmate-ai.zip
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ AI App deployed successfully${NC}"
    else
        echo -e "${RED}❌ Failed to deploy AI App${NC}"
        exit 1
    fi
}

# Function to test deployment
test_deployment() {
    echo -e "${YELLOW}🧪 Testing Deployment...${NC}"
    
    # Wait for apps to start
    echo "Waiting for applications to start..."
    sleep 30
    
    # Test AI App
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
    echo -e "${BLUE}Starting complete deployment...${NC}"
    echo ""
    
    # Check if deployment packages exist
    if [ ! -f "mindmate-web.zip" ] || [ ! -f "mindmate-ai.zip" ]; then
        echo -e "${RED}❌ Deployment packages not found. Please ensure mindmate-web.zip and mindmate-ai.zip exist.${NC}"
        exit 1
    fi
    
    # Create Azure resources
    create_resource_group
    create_app_service_plan
    create_mysql_database
    create_web_app
    create_ai_app
    
    # Configure environment
    configure_environment_variables
    
    # Deploy applications
    deploy_applications
    
    # Test deployment
    test_deployment
    
    echo ""
    echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
    echo ""
    echo -e "${BLUE}🌐 Application URLs:${NC}"
    echo "Web App: https://$WEB_APP_NAME.azurewebsites.net"
    echo "AI API: https://$AI_APP_NAME.azurewebsites.net"
    echo ""
    echo -e "${BLUE}🔧 Database Connection:${NC}"
    echo "Host: $MYSQL_SERVER.mysql.database.azure.com"
    echo "Username: $MYSQL_ADMIN_USER"
    echo "Password: $MYSQL_ADMIN_PASSWORD"
    echo "Database: $MYSQL_DATABASE"
    echo ""
    echo -e "${YELLOW}📋 Next Steps:${NC}"
    echo "1. Initialize the database with the schema from init_database.php"
    echo "2. Test the application functionality"
    echo "3. Configure custom domain (optional)"
    echo "4. Set up monitoring and alerts"
    echo ""
    echo -e "${GREEN}✅ Your MindMate application is now live on Azure!${NC}"
}

# Check if script is being run directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
