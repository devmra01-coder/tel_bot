# استفاده از نسخه رسمی PHP
FROM php:8.2-cli

# نصب ابزارهای مورد نیاز (اگر نیاز به extensions خاصی داری اینجا اضافه کن)
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    && docker-php-ext-install xml

# تعیین پوشه کاری داخل سرور
WORKDIR /app

# کپی کردن تمام فایل‌های پروژه به داخل سرور
COPY . /app

# اجرای دستور برای بالا آوردن سرور PHP روی پورت مشخص شده توسط Render
CMD php -S 0.0.0.0:${PORT} bot.php
