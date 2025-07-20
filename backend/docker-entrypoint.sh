#!/bin/bash
set -e

echo "Starting Laravel application setup..."

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from example or environment variables..."
    if [ -f .env.example ]; then
        cp .env.example .env
    fi
fi

# Create storage directories
echo "Creating storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate app key if not set
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Generate JWT secret if not set
if ! grep -q "^JWT_SECRET=" .env || grep -q "^JWT_SECRET=$" .env; then
    echo "Generating JWT secret..."
    php artisan jwt:secret --force
fi

# Clear Laravel cache
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "Laravel application setup complete!"

# Start PHP-FPM
exec "$@"