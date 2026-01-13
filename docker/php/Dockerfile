FROM php:8.4.1-fpm
COPY php.ini /usr/local/etc/php/
COPY docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY .bashrc /root/
COPY fonts/ /usr/share/fonts/

RUN apt-get update \
    && apt-get install -y \
    zlib1g-dev git zip unzip libzip-dev ghostscript \
    vim libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev \
    libpng-dev libfontconfig1 libxrender1 \
    default-mysql-client libmariadb-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd zip bcmath pdo_mysql mysqli exif

# libreoffice
RUN apt-get install -y libreoffice libreoffice-l10n-ja

# font
#RUN apt-get install -y fonts-ipafont
#RUN fc-cache -fv
#COPY fonts.conf /etc/fonts/local.conf

RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs
RUN npm install npm@latest -g

WORKDIR /root
RUN rm -rf /root/dircolors-solarized
RUN git clone https://github.com/seebi/dircolors-solarized

#composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

ENV COMPOSER_HOME /composer

ENV PATH $PATH:/composer/vendor/bin

WORKDIR /var/www

RUN composer global require "laravel/installer"
