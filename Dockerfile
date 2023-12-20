FROM php:8.2-apache

RUN chown -R www-data:www-data /var/www/html

COPY app/ /var/www/html

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \