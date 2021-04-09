FROM php:8-apache-buster

RUN mkdir /var/www/var
COPY henson.sql /var/www/var/
RUN apt-get update && apt-get install -y sqlite3
RUN sqlite3 /var/www/var/henson.sqlite < /var/www/var/henson.sql && chown -Rf www-data:www-data /var/www/var/

RUN a2enmod rewrite
