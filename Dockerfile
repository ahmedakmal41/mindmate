FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies if composer.json exists
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Expose port
EXPOSE 8080

# Start PHP built-in server
CMD php -S 0.0.0.0:${PORT:-8080} -t .
