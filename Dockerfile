FROM php:8.3-apache

# Apache Rewrite-Modul aktivieren
RUN a2enmod rewrite

# Systemabhängigkeiten und PHP-Erweiterungen installieren
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libicu-dev zlib1g-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache Konfiguration für Symfony
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf \
 && echo '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Arbeitsverzeichnis setzen
WORKDIR /var/www/html

# App-Dateien kopieren
COPY . .

# Symfony Abhängigkeiten installieren
RUN composer install --no-interaction --optimize-autoloader

# Schreibrechte für var/
RUN chmod -R 777 var

# Port freigeben (Apache nutzt Port 80 standardmäßig)
EXPOSE 80
