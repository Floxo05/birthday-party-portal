FROM php:8.3-fpm

# Systemabhängigkeiten installieren
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libicu-dev zlib1g-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis setzen
WORKDIR /var/www

# App-Dateien kopieren
COPY . .

# Abhängigkeiten installieren
RUN composer install -n -o

# Cache/Logs beschreibbar machen
RUN chmod -R 777 var

CMD ["php-fpm"]
