FROM php:8.2-cli

# نصب ابزارها + MySQL server
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    default-mysql-server \
    && docker-php-ext-install xml mysqli \
    && docker-php-ext-enable mysqli

# تنظیم پوشه کاری
WORKDIR /app

# کپی پروژه
COPY . /app

# اسکریپت شروع (هم DB هم PHP)
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080 3306

CMD ["/start.sh"]
