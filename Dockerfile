FROM php:8.2-fpm

# Gunakan Debian apt-get (lebih stabil & cepat dari Alpine untuk kompilasi C++)
RUN apt-get update && apt-get install -y \
    bash \
    git \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions menggunakan mlocati
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
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