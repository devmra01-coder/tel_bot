 
FROM php:8.2-cli
 
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    && docker-php-ext-install xml mysqli \
    && docker-php-ext-enable mysqli
 
WORKDIR /app
 
COPY . /app
 
CMD php -S 0.0.0.0:${PORT:-8080} bot.php