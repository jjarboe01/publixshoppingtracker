# Settings Feature - Quick Start Guide

## 🎯 What This Does
Allows you to manage your Gmail credentials through a web interface instead of editing code files.

## 🚀 Quick Setup (3 Steps)

### Step 1: Access Settings
1. Open http://localhost:8080 in your browser
2. Click "⚙️ Settings" in the navigation menu

### Step 2: Configure Credentials
1. Enter your Gmail address (e.g., `your-email@gmail.com`)
2. Enter your Gmail App Password (16 characters)
3. Click "💾 Save Configuration"

**How to get Gmail App Password:**
- Go to: https://myaccount.google.com/apppasswords
- Enable 2-Step Verification if not already enabled
- Generate a new App Password for "Mail"
- Copy the 16-character password

### Step 3: Sync Receipts
1. Click "🔄 Sync Receipts" in the navigation
2. Click "Sync Receipts Now" button
3. Watch receipts sync automatically

## ✨ Key Features

### 🔐 Secure Storage
- Credentials stored server-side in PHP file
- Not exposed to browser or client
- Excluded from Docker images

### 🔄 Automatic Loading
- Python script loads credentials automatically
- No need to enter credentials each time
- One-click syncing

### ✏️ Easy Updates
- Change email or password anytime
- Update password only (leave email unchanged)
- Update email only (keeps existing password)

### 📊 Status Display
- Shows currently configured email
- Indicates if credentials are set
- Green checkmark when configured

## 📍 Navigation Structure

All pages now have consistent navigation:

```
Dashboard (index.php)
├── 📊 Dashboard (current)
├── 🏆 Top Items
├── 📅 Monthly View
├── 🔍 Search
├── 🔄 Sync Receipts
└── ⚙️ Settings

Top Items (top-items.php)
├── ← Back to Dashboard
├── 🔄 Sync Receipts
└── ⚙️ Settings

Sync (sync.php)
├── ← Back to Dashboard
├── 🏆 Top Items
└── ⚙️ Settings

Settings (settings.php)
├── ← Back to Dashboard
└── 🔄 Sync Receipts
```

## 🎨 User Experience Flow

### First Time User
```
1. Open website
   ↓
2. Click "Settings"
   ↓
3. See "No Credentials" message
   ↓
4. Enter email + password
   ↓
5. Click "Save"
   ↓
6. See "Success" message
   ↓
7. Click "Sync Receipts Now"
   ↓
8. Receipts sync automatically
   ↓
9. View Dashboard
```

### Returning User
```
1. Open website
   ↓
2. Click "Sync Receipts"
   ↓
3. See "Credentials Configured" 
   ↓
4. Click "Sync Receipts Now"
   ↓
5. Done!
```

## 🔧 Technical Details

### File Locations
```
web/
├── settings.php           # Settings interface
├── sync.php              # Auto-loads credentials
├── index.php             # Dashboard
├── top-items.php         # Top items list
└── data/
    └── config.php        # Stored credentials (auto-created)
```

### Credential Priority (Python Script)
1. **web/data/config.php** ← Primary (Settings page)
2. **User prompt** ← Fallback

### Docker Integration
- Shared volume: `publix-data`
- Python path: `/app/data/`
- Web path: `/var/www/html/data/`
- Both containers access same config

## 💡 Pro Tips

### Updating Just Email
1. Enter new email address
2. Leave password field blank
3. Existing password is preserved

### Updating Just Password
1. Keep email address as-is
2. Enter new password
3. Email remains unchanged

### Testing Credentials
After saving:
1. Go to Sync page
2. Should show "Credentials Configured"
3. Click sync to test connection
4. Check output for "Successfully connected"

### Resetting Everything
1. Go to Settings
2. Enter new credentials
3. Click Save
4. Old credentials completely replaced

## 🆘 Troubleshooting

### "Could not save configuration file"
- Check Docker container is running
- Check volume permissions: `docker-compose exec web ls -la /var/www/html/data/`
- Restart containers: `docker-compose restart`

### "No Credentials Configured" on Sync page
- Visit Settings page first
- Enter and save credentials
- Refresh Sync page

### Python script prompts for password
- Check web/data/config.php exists
- Verify file format is correct
- Check file permissions

### Sync fails with "Failed to connect"
- Verify App Password is correct (not regular password)
- Check email address is exact
- Verify 2-Step Verification is enabled on Gmail
- Try regenerating App Password

## 📚 Related Documentation

- `SETTINGS_README.md` - Detailed technical documentation
- `DOCKER_SETUP.md` - Docker setup and deployment
- `IMPLEMENTATION_SUMMARY.md` - Development details

## 🎉 That's It!

Your Publix Tracker now has easy credential management. No more editing code files!
