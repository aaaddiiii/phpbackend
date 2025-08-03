#!/bin/bash

echo "Starting PHP Real Estate Backend..."

# Print environment info (for debugging)
echo "Environment variables:"
echo "- DB_HOST: ${DB_HOST:-'Not set'}"
echo "- DB_NAME: ${DB_NAME:-'Not set'}"
echo "- DB_PORT: ${DB_PORT:-'Not set'}"
echo "- APP_DEBUG: ${APP_DEBUG:-'Not set'}"

# Create uploads directory if it doesn't exist
mkdir -p /var/www/html/uploads

# Set proper permissions
chown -R www-data:www-data /var/www/html/uploads
chmod -R 755 /var/www/html/uploads

echo "Starting Apache web server..."
exec apache2-foreground
