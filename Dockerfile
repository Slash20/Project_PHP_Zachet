FROM php:8.2-fpm

# Set environment (prod/dev)
ENV APP_ENV=prod \
    APP_DEBUG=0

# Install system deps & PHP extensions
RUN apt-get update \
    && apt-get install -y \
       git unzip libpq-dev libicu-dev nginx \
    && docker-php-ext-install intl pdo pdo_pgsql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/html

# Copy composer and install deps
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && composer dump-autoload --optimize

# Clear & warmup cache
RUN php bin/console cache:clear --env=prod --no-debug \
    && php bin/console cache:warmup --env=prod --no-debug

# Fix permissions
RUN chown -R www-data:www-data var public

# Copy nginx config
COPY docker/nginx/symfony.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["php-fpm"]
