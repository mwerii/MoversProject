FROM php:8.2-apache

RUN a2enmod rewrite

# MySQL support
RUN docker-php-ext-install mysqli pdo_mysql

# PostgreSQL support
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

COPY ./movers/ /var/www/html/
WORKDIR /var/www/html
EXPOSE 80
