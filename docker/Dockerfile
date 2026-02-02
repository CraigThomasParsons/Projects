FROM php:8.4-fpm-alpine

# Install system dependencies needed for Laravel and SQLite.
RUN apk add --no-cache \
        curl \
        git \
        icu-dev \
        libzip-dev \
        sqlite \
        sqlite-dev \
        unzip \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_sqlite \
        zip

# Install Composer for dependency management.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

CMD ["php-fpm", "-F"]
