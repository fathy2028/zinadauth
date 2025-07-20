FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring intl exif bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy and fix entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Fix line endings and set permissions
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

# Create a backup entrypoint script in case the copied one fails
RUN echo '#!/bin/bash' > /usr/local/bin/entrypoint-backup.sh && \
    echo 'set -e' >> /usr/local/bin/entrypoint-backup.sh && \
    echo 'mkdir -p storage/framework/{sessions,views,cache}' >> /usr/local/bin/entrypoint-backup.sh && \
    echo 'mkdir -p bootstrap/cache' >> /usr/local/bin/entrypoint-backup.sh && \
    echo 'chown -R www-data:www-data storage bootstrap/cache' >> /usr/local/bin/entrypoint-backup.sh && \
    echo 'chmod -R 775 storage bootstrap/cache' >> /usr/local/bin/entrypoint-backup.sh && \
    echo 'php artisan config:clear' >> /usr/local/bin/entrypoint-backup.sh && \
    echo 'exec "$@"' >> /usr/local/bin/entrypoint-backup.sh && \
    chmod +x /usr/local/bin/entrypoint-backup.sh

# Set proper permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 9000

# Use the main entrypoint, fallback to backup if it fails
ENTRYPOINT ["/bin/bash", "-c", "if [ -x /usr/local/bin/docker-entrypoint.sh ]; then /usr/local/bin/docker-entrypoint.sh \"$@\"; else /usr/local/bin/entrypoint-backup.sh \"$@\"; fi", "--"]
CMD ["php-fpm"]
