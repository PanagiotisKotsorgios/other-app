FROM php:8.2-apache

# Install system packages and PHP extensions in one clean layer
# Notes:
#  - libjpeg-dev (not libjpeg62-turbo-dev) works across all Debian versions
#  - simplexml/xml/xmlreader/xmlwriter/fileinfo/pdo are ALREADY built-in, don't install
#  - docker-php-ext-install only for extensions NOT in the base image
RUN set -eux \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
      libfreetype6-dev \
      libjpeg-dev \
      libpng-dev \
      libwebp-dev \
      libzip-dev \
      libxml2-dev \
      libonig-dev \
      libicu-dev \
      zip unzip git curl default-mysql-client \
 && docker-php-ext-configure gd \
      --with-freetype \
      --with-jpeg \
      --with-webp \
 && docker-php-ext-install -j"$(nproc)" \
      pdo_mysql \
      mysqli \
      gd \
      mbstring \
      opcache \
      intl \
      zip \
      exif \
      bcmath \
 && a2enmod rewrite headers expires deflate \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && { \
      echo "upload_max_filesize = 100M"; \
      echo "post_max_size = 105M"; \
      echo "max_execution_time = 300"; \
      echo "max_input_time = 300"; \
      echo "memory_limit = 512M"; \
      echo "date.timezone = Europe/Athens"; \
      echo "variables_order = EGPCS"; \
    } >> "$PHP_INI_DIR/php.ini"

# Apache VirtualHost
COPY deploy/apache-callcenter.conf /etc/apache2/sites-available/callcenter.conf
RUN a2dissite 000-default && a2ensite callcenter

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source (vendor excluded via .dockerignore)
COPY --chown=www-data:www-data . .

# Install dependencies
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
      --no-dev \
      --optimize-autoloader \
      --no-interaction \
      --no-scripts

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
 && find /var/www/html -type d -exec chmod 755 {} \; \
 && find /var/www/html -type f -exec chmod 644 {} \; \
 && chmod -R 775 /var/www/html/public/assets/uploads \
 && chmod -R 775 /var/www/html/public/assets/templates

COPY deploy/docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
