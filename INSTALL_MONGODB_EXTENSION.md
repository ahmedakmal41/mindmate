# Installing MongoDB PHP Extension on macOS

## Quick Steps

### 1. Install the MongoDB extension using PECL
```bash
pecl install mongodb
```

If you get a permission error, use:
```bash
sudo pecl install mongodb
```

### 2. Find your php.ini file
```bash
php --ini
```

Look for the line that says "Loaded Configuration File". This is your active php.ini file.

### 3. Add the extension to php.ini

Open your php.ini file (the path from step 2):
```bash
nano /opt/homebrew/etc/php/8.4/php.ini
```
or
```bash
code /opt/homebrew/etc/php/8.4/php.ini
```

Add this line anywhere in the file (preferably in the "Dynamic Extensions" section):
```ini
extension=mongodb.so
```

Save and close the file.

### 4. Verify the installation
```bash
php -m | grep mongodb
```

You should see `mongodb` in the output.

### 5. Restart your PHP server
If you're running the built-in PHP server, stop it (Ctrl+C) and start it again:
```bash
cd /Users/champion/Documents/Mindmate
bash start-local.sh
```

## Troubleshooting

### Error: "pecl: command not found"
Install PECL with Homebrew:
```bash
brew install php
```

### Error: "Cannot find autoconf"
Install autoconf:
```bash
brew install autoconf
```

### Error: "Cannot find OpenSSL"
Install OpenSSL and link it:
```bash
brew install openssl
export PKG_CONFIG_PATH="/opt/homebrew/opt/openssl/lib/pkgconfig"
pecl install mongodb
```

### The extension still doesn't load
1. Make sure you edited the correct php.ini file (use `php --ini` to confirm)
2. Make sure the line is `extension=mongodb.so` (not `extension="mongodb.so"`)
3. Check for errors: `php -v`
4. Restart your terminal/shell

### Using a different PHP version manager (phpbrew, phpenv, etc.)
If you're using a PHP version manager, make sure you're installing the extension for the correct PHP version:
```bash
# Check your PHP version
php -v

# Install for that version
pecl install mongodb
```

## Alternative: Using Docker (Recommended for Development)

If you don't want to install the extension locally, you can use Docker to run the PHP app with all dependencies:

```bash
# Build the Docker image
docker build -t mindmate-app .

# Run the container
docker run -p 8000:8080 \
  -e DB_TYPE=mongodb \
  -e MONGODB_CONNECTION_STRING="mongodb://mindmate-cdb:undefined@mindmate-cdb.mongo.cosmos.azure.com:10255/?ssl=true&retrywrites=false&replicaSet=globaldb&maxIdleTimeMS=120000&appName=@mindmate-cdb@" \
  -e COSMOS_DATABASE=mindmate \
  -e AI_API_URL=https://aiengine-sable.vercel.app \
  mindmate-app
```

Then access the app at http://localhost:8000

