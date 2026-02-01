# Docker Setup Instructions

## Prerequisites
- Docker installed
- Docker Compose installed

## Setup Steps

### 1. Build and Start Containers
```bash
cd /home/jjarboe/Projects/PublixTracker
docker-compose up -d
```

### 2. Access the Web Interface
Open your browser to: `http://localhost:8080`

### 3. Configure Gmail Credentials
1. Click "⚙️ Settings" in the navigation menu
2. Enter your Gmail address
3. Enter your Gmail App Password (create at https://myaccount.google.com/apppasswords)
4. Click "Save Configuration"

**Note:** Your credentials are securely stored and will be used automatically for all future syncs.

### 4. Sync Your Receipts
1. Click "🔄 Sync Receipts" button
2. Click "Sync Receipts Now" (credentials will be loaded automatically)
3. Wait for the sync to complete
4. View your dashboard to see results

## Container Management

### View Logs
```bash
# All containers
docker-compose logs -f

# Web container only
docker-compose logs -f web

# Python backend only
docker-compose logs -f python-backend
```

### Stop Containers
```bash
docker-compose down
```

### Restart Containers
```bash
docker-compose restart
```

### Rebuild After Code Changes
```bash
docker-compose down
docker-compose build
docker-compose up -d
```

## Manual Python Script Execution

### Run receipt sync manually
```bash
docker-compose exec python-backend python3 GetReciepts.py
```

**Note:** The script will automatically use credentials from the web settings if configured. Otherwise, it will prompt for credentials.

### View database
```bash
docker-compose exec python-backend python3 ViewDatabase.py
```

### Access Python container shell
```bash
docker-compose exec python-backend bash
```

## Web Pages Available

- **Dashboard** (`/`) - Overview with statistics and recent purchases
- **Top Items** (`/top-items.php`) - Top 50 most purchased items with price analytics
- **Settings** (`/settings.php`) - Configure Gmail credentials
- **Sync** (`/sync.php`) - Manually trigger receipt sync

## Credentials Management

### Where credentials are stored
Credentials are stored in `web/data/config.php` when you use the Settings page. This file:
- Is created automatically when you save credentials via the web interface
- Is shared between the web and Python containers via Docker volume
- Is excluded from Docker builds via `.dockerignore`
- Should be backed up separately if needed

### Priority order for credentials
The Python script checks for credentials in this order:
1. Web config file (`web/data/config.php`) - Set via Settings page
2. Command-line prompt - Manual entry

**Recommendation:** Always use the Settings page for credential management.

## Database Location
The SQLite database is stored in a Docker volume and shared between containers:
- Volume name: `publix-data`
- Python path: `/app/data/publix_tracker.db`
- Web path: `/var/www/html/data/publix_tracker.db`

## Troubleshooting

### Database not found
Make sure the database file is in the shared volume:
```bash
docker-compose exec python-backend ls -la /app/data/
```

### Permission issues
```bash
docker-compose exec python-backend chmod 666 /app/data/publix_tracker.db
```

### Reset everything
```bash
docker-compose down -v  # Warning: This deletes all data
docker-compose up -d
```

## Environment Variables (Optional)
Create a `.env` file in the project root:
```
GMAIL_USER=your-email@gmail.com
GMAIL_PASSWORD=your-app-password
```

## Production Deployment

For production, update `docker-compose.yml`:
1. Use proper secrets management
2. Add SSL/TLS certificates
3. Use nginx reverse proxy
4. Set up proper logging
5. Configure backups for the database volume
