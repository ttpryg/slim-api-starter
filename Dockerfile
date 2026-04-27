FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    unzip \
    zip

# Install PHP extensions
RUN docker-php-ext-install \
    intl \
    opcache \
    pdo_mysql \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first (biar cache optimal)
COPY composer.json composer.lock ./

# Install dependencies (vendor dibuat di image, aman dari permission issue)
RUN composer install \
    --no-interaction \
    --no-scripts \
    --prefer-dist

# Copy seluruh source code
COPY . .

# Fix permission hanya untuk folder yang butuh write
RUN mkdir -p storage/logs \
    && chown -R www-data:www-data storage \
    && chmod -R 775 storage

# Gunakan user non-root (lebih aman)
USER www-data

EXPOSE 9000

CMD ["php-fpm"]