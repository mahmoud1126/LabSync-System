FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

# Install zip + unzip (needed by Composer to extract dependencies fast)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip git \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (copied straight from the official Composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
