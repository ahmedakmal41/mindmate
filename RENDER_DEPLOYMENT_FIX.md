# Render Deployment MongoDB Fix Guide

## Problem
Your MindMate app is deployed on Render using Docker, and you're getting:
```
Fatal error: Uncaught MongoDB\Driver\Exception\AuthenticationException: Invalid key
```

This is because the `MONGODB_CONNECTION_STRING` environment variable is not properly set in Render.

## Solution Steps

### 1. Get Fresh MongoDB Connection String

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to your Cosmos DB account `mindmate-cdb`
3. Click **"Connection String"** in the left sidebar
4. Copy the **PRIMARY CONNECTION STRING** (MongoDB format)
5. It should look like:
   ```
   mongodb://mindmate-cdb:[KEY]@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@
   ```

### 2. Set Environment Variable in Render Dashboard

1. Go to your [Render Dashboard](https://dashboard.render.com)
2. Find your `mindmate` service
3. Click on your service name
4. Go to **"Environment"** tab
5. Add/Update the environment variable:
   - **Key:** `MONGODB_CONNECTION_STRING`
   - **Value:** [Paste your connection string from Azure]
   - Make sure it's marked as **Secret** (🔒 icon)

### 3. Verify Other Environment Variables

Make sure these are also set in Render:
- `DB_TYPE` = `mongodb`
- `COSMOS_DATABASE` = `mindmate`
- `AI_API_URL` = `https://aiengine-sable.vercel.app`

### 4. Redeploy Your Service

After updating the environment variables:
1. Click **"Manual Deploy"** → **"Deploy latest commit"**
2. Or push a new commit to trigger auto-deploy

## Alternative: Use Render Environment Variables in render.yaml

You can also update your `render.yaml` to reference Render environment variables:

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

## Debugging on Render

### Create a Debug Endpoint

Add this file to help debug environment variables on Render: