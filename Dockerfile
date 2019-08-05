FROM php:7.2
MAINTAINER Martin Hughes <martinph01@gmail.com>

RUN apt-get update \
    && apt-get install -y git zip libxml2-dev curl g++ make autoconf libmcrypt-dev libicu-dev zlib1g-dev libzip-dev cron vim runit build-essential python libglib2.0-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring intl zip xml \
    && docker-php-source extract \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete \
    && echo "xdebug.remote_autostart=true" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_mode=req" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=127.0.0.1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo "Europe/London" > /etc/timezone
RUN dpkg-reconfigure -f noninteractive tzdata

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

RUN mkdir /app

COPY . /app

WORKDIR /app
RUN composer global require hirak/prestissimo \
    && composer install --no-dev --no-progress --no-interaction --no-suggest --no-scripts

ENTRYPOINT ["/app/bot"]
