FROM php:8.2-apache

# mysqli is required by config/database.php
RUN docker-php-ext-install mysqli

# Sensible upload limits for menu item images
RUN { \
    echo 'upload_max_filesize = 5M'; \
    echo 'post_max_size = 6M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs \
    && chmod -R 755 /var/www/html/uploads /var/www/html/logs

# Railway/Render inject the port to listen on via $PORT; Apache's default
# image is hardcoded to 80, so rewrite it at container start.
CMD sh -c "sed -i \"s/80/\${PORT:-80}/g\" /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf && apache2-foreground"
