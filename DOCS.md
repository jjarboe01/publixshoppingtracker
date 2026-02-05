# Home Assistant Add-on: Publix Shopping Tracker

## About

This add-on automatically retrieves and tracks your Publix shopping receipts from your email account. It parses receipt details, stores them in a local database, and provides a web interface for analyzing your shopping habits, tracking savings, and viewing spending trends.

## Features

- **Automatic Receipt Retrieval**: Syncs receipts daily from your email
- **Multi-Provider Support**: Works with Gmail, Outlook, Yahoo, iCloud, and AOL
- **Shopping Analytics**: View top items, monthly/yearly spending, and individual trips
- **Savings Tracking**: See how much you've saved with sales and promotions
- **BOGO Support**: Properly handles Buy One Get One Free deals
- **Web Dashboard**: Clean, responsive interface for all your shopping data

## Installation

1. Add this repository to your Home Assistant add-on store
2. Install the "Publix Shopping Tracker" add-on
3. Configure your email credentials (see Configuration below)
4. Start the add-on
5. Access the web interface

## Configuration

### Email Setup

You'll need to generate an **app-specific password** from your email provider (not your regular password):

#### Gmail
1. Go to [Google App Passwords](https://myaccount.google.com/apppasswords)
2. Enable 2-Step Verification first if not enabled
3. Generate a new app password for "Mail"
4. Copy the 16-character password

#### Outlook/Hotmail
1. Go to [Microsoft Security](https://account.microsoft.com/security)
2. Enable "Two-step verification"
3. Create an app password
4. Copy the generated password

#### Yahoo
1. Go to [Yahoo Account Security](https://login.yahoo.com/account/security)
2. Generate an app password
3. Copy the password

#### iCloud
1. Go to [Apple ID Settings](https://appleid.apple.com/)
2. Security → App-Specific Passwords
3. Generate a password
4. Copy it

#### AOL
1. Go to [AOL Account Security](https://login.aol.com/account/security)
2. Generate an app password
3. Copy the password

### Add-on Configuration

```yaml
email: your-email@gmail.com
email_provider: auto
app_password: your-app-specific-password
sync_hour: 5
```

- **email**: Your email address that receives Publix receipts
- **email_provider**: Email provider (auto-detect recommended)
  - Options: `auto`, `gmail`, `outlook`, `yahoo`, `icloud`, `aol`, `custom`
- **app_password**: App-specific password from your email provider
- **sync_hour**: Hour of day (0-23) to automatically sync receipts (default: 5 AM)

## Usage

After starting the add-on:

1. **Web Interface**: Click "OPEN WEB UI" or navigate to `http://homeassistant.local:8080`
2. **First Sync**: Go to Settings and verify your credentials, then click "Sync Receipts"
3. **View Data**: Explore your shopping trips, top items, and spending analytics

### Pages

- **Dashboard**: Overview with key statistics
- **Shopping Trips**: View items from each shopping trip
- **Top Items**: See your most frequently purchased items
- **Monthly View**: Month-by-month spending breakdown
- **Yearly View**: Annual spending analysis
- **Settings**: Configure email credentials

## Automatic Syncing

The add-on automatically syncs new receipts once per day at the configured hour (default: 5 AM). You can also manually sync anytime via the web interface.

## Data Storage

All data is stored in `/data/publix_tracker.db` within your Home Assistant configuration directory and persists across add-on restarts.

## Support

For issues or feature requests, please visit the GitHub repository.

## Changelog

### 1.0.0
- Initial release
- Multi-provider email support
- BOGO deal handling
- Shopping trips view
- Savings tracking
