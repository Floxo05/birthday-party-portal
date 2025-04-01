# Dockerfile
FROM php:8.3-cli

# System-Abhängigkeiten installieren
RUN apt-get update && apt-get install -y \
    git unzip mariadb-client libzip-dev zlib1g-dev libpng-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Composer installieren
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
