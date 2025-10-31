#!/bin/bash
# Fix MongoDB PHP library version compatibility

echo "🔧 Fixing MongoDB library version for PHP 8.4..."

# Remove old vendor directory
rm -rf vendor composer.lock

# Install the latest MongoDB library
composer require mongodb/mongodb:^1.19 --ignore-platform-req=ext-mongodb

echo "✅ MongoDB library updated!"
echo ""
echo "Now run: bash start-local.sh"






