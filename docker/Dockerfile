FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install system packages and PHP extensions required by Laravel.
RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-libs \
        libzip \
        sqlite \
        libpq \
    && apk add --no-cache --virtual .build-deps \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite-dev \
        postgresql-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pdo \
        pdo_sqlite \
        pdo_pgsql \
        zip \
    && apk del .build-deps

# Copy the entrypoint script.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
