#!/bin/bash

# شروع MySQL
service mysql start

# ساخت دیتابیس اولیه (اختیاری)
mysql -e "CREATE DATABASE IF NOT EXISTS mydatabase;"

# اجرای PHP server
php -S 0.0.0.0:8080 bot.php
