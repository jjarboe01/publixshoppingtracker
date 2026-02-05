#!/usr/bin/with-contenv bashio

# ==============================================================================
# Home Assistant Add-on: Publix Shopping Tracker
# Runs the Publix Shopping Tracker web application
# ==============================================================================

bashio::log.info "Starting Publix Shopping Tracker..."

# Create persistent data directory in /share
mkdir -p /share/publix-tracker/receipts
chown -R apache:apache /share/publix-tracker
chmod -R 775 /share/publix-tracker

# Remove old directories if they exist and create symlinks to persistent storage
rm -rf /app/data
ln -sf /share/publix-tracker /app/data

# Ensure the web data symlink points to the right place
rm -rf /var/www/localhost/htdocs/data
ln -sf /share/publix-tracker /var/www/localhost/htdocs/data

bashio::log.info "Data directory symlinks created"

# Get configuration from Home Assistant
EMAIL=$(bashio::config 'email')
EMAIL_PROVIDER=$(bashio::config 'email_provider')
APP_PASSWORD=$(bashio::config 'app_password')
SYNC_HOUR=$(bashio::config 'sync_hour')

# Debug: Log configuration values (without password)
bashio::log.info "Configuration loaded:"
bashio::log.info "  Email: ${EMAIL:-<not set>}"
bashio::log.info "  Provider: ${EMAIL_PROVIDER:-<not set>}"
bashio::log.info "  Password: ${APP_PASSWORD:+<set>}${APP_PASSWORD:-<not set>}"
bashio::log.info "  Sync Hour: ${SYNC_HOUR:-<not set>}"

# Create config.php if credentials are provided
if [ -n "$EMAIL" ] && [ -n "$APP_PASSWORD" ]; then
    bashio::log.info "Creating configuration file..."
    cat > /share/publix-tracker/config.php << EOF
<?php
// Email Configuration
// Generated: $(date '+%Y-%m-%d %H:%M:%S')

define('GMAIL_EMAIL', '${EMAIL}');
define('GMAIL_PASSWORD', '${APP_PASSWORD}');
define('EMAIL_PROVIDER', '${EMAIL_PROVIDER}');
EOF
    chmod 644 /share/publix-tracker/config.php
    bashio::log.info "Configuration saved successfully"
else
    bashio::log.warning "No email credentials configured. Please set up via the web interface."
fi

# Update cron schedule
bashio::log.info "Setting up daily sync at ${SYNC_HOUR}:00..."
echo "0 ${SYNC_HOUR} * * * cd /app && python3 GetReciepts.py >> /share/publix-tracker/cron.log 2>&1" > /etc/crontabs/root
chmod 0644 /etc/crontabs/root

# Start cron
bashio::log.info "Starting cron service..."
crond -b

# Set permissions
chown -R apache:apache /app/data
chmod -R 775 /app/data

bashio::log.info "Starting Apache web server..."

# Make sure Apache can write logs
mkdir -p /var/log/apache2
chown apache:apache /var/log/apache2

# Start Apache in foreground with proper settings
exec httpd -DFOREGROUND -e info
