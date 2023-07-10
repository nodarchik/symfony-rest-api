# Use the PHP 8.2 FPM (FastCGI Process Manager) base image
FROM php:8.2-fpm-alpine

# Install dependencies
# These include libraries that PHP extensions and Symfony need
RUN apk add --no-cache \
    curl \
    openssl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    icu-dev 

# Install PHP extensions
# These extensions are necessary for Symfony and common PHP tasks
RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring pcntl bcmath intl zip

# Get latest Composer
# Composer is a tool for dependency management in PHP.
# It allows you to declare the libraries your project depends on, and it will manage (install/update) them for you.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . .

# Install all PHP dependencies
# The --no-dev flag disables installation of packages listed in require-dev.
# The --optimize-autoloader flag convert PSR-0/4 autoloading to classmap to get a faster autoloader.
# composer clear-cache clears composer's internal package cache.
RUN composer install --no-dev --optimize-autoloader && composer clear-cache

# Change owner to www-data for all application files
RUN chown -R www-data:www-data .

# Expose port 8000 for Symfony's built-in server
EXPOSE 8000

# Run Symfony's built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
