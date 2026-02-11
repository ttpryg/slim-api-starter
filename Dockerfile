FROM php:8.2-fpm-alpine

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
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json ./

# Install dependencies
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --verbose

# Copy the rest of the application
COPY . .

RUN chown -R www-data:www-data /var/www/html
