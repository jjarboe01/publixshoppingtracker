# Publix Shopping Tracker

A Home Assistant add-on (or standalone Docker application) that automatically retrieves and tracks your Publix shopping receipts from email, providing detailed analytics and insights into your shopping habits.

## Features

- 📧 **Automatic Email Sync**: Retrieves receipts from Gmail, Outlook, Yahoo, iCloud, or AOL
- 📊 **Shopping Analytics**: View spending trends, top items, and shopping patterns
- 💰 **Savings Tracking**: Track promotions, sales, and BOGO deals
- 🛒 **Trip History**: See items from each shopping trip
- 📅 **Monthly/Yearly Views**: Analyze spending over time
- 🔒 **Secure**: App passwords only, data stored locally

## Installation

### As a Home Assistant Add-on

1. Add this repository to your Home Assistant add-on store:
   - Navigate to **Supervisor** → **Add-on Store** → **⋮** (menu) → **Repositories**
   - Add: `https://github.com/yourusername/homeassistant-publix-tracker`

2. Install the "Publix Shopping Tracker" add-on

3. Configure your email credentials in the add-on configuration

4. Start the add-on and access the web interface

### As a Standalone Docker Container

```bash
docker compose up -d
```

Access the web interface at `http://localhost:8080`

## Configuration (Home Assistant)

```yaml
email: your-email@gmail.com
email_provider: auto
app_password: your-app-specific-password
sync_hour: 5
```

See [DOCS.md](DOCS.md) for detailed configuration instructions and how to generate app passwords.
