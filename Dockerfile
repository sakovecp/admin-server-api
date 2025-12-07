FROM php:8.4-fpm

# Встановлення пакетів
RUN apt-get update && apt-get install -y \
    nginx zip unzip sqlite3 libsqlite3-dev sudo \
    && docker-php-ext-install pdo pdo_sqlite

RUN echo "www-data ALL=NOPASSWD: /usr/sbin/nginx" >> /etc/sudoers

# Копіюємо composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
EXPOSE 8080

# Запуск php-fpm та nginx без Supervisor
CMD php-fpm & nginx -g "daemon off;"
