# Imagen base oficial de PHP con FPM
FROM php:8.2-fpm

# Instala dependencias del sistema necesarias, incluyendo las libs para GD
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    postgresql-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
       intl \
       pdo_pgsql \
       zip \
       gd \
       opcache \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Copia Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura Opcache para un rendimiento óptimo
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.enable_cli=1"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.interned_strings_buffer=8"; \
      echo "opcache.max_accelerated_files=10000"; \
      echo "opcache.validate_timestamps=1"; \
      echo "opcache.revalidate_freq=2"; \
    } > /usr/local/etc/php/conf.d/docker-php-opcache.ini

# Establece el directorio de trabajo
WORKDIR /var/www/app
