# Use the PHP 8.2 FPM (FastCGI Process Manager) base image
FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    curl \
    openssl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    icu \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring pcntl bcmath intl zip \
    && echo "extension=intl.so" > /usr/local/etc/php/conf.d/docker-php-ext-intl.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . .

# Install all PHP dependencies
RUN composer install --no-dev --optimize-autoloader && composer clear-cache

# Change owner to www-data for all application files
RUN chown -R www-data:www-data .

# Expose port 8000 for Symfony's built-in server
EXPOSE 8000

# Run Symfony's built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]