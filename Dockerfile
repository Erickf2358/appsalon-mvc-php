FROM php:8.2-cli

# Install system deps
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Render uses $PORT
CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t public"]
