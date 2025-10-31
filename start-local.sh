#!/bin/bash
# MindMate - Local Development Startup Script

echo "🚀 Starting MindMate locally..."

# Check if MongoDB extension is installed
if ! php -m 2>/dev/null | grep -q mongodb; then
    echo "❌ ERROR: MongoDB PHP extension is not installed!"
    echo ""
    echo "📋 To install it:"
    echo "   1. sudo pecl install mongodb"
    echo "   2. Add 'extension=mongodb.so' to your php.ini"
    echo "   3. Run: php --ini  (to find your php.ini location)"
    echo ""
    echo "📖 See INSTALL_MONGODB_EXTENSION.md for detailed instructions"
    echo ""
    echo "🐳 Or use Docker instead (recommended):"
    echo "   docker build -t mindmate-app ."
    echo "   docker run -p 8000:8080 \\"
    echo "     -e DB_TYPE=mongodb \\"
    echo "     -e MONGODB_CONNECTION_STRING=\"mongodb://mindmate-cdb:...\" \\"
    echo "     -e COSMOS_DATABASE=mindmate \\"
    echo "     -e AI_API_URL=https://aiengine-sable.vercel.app \\"
    echo "     mindmate-app"
    exit 1
fi

# Load environment variables from .env file if it exists
if [ -f .env ]; then
    echo "📄 Loading environment variables from .env file..."
    export $(grep -v '^#' .env | xargs)
else
    # Set default environment variables
    echo "⚠️  Warning: No .env file found. Using placeholder values."
    echo "   Create a .env file with your actual MongoDB connection string."
    echo ""
    export DB_TYPE=mongodb
    export MONGODB_CONNECTION_STRING="YOUR_MONGODB_CONNECTION_STRING_HERE"
    export COSMOS_DATABASE=mindmate
    export AI_API_URL=https://aiengine-sable.vercel.app
fi

# Find available port
PORT=8000
while lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1 ; do
    echo "⚠️  Port $PORT is in use, trying $((PORT+1))..."
    PORT=$((PORT+1))
done

export PORT

echo "✅ Environment variables set"
echo "🌐 AI Engine: $AI_API_URL"
echo "🗄️ Database: MongoDB (Cosmos DB)"
echo ""
echo "🚀 Starting PHP server on http://localhost:$PORT"
echo "📋 Press Ctrl+C to stop"
echo ""

# Start PHP server
php -S localhost:$PORT
