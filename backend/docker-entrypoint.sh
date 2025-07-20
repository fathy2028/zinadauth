#!/bin/bash
set -e

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from example or environment variables..."
    if [ -f .env.example ]; then
        cp .env.example .env
    fi
fi

# Create storage directories
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache

# Set proper permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate app key if not set
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear Laravel cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Run migrations if database is ready
if php artisan db:monitor 2>/dev/null; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# RUN jwt:secret if JWT_SECRET is not set
if grep -q "^JWT_SECRET=" .env; then
    echo "Generating JWT secret..."
    php artisan jwt:secret --force
fi

# Start PHP-FPM
exec "$@"