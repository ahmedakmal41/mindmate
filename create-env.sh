#!/bin/bash
# Create .env file with MongoDB credentials

cat > .env << 'EOF'
DB_TYPE=mongodb
MONGODB_CONNECTION_STRING=YOUR_MONGODB_CONNECTION_STRING_HERE
COSMOS_DATABASE=mindmate
AI_API_URL=https://aiengine-sable.vercel.app
PORT=8000
EOF

echo "✅ .env file created successfully!"
echo ""
echo "📋 Contents:"
cat .env


