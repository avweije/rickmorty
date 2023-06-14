# Dockerfile
FROM php:8.2.4

RUN apt-get update -y && apt-get install -y libmcrypt-dev

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    acl \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc

RUN docker-php-ext-configure gd --with-jpeg --with-freetype 

RUN docker-php-ext-install \
    pdo pdo_mysql zip xsl gd intl opcache exif mbstring

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# RUN docker-php-ext-install pdo mbstring

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
# COPY ./vendor /app/vendor
COPY . /app

RUN composer install

# RUN composer require symfony/web-server-bundle

EXPOSE 8000
# CMD php bin/console server:run 0.0.0.0:8000
CMD php -S 0.0.0.0:8000 -t public/
# CMD symfony server:start --port=8000