FROM php:8.2-cli

# نصب وابستگی‌ها + Zip Extension
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libxml2-dev \
    && docker-php-ext-install zip xml mysqli \
    && docker-php-ext-enable zip mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app

# پورت پیش‌فرض
EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} index.php
