FROM syncxplus/php:7.4.24-apache-buster

LABEL maintainer=jibo@outlook.com

COPY . /var/www/

RUN chown -R www-data:www-data /var/www/