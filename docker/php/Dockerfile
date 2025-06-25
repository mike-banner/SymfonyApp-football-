FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip libonig-dev libpng-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    zip \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    curl \
    gnupg \
    ca-certificates

# Install PHP extensions
RUN docker-php-ext-install intl pdo pdo_mysql zip opcache

# Install Node.js (via NodeSource) + npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Symfony working dir
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html

ARG UID=1000
ARG GID=1000

RUN groupmod -g $GID www-data \
 && usermod -u $UID -g www-data www-data \
 && chown -R www-data:www-data /var/www/html


CMD ["php-fpm"]

# Symfony dir
WORKDIR /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html

CMD ["php-fpm"]
