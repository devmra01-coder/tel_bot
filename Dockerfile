FROM php:8.2-cli

# نصب کتابخانه‌های مورد نیاز برای PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql

WORKDIR /app

COPY . /app

CMD php -S 0.0.0.0:${PORT:-8080} bot.php
