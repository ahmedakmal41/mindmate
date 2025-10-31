FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libssl-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Set working directory
WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Copy application files
COPY . .

# Expose port
EXPOSE 8080

# Start PHP built-in server
CMD php -S 0.0.0.0:${PORT:-8080} -t .
