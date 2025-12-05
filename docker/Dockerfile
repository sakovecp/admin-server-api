FROM php:8.4-fpm

# Встановлюємо необхідні пакети + docker CLI
RUN apt-get update && apt-get install -y \
    zip unzip sqlite3 libsqlite3-dev \
    curl gnupg lsb-release apt-transport-https \
    && curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg \
    && echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" > /etc/apt/sources.list.d/docker.list \
    && apt-get update && apt-get install -y docker-ce-cli \
    && docker-php-ext-install pdo pdo_sqlite

# Копіюємо composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
