# Home Assistant Add-on Installation Guide

## Quick Start

1. **Add Repository to Home Assistant**
   - Open Home Assistant
   - Navigate to **Supervisor** → **Add-on Store**
   - Click the **⋮** (menu) button in the top right
   - Select **Repositories**
   - Add this URL: `https://github.com/yourusername/homeassistant-publix-tracker`
   - Click **Add**

2. **Install the Add-on**
   - Refresh the Add-on Store page
   - Find "Publix Shopping Tracker" in the list
   - Click on it and select **Install**
   - Wait for the installation to complete

3. **Configure**
   - Go to the **Configuration** tab
   - Enter your email credentials (see below)
   - Click **Save**

4. **Start**
   - Go to the **Info** tab
   - Click **Start**
   - Enable **Start on boot** (optional)
   - Enable **Watchdog** (optional)

5. **Access**
   - Click **Open Web UI** or
   - Navigate to `http://homeassistant.local:8080`

## Configuration Options

### Email Setup

```yaml
email: your-email@gmail.com
email_provider: auto
app_password: your-16-char-app-password
sync_hour: 5
```

### Getting App Passwords

**Important**: You MUST use an app-specific password, not your regular email password.

#### Gmail
1. Visit [Google App Passwords](https://myaccount.google.com/apppasswords)
2. You must have 2-Step Verification enabled
3. Select **Mail** and your device
4. Copy the 16-character password (remove spaces)

#### Outlook/Hotmail
1. Visit [Microsoft Security Settings](https://account.microsoft.com/security)
2. Enable Two-step verification
3. Under App passwords, create a new one
4. Copy the generated password

#### Yahoo
1. Visit [Yahoo Account Security](https://login.yahoo.com/account/security)
2. Click "Generate app password"
3. Select "Other app" and enter "Publix Tracker"
4. Copy the password

#### iCloud
1. Visit [Apple ID Settings](https://appleid.apple.com/)
2. Go to Security section
3. Under App-Specific Passwords, click "Generate Password"
4. Enter a label like "Publix Tracker"
5. Copy the password

#### AOL
1. Visit [AOL Account Security](https://login.aol.com/account/security)
2. Click "Generate app password"
3. Select "Other app"
4. Copy the password

## Publishing Your Add-on (For Developers)

### 1. Create GitHub Repository

```bash
cd /home/jjarboe/Projects/PublixTracker
git init
git add .
git commit -m "Initial commit - Home Assistant add-on"
git remote add origin https://github.com/yourusername/homeassistant-publix-tracker.git
git push -u origin main
```

### 2. Update Repository URLs

Edit these files and replace `yourusername` with your GitHub username:
- `config.yaml` → `url:` field
- `README.md` → repository links
- `repository.yaml` → `url:` field

### 3. Build Multi-Architecture Images (Optional)

```bash
# Build for multiple architectures
docker buildx create --name multiarch --use
docker buildx inspect --bootstrap

# Build and push
docker buildx build \
  --platform linux/amd64,linux/arm64,linux/arm/v7 \
  -t ghcr.io/yourusername/publix-tracker:latest \
  -f Dockerfile.unified \
  --push .
```

### 4. Create GitHub Release

1. Go to your repository on GitHub
2. Click **Releases** → **Create a new release**
3. Tag: `v1.0.0`
4. Title: `Publix Shopping Tracker v1.0.0`
5. Description: Copy from DOCS.md changelog
6. Click **Publish release**

### 5. Share Your Repository

Users can add your repository URL to Home Assistant:
```
https://github.com/yourusername/homeassistant-publix-tracker
```

## Local Testing

Test the add-on locally before publishing:

```bash
# Build the image
docker compose build

# Start the container
docker compose up -d

# View logs
docker compose logs -f

# Access web interface
open http://localhost:8080

# Stop
docker compose down
```

## Troubleshooting

### Add-on won't start
- Check logs in Home Assistant: Supervisor → Publix Shopping Tracker → Logs
- Verify your email credentials are correct
- Ensure app password is properly generated

### Cannot access web interface
- Verify port 8080 is not in use
- Check Home Assistant firewall settings
- Try accessing via IP: `http://192.168.1.x:8080`

### Receipts not syncing
- Verify email credentials in Settings page
- Check that receipts are from `no-reply@exact.publix.com`
- Check cron logs: `/data/cron.log`

### Database errors
- Ensure `/data` directory has proper permissions
- Check disk space availability
- Try restarting the add-on

## Support

For issues, questions, or feature requests:
- GitHub Issues: https://github.com/yourusername/homeassistant-publix-tracker/issues
- Home Assistant Community Forum: [post your topic URL here]

## Advanced Configuration

### Custom Sync Schedule

Edit the cron schedule in the add-on configuration:
- `sync_hour: 5` runs at 5:00 AM daily
- Adjust to your preference (0-23)

### Manual Sync

You can manually trigger a sync anytime:
1. Open the web interface
2. Go to Settings
3. Click "Sync Receipts"

### Backup Your Data

The database is stored in Home Assistant's `/data` directory and included in snapshots. To manually backup:

```bash
# Access Home Assistant terminal
# Copy database
cp /data/publix_tracker.db /backup/publix_tracker_backup_$(date +%Y%m%d).db
```
