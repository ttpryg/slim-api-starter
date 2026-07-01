FROM php:8.2-fpm-alpine

# Install essential tools
RUN apk add --no-cache bash git unzip zip

# Install PHP extensions instantly using mlocati's installer
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
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