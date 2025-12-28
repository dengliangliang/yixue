FROM php:7.4-fpm

# 设置工作目录
WORKDIR /var/www/yixue

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 配置并安装PHP扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    bcmath \
    zip \
    gd \
    intl \
    opcache \
    soap \
    sockets

# 安装Redis扩展
RUN pecl install redis-5.3.7 \
    && docker-php-ext-enable redis

# 复制PHP配置
COPY php.ini /usr/local/etc/php/
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# 设置权限
RUN chown -R www-data:www-data /var/www

# 暴露端口
EXPOSE 9000

CMD ["php-fpm"]
