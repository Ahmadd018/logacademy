FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite vhost_alias

RUN pecl install mailparse && docker-php-ext-enable mailparse

RUN apt-get install -y msmtp msmtp-mta

COPY php.ini /usr/local/etc/php/conf.d/custom.ini

RUN a2enmod headers

RUN mkdir -p /var/www/html/logacademy/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/logacademy/uploads

EXPOSE 80
