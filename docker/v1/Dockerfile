FROM php:8.4-fpm

# Встановлення пакетів
RUN apt-get update && apt-get install -y \
    nginx zip unzip sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Копіюємо composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
EXPOSE 8000

CMD bash -c "service nginx start && php artisan serve --host=0.0.0.0 --port=8000"

