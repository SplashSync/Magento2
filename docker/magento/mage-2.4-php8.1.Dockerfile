FROM php:8.1-apache

LABEL description="Magento 2.4 with PHP 8.1 for SplashSync CI"

################################################################################
# Declare Env Variables
################################################################################
ENV MAGENTO_VERSION         2.4.5
ENV INSTALL_DIR             /var/www/html
ENV MODULE_DIR              /builds/SplashSync/Magento2
ENV COMPOSER_HOME           /var/www/.composer/

################################################################################
# Install Libs
################################################################################
RUN requirements="cron git libpng++-dev libzip-dev libmcrypt-dev libmcrypt4 libcurl3-dev libfreetype6 libjpeg62-turbo-dev libfreetype6-dev libicu-dev libxslt1-dev libonig-dev unzip" \
    && apt-get update \
    && apt-get install -y $requirements \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    && docker-php-ext-install xsl \
    && docker-php-ext-install soap \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install sockets

RUN yes '' | pecl install mcrypt-1.0.5 \
    && echo 'extension=mcrypt.so' > /usr/local/etc/php/conf.d/mcrypt.ini

################################################################################
# Configure PHP & Apache
################################################################################
RUN a2enmod rewrite
RUN echo "memory_limit=2048M" > /usr/local/etc/php/conf.d/memory-limit.ini

RUN chsh -s /bin/bash www-data

################################################################################
# COPY Configs, Scripts, Etc...
################################################################################
COPY ./scripts/ /usr/local/bin
COPY ./conf/auth.json $COMPOSER_HOME
COPY ./conf/crontab /etc/cron.d/magento2-cron
COPY ./module/composer.json /builds/SplashSync/Magento2/composer.json
RUN chmod +x /usr/local/bin/*.sh

################################################################################
# Run Install Scripts
################################################################################
RUN install-composer2.sh
RUN install-phpunit.sh
RUN install-crontab.sh
RUN create-magento.sh

################################################################################
# Clean
################################################################################
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR     $INSTALL_DIR
