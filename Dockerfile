FROM php:8.4-fpm

# Встановлюємо необхідні пакети + docker CLI
RUN apt-get update && apt-get install -y \
    zip unzip sqlite3 libsqlite3-dev docker.io \
    && docker-php-ext-install pdo pdo_sqlite \
    && usermod -aG docker www-data

# Копіюємо composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
