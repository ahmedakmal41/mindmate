# MongoDB Connection Fix Guide

## Problem
You're getting this error:
```
Fatal error: Uncaught MongoDB\Driver\Exception\AuthenticationException: Invalid key
```

This means your MongoDB connection string has invalid or expired credentials.

## Quick Fix Steps

### 1. Get Fresh Connection String from Azure

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to your Cosmos DB account `mindmate-cdb`
3. Click on **"Connection String"** in the left menu
4. Copy the **PRIMARY CONNECTION STRING** (MongoDB format)

### 2. Update Your .env File

Replace the `MONGODB_CONNECTION_STRING` in your `.env` file with the new connection string:

```env
DB_TYPE=mongodb
MONGODB_CONNECTION_STRING=mongodb://mindmate-cdb:[NEW_KEY]@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@
COSMOS_DATABASE=mindmate
AI_API_URL=https://aiengine-sable.vercel.app
PORT=8000
```

### 3. Restart Your Web Server

```bash
# For Apache
sudo systemctl restart apache2

# For Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php-fpm

# Or if using a different setup
sudo service apache2 restart
```

### 4. Test the Connection

Run the validation script:
```bash
php validate_server_setup.php
```

Or access it via web browser:
```
http://your-domain.com/validate_server_setup.php
```

## Diagnostic Scripts

I've created several scripts to help diagnose and fix the issue:

1. **`validate_server_setup.php`** - Complete server validation
2. **`diagnose_mongodb_auth.php`** - MongoDB authentication diagnostics  
3. **`fix_mongodb_connection.php`** - Interactive connection fix utility
4. **`quick_mongo_test.php`** - Simple connection test

## Common Issues & Solutions

### Issue 1: .env File Not Found
**Solution:** Ensure the `.env` file exists in your web root directory with proper permissions:
```bash
chmod 644 .env
```

### Issue 2: Environment Variables Not Loading
**Solution:** Check that `backend/load_env.php` is being included properly in your PHP files.

### Issue 3: MongoDB Extension Missing
**Solution:** Install the MongoDB PHP extension:
```bash
# Ubuntu/Debian
sudo apt-get install php-mongodb

# CentOS/RHEL
sudo yum install php-mongodb

# Or via PECL
sudo pecl install mongodb
```

### Issue 4: Connection String Format
**Expected format:**
```
mongodb://[account-name]:[primary-key]@[account-name].mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@[account-name]@
```

## Verification Steps

After applying the fix:

1. ✅ Run `php validate_server_setup.php`
2. ✅ Check that all tests pass
3. ✅ Try logging into your application
4. ✅ Test user registration
5. ✅ Verify chat functionality works

## Security Notes

- Never commit your `.env` file to version control
- Regenerate keys if they've been exposed
- Use environment-specific connection strings for different deployments
- Consider using Azure Key Vault for production environments

## Need Help?

If you're still having issues:

1. Run the diagnostic scripts and share the output
2. Check your Azure Cosmos DB account status in the portal
3. Verify your IP address is whitelisted in Cosmos DB firewall settings
4. Ensure your Cosmos DB account has the MongoDB API enabled

## Files Created

- `validate_server_setup.php` - Server validation script
- `diagnose_mongodb_auth.php` - Authentication diagnostics
- `fix_mongodb_connection.php` - Connection fix utility
- `quick_mongo_test.php` - Simple connection test
- `MONGODB_FIX_GUIDE.md` - This guide

Run any of these scripts to help diagnose and fix your MongoDB connection issues.