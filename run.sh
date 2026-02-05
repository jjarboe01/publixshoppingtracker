#!/usr/bin/with-contenv bashio

# ==============================================================================
# Home Assistant Add-on: Publix Shopping Tracker
# Runs the Publix Shopping Tracker web application
# ==============================================================================

bashio::log.info "Starting Publix Shopping Tracker..."

# Create data directory
mkdir -p /app/data
chmod 775 /app/data

# Get configuration from Home Assistant
EMAIL=$(bashio::config 'email')
EMAIL_PROVIDER=$(bashio::config 'email_provider')
APP_PASSWORD=$(bashio::config 'app_password')
SYNC_HOUR=$(bashio::config 'sync_hour')

# Create config.php if credentials are provided
if [ -n "$EMAIL" ] && [ -n "$APP_PASSWORD" ]; then
    bashio::log.info "Creating configuration file..."
    cat > /app/data/config.php << EOF
<?php
// Email Configuration
// Generated: $(date '+%Y-%m-%d %H:%M:%S')

define('GMAIL_EMAIL', '${EMAIL}');
define('GMAIL_PASSWORD', '${APP_PASSWORD}');
define('EMAIL_PROVIDER', '${EMAIL_PROVIDER}');
EOF
    chmod 644 /app/data/config.php
    bashio::log.info "Configuration saved successfully"
else
    bashio::log.warning "No email credentials configured. Please set up via the web interface."
fi

# Update cron schedule
bashio::log.info "Setting up daily sync at ${SYNC_HOUR}:00..."
echo "${SYNC_HOUR} 5 * * * cd /app && python3 GetReciepts.py >> /app/data/cron.log 2>&1" > /etc/cron.d/publix-sync
chmod 0644 /etc/cron.d/publix-sync
crontab /etc/cron.d/publix-sync

# Start cron
bashio::log.info "Starting cron service..."
service cron start

# Set permissions
chown -R www-data:www-data /app/data
chmod -R 775 /app/data

bashio::log.info "Starting Apache web server..."

# Start Apache in foreground
exec apache2-foreground
