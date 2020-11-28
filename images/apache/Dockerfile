FROM httpd

COPY httpd.conf /usr/local/apache2/conf/httpd.conf

RUN usermod -u 1000 www-data \
 && groupmod -g 1000 www-data \
 && chgrp -R www-data /usr/local/apache2