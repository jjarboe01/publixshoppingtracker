# Settings Management

## Overview
The Publix Tracker now includes a web-based settings page for managing your Gmail credentials.

## Features

### Web-Based Configuration
- Navigate to **Settings** (⚙️) from any page
- Enter your Gmail address and App Password
- Credentials are saved securely and used automatically

### Automatic Credential Loading
The Python script (`GetReciepts.py`) now loads credentials in this priority order:
1. **Web Config** - From `web/data/config.php` (set via Settings page)
2. **User Prompt** - Manual entry when running script

### Security Notes
- Use Gmail **App Passwords**, not your regular Gmail password
- Create an App Password at: https://myaccount.google.com/apppasswords
- Credentials are stored in a PHP file on the server
- The `web/data/` directory should not be publicly accessible
- The config file is excluded from Docker builds via `.dockerignore`

## Usage

### Via Web Interface
1. Open http://localhost:8080 (or your deployed URL)
2. Click **⚙️ Settings** in the navigation
3. Enter your Gmail address
4. Enter your 16-character Gmail App Password
5. Click **Save Configuration**
6. Go to **🔄 Sync Receipts** and click **Sync Receipts Now**

### Via Command Line
The script will automatically use web-configured credentials:
```bash
python3 GetReciepts.py
```

Or with Docker:
```bash
docker-compose exec python-backend python3 GetReciepts.py
```

## Files Created

### `web/settings.php`
- Web interface for credential management
- Displays current configuration
- Provides instructions for creating App Passwords

### `web/data/config.php`
- Stores credentials in PHP define() statements
- Created automatically when you save via Settings page
- Format:
```php
<?php
define('GMAIL_EMAIL', 'your-email@gmail.com');
define('GMAIL_PASSWORD', 'your-app-password');
```

## Backup Recommendations

Since credentials are stored in `web/data/config.php`, consider:
- Backing up this file separately
- Using environment variables in production
- Setting appropriate file permissions (readable only by web server)

## Docker Considerations

- The `web/data/` directory is on the shared `publix-data` volume
- Both containers (web and python-backend) can access the config
- The config file is excluded from Docker images via `.dockerignore`
- Credentials persist across container restarts
