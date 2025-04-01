FROM php:8.3-cli

# Basis-Abhängigkeiten
RUN apt-get update && apt-get install -y \
    git unzip mariadb-client libzip-dev zlib1g-dev libpng-dev libonig-dev bc \
    && docker-php-ext-install pdo pdo_mysql zip

# Xdebug installieren
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Coverage aktivieren
ENV XDEBUG_MODE=coverage
ENV XDEBUG_CONFIG=""

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
