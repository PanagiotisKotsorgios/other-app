FROM php:8.2-apache

# Install system deps + PHP extensions in ONE layer
# (libraries must be present during docker-php-ext-configure)
RUN apt-get update && apt-get install -y --no-install-recommends \
        # GD image support
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxpm-dev \
        # ZIP / XML / intl / mbstring
        libzip-dev \
        libxml2-dev \
        libonig-dev \
        libicu-dev \
        # Tools
        zip unzip git curl default-mysql-client \
    # Configure GD with all image formats
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    # Install all PHP extensions needed by the app + phpspreadsheet
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
        xml \
        simplexml \
        xmlreader \
        xmlwriter \
        gd \
        mbstring \
        opcache \
        intl \
        exif \
        fileinfo \
    # Enable Apache modules
    && a2enmod rewrite headers expires deflate \
    # Cleanup
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && echo "upload_max_filesize = 100M"      >> "$PHP_INI_DIR/php.ini" \
 && echo "post_max_size = 105M"            >> "$PHP_INI_DIR/php.ini" \
 && echo "max_execution_time = 300"        >> "$PHP_INI_DIR/php.ini" \
 && echo "max_input_time = 300"            >> "$PHP_INI_DIR/php.ini" \
 && echo "memory_limit = 512M"             >> "$PHP_INI_DIR/php.ini" \
 && echo "date.timezone = Europe/Athens"   >> "$PHP_INI_DIR/php.ini"

# Apache VirtualHost
COPY deploy/apache-callcenter.conf /etc/apache2/sites-available/callcenter.conf
RUN a2dissite 000-default && a2ensite callcenter

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source (vendor is in .dockerignore — installed fresh below)
COPY --chown=www-data:www-data . .

# Install Composer deps (no --quiet so errors are visible)
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-scripts

# Permissions
RUN chown -R www-data:www-data /var/www/html \
 && find /var/www/html -type f -exec chmod 644 {} \; \
 && find /var/www/html -type d -exec chmod 755 {} \; \
 && chmod -R 775 /var/www/html/public/assets/uploads \
 && chmod -R 775 /var/www/html/public/assets/templates

# Startup script
COPY deploy/docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
