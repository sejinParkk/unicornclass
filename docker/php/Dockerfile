FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 파일 업로드 크기 설정 (썸네일 5MB, 강의자료 50MB)
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# storage 디렉토리 생성 및 www-data 쓰기 권한 부여
RUN mkdir -p /var/www/html/storage/uploads/class \
             /var/www/html/storage/uploads/instructor \
             /var/www/html/storage/uploads/materials \
             /var/www/html/storage/uploads/site \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

WORKDIR /var/www/html
