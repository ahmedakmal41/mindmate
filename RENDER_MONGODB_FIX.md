# 🐳 Render MongoDB Connection Fix

## The Problem
Your MindMate app on Render is showing:
```
Fatal error: Uncaught MongoDB\Driver\Exception\AuthenticationException: Invalid key
```

This happens because the `MONGODB_CONNECTION_STRING` environment variable in Render has an invalid/expired Azure Cosmos DB key.

## 🚀 Quick Fix (5 minutes)

### Step 1: Get Fresh Connection String from Azure
1. Go to [Azure Portal](https://portal.azure.com)
2. Search for `mindmate-cdb` (your Cosmos DB account)
3. Click on your Cosmos DB account
4. In the left menu, click **"Connection String"**
5. Copy the **PRIMARY CONNECTION STRING** (MongoDB format)

### Step 2: Update Render Environment Variable
1. Go to [Render Dashboard](https://dashboard.render.com)
2. Find your `mindmate` service and click on it
3. Go to **"Environment"** tab
4. Find `MONGODB_CONNECTION_STRING` or click **"Add Environment Variable"**
5. Set:
   - **Key:** `MONGODB_CONNECTION_STRING`
   - **Value:** [Paste your connection string from Azure]
   - **Important:** Click the 🔒 lock icon to make it a secret
6. Click **"Save Changes"**

### Step 3: Redeploy
1. In your Render service, click **"Manual Deploy"**
2. Select **"Deploy latest commit"**
3. Wait for deployment to complete (2-3 minutes)

### Step 4: Test
Visit your debug endpoint to verify:
```
https://your-app-name.onrender.com/render_debug.php
```

## 🔍 Debug Your Deployment

I've created debug scripts you can use:

### 1. Check Environment Variables
Visit: `https://your-app.onrender.com/render_debug.php`

This will show:
- ✅ All environment variables status
- ✅ MongoDB connection test
- ✅ PHP extensions check
- ✅ File system verification

### 2. Environment Setup Helper
Visit: `https://your-app.onrender.com/render_env_setup.php`

This provides step-by-step configuration guidance.

## 📋 Required Environment Variables

Make sure these are set in your Render Dashboard:

| Variable | Value | Secret? | Description |
|----------|-------|---------|-------------|
| `DB_TYPE` | `mongodb` | No | Database type |
| `MONGODB_CONNECTION_STRING` | `mongodb://mindmate-cdb:[KEY]@...` | **Yes** 🔒 | Azure connection string |
| `COSMOS_DATABASE` | `mindmate` | No | Database name |
| `AI_API_URL` | `https://aiengine-sable.vercel.app` | No | AI service URL |
| `PORT` | `8080` | No | App port (auto-set by Render) |

## 🛠️ Common Issues & Solutions

### Issue 1: "Invalid key" Error
**Cause:** Expired/wrong MongoDB connection string  
**Fix:** Get fresh connection string from Azure Portal

### Issue 2: Environment Variables Not Loading
**Cause:** Variables not set in Render Dashboard  
**Fix:** Set them in Environment tab, not just in render.yaml

### Issue 3: Connection Timeout
**Cause:** Azure Cosmos DB firewall blocking Render IPs  
**Fix:** In Azure Portal → Cosmos DB → Networking → Allow access from Azure services

### Issue 4: App Not Starting
**Cause:** Missing environment variables  
**Fix:** Check all required variables are set in Render Dashboard

## 🔧 Your Current Configuration

Your `render.yaml` looks correct:
```yaml
services:
  - type: web
    name: mindmate
    env: docker
    dockerfilePath: ./Dockerfile
    envVars:
      - key: MONGODB_CONNECTION_STRING
        fromService:
          type: web
          name: mindmate
          envVarKey: MONGODB_CONNECTION_STRING
      - key: COSMOS_DATABASE
        value: mindmate
      - key: AI_API_URL
        value: https://aiengine-sable.vercel.app
      - key: DB_TYPE
        value: mongodb
```

The key point is that `MONGODB_CONNECTION_STRING` references a service environment variable, which means you **must** set it in the Render Dashboard.

## 🎯 Verification Steps

After fixing:

1. ✅ Visit `https://your-app.onrender.com/render_debug.php`
2. ✅ Should show "✅ RENDER DEPLOYMENT READY!"
3. ✅ Test login: `https://your-app.onrender.com/login.php`
4. ✅ Test registration: `https://your-app.onrender.com/register.php`

## 🔒 Security Notes

- Always mark `MONGODB_CONNECTION_STRING` as secret (🔒) in Render
- Never commit connection strings to your repository
- Regenerate keys if they've been exposed
- Use different connection strings for different environments

## 📞 Still Having Issues?

1. Run the debug script and share the output
2. Check your Azure Cosmos DB account status
3. Verify your Cosmos DB has MongoDB API enabled
4. Ensure your Azure account has proper permissions

The fix is usually just updating the connection string in Render Dashboard and redeploying!