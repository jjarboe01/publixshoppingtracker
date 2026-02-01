# Quick Fix for "Forbidden" Error

## The Problem
When accessing http://localhost:8080, you see:
```
Forbidden
You don't have permission to access this resource.
```

## The Solution

### Option 1: Use the Fix Script (Recommended)
```bash
cd /home/jjarboe/Projects/PublixTracker
./fix-permissions.sh
```

This script will:
- Stop containers
- Rebuild the web container with proper permissions
- Start containers
- Verify permissions

### Option 2: Manual Fix
```bash
cd /home/jjarboe/Projects/PublixTracker

# Stop containers
docker-compose down

# Rebuild web container
docker-compose build --no-cache web

# Start containers
docker-compose up -d

# Wait a few seconds, then test
curl http://localhost:8080
```

### Option 3: Fix Permissions on Running Container
If containers are already running:
```bash
docker-compose exec web chown -R www-data:www-data /var/www/html/data
docker-compose exec web chmod -R 775 /var/www/html/data
docker-compose restart web
```

## Why This Happens

The "Forbidden" error occurs because:
1. Apache runs as the `www-data` user inside the container
2. The `web/data` directory needs proper ownership and permissions
3. Docker volumes sometimes have incorrect permissions

## What Was Fixed

1. **Created Dockerfile.web** - Custom PHP/Apache image with:
   - SQLite extensions installed
   - Apache modules enabled
   - Proper directory permissions
   - Entrypoint script to ensure data directory exists

2. **Updated docker-compose.yml** - Now builds custom web image instead of using stock PHP image

3. **Added .htaccess** - Apache configuration for proper access control

4. **Added entrypoint script** - Automatically creates and sets permissions on startup

## Verification

After fixing, verify it works:

```bash
# Check web interface
curl http://localhost:8080

# Should see HTML output, not "Forbidden"

# Check permissions
docker-compose exec web ls -la /var/www/html/data/

# Should show:
# drwxrwxr-x ... www-data www-data ... data
```

## Still Not Working?

1. **Check if containers are running:**
   ```bash
   docker-compose ps
   ```

2. **Check web container logs:**
   ```bash
   docker-compose logs web
   ```

3. **Check Apache error log:**
   ```bash
   docker-compose exec web tail -f /var/log/apache2/error.log
   ```

4. **Nuclear option - complete rebuild:**
   ```bash
   docker-compose down -v
   docker-compose build --no-cache
   docker-compose up -d
   ```
   **Warning:** This deletes all data including saved credentials!

## Prevention

To avoid this in the future:
- Always run `docker-compose build` before `docker-compose up -d`
- Use the provided `fix-permissions.sh` script
- Don't manually modify volume permissions from host system

## Files Involved

- `Dockerfile.web` - Custom web container image
- `docker-compose.yml` - Container orchestration
- `web/.htaccess` - Apache access configuration
- `fix-permissions.sh` - Automated fix script
