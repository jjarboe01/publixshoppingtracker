#!/bin/bash
# Fix permissions for Publix Tracker Docker setup

echo "🔧 Fixing Docker permissions..."

# Stop containers if running
echo "Stopping containers..."
docker-compose down

# Rebuild web container
echo "Rebuilding web container with proper permissions..."
docker-compose build --no-cache web

# Start containers
echo "Starting containers..."
docker-compose up -d

# Wait for containers to start
echo "Waiting for containers to initialize..."
sleep 5

# Verify permissions
echo ""
echo "✓ Verifying permissions..."
docker-compose exec web ls -la /var/www/html/ 2>/dev/null
echo ""
docker-compose exec web ls -la /var/www/html/data/ 2>/dev/null || echo "Data directory will be created on first access"

echo ""
echo "✅ Done! Try accessing http://localhost:8080"
echo ""
echo "If you still see permission errors, run:"
echo "  docker-compose exec web chown -R www-data:www-data /var/www/html/data"
echo "  docker-compose exec web chmod -R 775 /var/www/html/data"
