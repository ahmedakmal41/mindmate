# Cosmos DB Connection Authentication Fix

## The Problem

You're seeing this error:
```
Fatal error: MongoDB\Driver\Exception\AuthenticationException: Failed to send "saslContinue" command
```

This means your MongoDB connection string is either:
- Not set correctly
- Invalid or expired
- Using the default placeholder value

## Quick Fix

### Step 1: Get Your Connection String from Azure

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to your Cosmos DB account (`mindmate-cdb`)
3. Click **"Connection String"** in the left menu
4. Copy the **PRIMARY CONNECTION STRING**

It should look like:
```
mongodb://mindmate-cdb:XXXXXXXXXXXX@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@
```

### Step 2: Update Your Local .env File

Create or edit `.env` in your project root:

```bash
# .env file
DB_TYPE=mongodb
MONGODB_CONNECTION_STRING=mongodb://mindmate-cdb:YOUR_ACTUAL_KEY@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@
COSMOS_DATABASE=mindmate
AI_API_URL=https://aiengine-sable.vercel.app
PORT=8000
```

**Important:** Replace the entire connection string with the one from Azure Portal.

### Step 3: Restart Your Server

```bash
# Stop the current server (Ctrl+C)
# Then restart
./start-local.sh
```

## For Deployment (Render/Vercel/Azure)

### Render
1. Go to your Render Dashboard
2. Select your web service
3. Go to **Environment** tab
4. Add/Update environment variable:
   - Key: `MONGODB_CONNECTION_STRING`
   - Value: Your connection string from Azure

### Vercel
```bash
# Using Vercel CLI
vercel env add MONGODB_CONNECTION_STRING
# Paste your connection string when prompted
```

### Azure App Service
1. Go to Azure Portal
2. Navigate to your App Service
3. Go to **Configuration** > **Application settings**
4. Add new setting:
   - Name: `MONGODB_CONNECTION_STRING`
   - Value: Your connection string

## Verify Connection

After updating, test the connection:

```bash
php create_test_user_mongodb.php
```

If successful, you should see:
```
✅ Test user created successfully!
```

## Network & Firewall Issues

If you still get errors after setting the correct connection string:

### Check Cosmos DB Firewall

1. In Azure Portal, go to your Cosmos DB
2. Click **Firewall and virtual networks**
3. Enable one of:
   - **Allow access from Azure Portal** (for testing)
   - **Allow access from All networks** (for development)
   - **Add your IP address** (most secure)

### Check Connection String Format

Ensure your connection string:
- ✅ Starts with `mongodb://`
- ✅ Has `ssl=true`
- ✅ Has `retrywrites=false`
- ✅ Has the correct hostname (`.mongo.cosmos.azure.com`)
- ✅ Has the correct port (`:10255`)

## Common Mistakes

❌ **Wrong:** Using the placeholder connection string
```
MONGODB_CONNECTION_STRING=YOUR_MONGODB_CONNECTION_STRING_HERE
```

✅ **Right:** Using your actual connection string from Azure
```
MONGODB_CONNECTION_STRING=mongodb://mindmate-cdb:U3OCkzOqKGp...@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true...
```

❌ **Wrong:** Having spaces or line breaks in the connection string

✅ **Right:** Single line, no spaces around the `=` sign

## Alternative: Use Local MongoDB for Development

If you don't want to use Cosmos DB for local development:

```bash
# Start local MongoDB with Docker
docker run -d -p 27017:27017 --name mindmate-mongo mongo:latest

# Update .env
MONGODB_CONNECTION_STRING=mongodb://localhost:27017
COSMOS_DATABASE=mindmate
```

This gives you full MongoDB features without Azure dependencies.

## Still Having Issues?

1. Check the application logs in `logs/` directory
2. Verify MongoDB extension is installed: `php -m | grep mongodb`
3. Check your `.env` file exists and is in the project root
4. Ensure environment variables are loaded: Check the startup messages

For more help, see:
- `MONGODB_DEPLOYMENT.md` - General MongoDB setup
- `COSMOS_DB_INDEXES.md` - Index setup guide
- Azure Cosmos DB documentation

