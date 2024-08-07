FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libicu-dev \
    libzip-dev \
    nano \
    libjpeg62-turbo-dev \
    xdg-utils \
    supervisor  # Adding supervisor

# Configure & install gd extension with jpeg support
RUN docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# Clear installation sources to reduce image size
RUN docker-php-source delete

# Install Xdebug and configure
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Set PHPStan Pro port and expose it
ENV PHPSTAN_PRO_WEB_PORT=11111
EXPOSE 11111

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Create system user
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Set working directory
WORKDIR /var/www

# Copy supervisor config file
ADD supervisor/supervisor.conf /etc/supervisor/conf.d/

# Command to run supervisord
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]