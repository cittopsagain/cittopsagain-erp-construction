FROM php:8.2-fpm-alpine

# Install common extensions
RUN docker-php-ext-install pdo pdo_mysql

# Create the Logs directory and set permissions
RUN mkdir -p /var/www/html/Logs && \
    chown -R www-data:www-data /var/www/html/Logs && \
    chmod -R 777 /var/www/html/Logs

WORKDIR /var/www/html
