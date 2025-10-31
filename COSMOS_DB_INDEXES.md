# Cosmos DB Indexes Guide

## Why Manual Index Creation?

Cosmos DB's MongoDB API has limitations on programmatic index creation. Automatic index creation has been disabled to prevent startup errors. The application will work without these indexes, but performance may be slower on large datasets.

## Creating Indexes via Azure Portal

### Step 1: Navigate to Cosmos DB
1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to your Cosmos DB account (`mindmate-cdb`)
3. Click on **Data Explorer** in the left menu

### Step 2: Create Indexes

For each collection, follow these steps:

#### Users Collection
```javascript
// Email index (unique)
db.users.createIndex({ "email": 1 }, { unique: true })

// Username index (unique)
db.users.createIndex({ "username": 1 }, { unique: true })
```

#### Chats Collection
```javascript
// User ID and timestamp compound index
db.chats.createIndex({ "user_id": 1, "timestamp": -1 })
```

#### Mood Checks Collection
```javascript
// User ID and timestamp compound index
db.mood_checks.createIndex({ "user_id": 1, "timestamp": -1 })
```

#### Rate Limits Collection
```javascript
// User ID and action compound index
db.rate_limits.createIndex({ "user_id": 1, "action": 1 })
```

#### User Sessions Collection
```javascript
// User ID index
db.user_sessions.createIndex({ "user_id": 1 })
```

### Step 3: Verify Indexes

To verify indexes were created:

```javascript
// For each collection
db.users.getIndexes()
db.chats.getIndexes()
db.mood_checks.getIndexes()
db.rate_limits.getIndexes()
db.user_sessions.getIndexes()
```

## Performance Impact

**Without Indexes:**
- App will work normally
- Queries may be slower with large datasets (1000+ records)
- Authentication still secure

**With Indexes:**
- Faster user lookups and authentication
- Faster chat history retrieval
- Better performance with many users

## Troubleshooting

If index creation fails:

1. **Unique constraint violations:** Delete duplicate documents first
2. **Permission issues:** Ensure you have write access to the database
3. **Cosmos DB limits:** Check your account's index limits

## Alternative: Local MongoDB

For local development with full MongoDB features:

```bash
# Use local MongoDB instead
docker run -d -p 27017:27017 --name mindmate-mongo mongo:latest

# Update .env
MONGODB_CONNECTION_STRING=mongodb://localhost:27017
COSMOS_DATABASE=mindmate
```

Local MongoDB supports all index features without limitations.

