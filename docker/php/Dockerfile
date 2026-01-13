FROM php:8.2-fpm

# 1. Install system dependencies required for Laravel and Extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# 2. Clear cache to keep the image small
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install PHP extensions (pdo_mysql is required for the DB)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 4. Install Composer (The PHP Package Manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Set the working directory inside the container
WORKDIR /var/www

# 6. Default command: Start PHP-FPM
CMD ["php-fpm"]