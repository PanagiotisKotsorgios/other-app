FROM php:8.2-apache

# Install system deps AND PHP extensions in one layer so libraries are present during configure
RUN apt-get update && apt-get install -y --no-install-recommends \
        # GD dependencies
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxpm-dev \
        # zip / xml / intl / mbstring deps
        libzip-dev \
        libxml2-dev \
        libonig-dev \
        libicu-dev \
        # misc tools
        zip unzip git curl default-mysql-client \
    # Configure GD with all image format support
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    # Install all required PHP extensions
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
        xml \
        gd \
        mbstring \
        opcache \
        intl \
        exif \
    # Enable Apache modules
    && a2enmod rewrite headers expires deflate \
    # Cleanup to keep image small
    && apt-get purge -y --auto-remove \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev \
        libxpm-dev libzip-dev libxml2-dev libonig-dev libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && echo "upload_max_filesize = 100M"  >> "$PHP_INI_DIR/php.ini" \
 && echo "post_max_size = 105M"        >> "$PHP_INI_DIR/php.ini" \
 && echo "max_execution_time = 300"    >> "$PHP_INI_DIR/php.ini" \
 && echo "max_input_time = 300"        >> "$PHP_INI_DIR/php.ini" \
 && echo "memory_limit = 512M"         >> "$PHP_INI_DIR/php.ini" \
 && echo "date.timezone = Europe/Athens" >> "$PHP_INI_DIR/php.ini"

# OPcache tuning for production
RUN echo "opcache.enable=1"               >> "$PHP_INI_DIR/conf.d/opcache.ini" \
 && echo "opcache.memory_consumption=128" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
 && echo "opcache.max_accelerated_files=10000" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
 && echo "opcache.validate_timestamps=0"  >> "$PHP_INI_DIR/conf.d/opcache.ini"

# Apache VirtualHost
COPY deploy/apache-callcenter.conf /etc/apache2/sites-available/callcenter.conf
RUN a2dissite 000-default && a2ensite callcenter

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app (vendor excluded — installed fresh below)
COPY --chown=www-data:www-data . .

# Install Composer deps
RUN composer install --no-dev --optimize-autoloader --no-interaction --quiet

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
