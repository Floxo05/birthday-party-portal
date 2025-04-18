# 1. STAGE: Assets bauen
FROM node:20-slim AS frontend-build

WORKDIR /app

# Nur die JS-Dateien
COPY package.json package-lock.json ./
RUN npm ci

# Restliche Assets
COPY assets/ ./assets/
COPY webpack.config.js ./

# Encore Build
RUN npm run build

# 2. STAGE: PHP + Apache + Symfony
FROM php:8.3-apache AS app

RUN a2enmod rewrite

# PHP-Abhängigkeiten
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libicu-dev zlib1g-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/custom-php.ini /usr/local/etc/php/conf.d/zz-upload-limit.ini

# Apache für Symfony vorbereiten
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf \
 && echo '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Symfony-App kopieren
COPY . .

# Composer install (prod only)
RUN composer install --optimize-autoloader --no-interaction --ignore-platform-req=ext-http

# Assets aus erster Stage übernehmen
COPY --from=frontend-build /app/public/build public/build

# Berechtigungen & Start
RUN chmod -R 777 var
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
