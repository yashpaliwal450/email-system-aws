FROM php:8.1-apache

# Set environment variable for uid
ARG uid
ENV UID=${uid}

# Install necessary packages
RUN apt-get update && apt-get install -y \
    curl \
    g++ \
    git \
    libbz2-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libpng-dev \
    libreadline-dev \
    sudo \
    libzip-dev \
    unzip \
    zip \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-install mysqli pdo_mysql zip \
 && docker-php-ext-enable mysqli

RUN curl sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Apache configurations
RUN echo "ServerName laravel-app.local" >> /etc/apache2/apache2.conf \
 && a2enmod rewrite headers

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /app
COPY composer.json .
RUN composer install --no-scripts
COPY . .
# Composer
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

CMD php artisan migrate
CMD php artisan passport:install
CMD php artisan serve --host=0.0.0.0 --port 80


