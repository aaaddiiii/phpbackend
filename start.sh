#!/bin/bash

# Wait for database to be ready
echo "Waiting for database to be ready..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done

echo "Database is ready!"

# Run database migrations/setup if needed
# You can add database setup commands here

# Start Apache
exec apache2-foreground
