FROM php:rc-fpm

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN usermod -u 1000 www-data \
 && groupmod -g 1000 www-data \
 && chgrp -R staff /usr/local/etc/php-fpm.d/www.conf