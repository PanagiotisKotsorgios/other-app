FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev libxml2-dev libpng-dev libonig-dev \
    libfreetype6-dev libjpeg62-turbo-dev libwebp-dev \
    zip unzip git curl default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install pdo pdo_mysql mysqli zip xml gd mbstring opcache intl

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# PHP tuning
RUN echo "upload_max_filesize=100M\n\
post_max_size=105M\n\
max_execution_time=300\n\
max_input_time=300\n\
memory_limit=512M\n\
date.timezone=Europe/Athens" >> "$PHP_INI_DIR/php.ini"

# Apache VirtualHost
COPY deploy/apache-callcenter.conf /etc/apache2/sites-available/callcenter.conf
RUN a2dissite 000-default && a2ensite callcenter

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy app files (vendor excluded — installed below)
COPY --chown=www-data:www-data . .

# Install composer deps
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && chmod -R 775 /var/www/html/public/assets/uploads \
 && chmod -R 775 /var/www/html/public/assets/templates \
 && chmod 640 /var/www/html/.env 2>/dev/null || true

# Startup script
COPY deploy/docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
