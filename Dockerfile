ARG BUILD_FROM
FROM $BUILD_FROM

# Set shell
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# Install Apache, PHP, Python, and dependencies
RUN apk add --no-cache \
    apache2 \
    apache2-ssl \
    php82 \
    php82-apache2 \
    php82-pdo \
    php82-pdo_sqlite \
    php82-sqlite3 \
    php82-session \
    python3 \
    py3-pip \
    sqlite \
    dcron \
    bash

# Install Python packages
RUN pip3 install --break-system-packages --no-cache-dir imaplib2

# Create directories
RUN mkdir -p /app /app/data /app/data/receipts /run/apache2 /var/www/localhost/htdocs

# Copy application files
COPY GetReciepts.py ViewDatabase.py /app/
COPY web/ /var/www/localhost/htdocs/
COPY run.sh /

# Create symlink for data directory
RUN rm -rf /var/www/localhost/htdocs/data && \
    ln -s /app/data /var/www/localhost/htdocs/data

# Set permissions
RUN chown -R apache:apache /var/www/localhost/htdocs /app/data && \
    chmod -R 755 /var/www/localhost/htdocs && \
    chmod -R 775 /app/data && \
    chmod +x /run.sh

# Configure Apache
RUN sed -i 's/#LoadModule rewrite_module/LoadModule rewrite_module/' /etc/apache2/httpd.conf && \
    sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/httpd.conf && \
    sed -i 's/ServerTokens OS/ServerTokens Prod/' /etc/apache2/httpd.conf && \
    sed -i 's/ServerSignature On/ServerSignature Off/' /etc/apache2/httpd.conf && \
    sed -i 's/Listen 80/Listen 8080/' /etc/apache2/httpd.conf && \
    echo "DirectoryIndex index.php index.html" >> /etc/apache2/httpd.conf && \
    mkdir -p /var/log/apache2 /run/apache2

# Setup cron
RUN echo "0 5 * * * cd /app && python3 GetReciepts.py >> /share/publix-tracker/cron.log 2>&1" > /etc/crontabs/root

WORKDIR /var/www/localhost/htdocs

# Expose port
EXPOSE 8080

# Remove healthcheck - let Home Assistant handle it
# HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
#     CMD wget --no-verbose --tries=1 --spider http://localhost:80/ || exit 1

# Run
CMD ["/run.sh"]
