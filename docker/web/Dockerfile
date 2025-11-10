FROM php:8.3-apache

# --------- [追加] vim, lessなどCLIツールをインストール ---------
RUN apt-get update -qq && \
    apt-get install -y \
    git \
    unzip \
    zip \
    vim \
    less \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    && docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/ \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        zip \
        gd \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && echo -e "pcov.enabled=1\npcov.directory=/var/www/html/app\npcov.exclude='~(vendor|tests)~'" > /usr/local/etc/php/conf.d/pcov.ini \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apacheの設定ファイルをコピー
COPY ./docker/web/000-default.conf /etc/apache2/sites-available/000-default.conf

# mod_rewrite有効化
RUN a2enmod rewrite

# タイムゾーン設定
RUN echo "date.timezone=Asia/Tokyo" > /usr/local/etc/php/conf.d/timezone.ini

# Composerインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www/html

# 起動時の権限調整スクリプト
COPY docker/web/fix-perms.sh /usr/local/bin/fix-perms.sh
RUN chmod +x /usr/local/bin/fix-perms.sh

# 起動時に毎回 fix → Apache
CMD ["/usr/local/bin/fix-perms.sh"]