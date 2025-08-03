#!/bin/bash

echo "Installing Composer dependencies for Real Estate API..."
echo

# Check if composer.phar exists
if [ ! -f composer.phar ]; then
    echo "Downloading Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    echo
fi

# Install dependencies
echo "Installing dependencies..."
php composer.phar install --no-dev --optimize-autoloader

echo
echo "Dependencies installed successfully!"
echo
echo "Next steps:"
echo "1. Configure your .env file with database and API credentials"
echo "2. Run: php setup/database.php"
echo "3. Visit: http://localhost/phpbackendPMS/docs.php for API documentation"
echo "4. Visit: http://localhost/phpbackendPMS/test.php to test the API"
echo
