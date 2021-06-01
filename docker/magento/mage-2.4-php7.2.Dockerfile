#FROM quay.io/alexcheng1982/apache2-php7:7.3.12
FROM php:7.4-apache

LABEL description="Magento 2.4 with PHP 7.4 for SplashSync CI"

################################################################################
# Declare Env Variables
################################################################################
ENV MAGENTO_VERSION         2.4.2
ENV INSTALL_DIR             /var/www/html
ENV MODULE_DIR              /builds/SplashSync/Magento2
ENV COMPOSER_HOME           /var/www/.composer/

WORKDIR     $INSTALL_DIR
VOLUME      $INSTALL_DIR
ENTRYPOINT  docker-entrypoint.sh

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

RUN yes '' | pecl install mcrypt-1.0.3 \
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
# Configure Scripts
################################################################################
#COPY ./scripts/install-magento /usr/local/bin/install-magento
#RUN chmod +x /usr/local/bin/install-magento
#
#COPY ./scripts/install-sampledata /usr/local/bin/install-sampledata
#RUN chmod +x /usr/local/bin/install-sampledata

################################################################################
# Configure Module
################################################################################
#COPY ./module/composer.json /builds/SplashSync/Magento2/composer.json


################################################################################
# Install Composer 2
################################################################################
#RUN curl -sS https://getcomposer.org/installer | php \
#    && mv composer.phar /usr/local/bin/composer
#COPY ./conf/auth.json $COMPOSER_HOME
#
#RUN chown -R www-data:www-data /var/www
#RUN su www-data -c "composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition $INSTALL_DIR $MAGENTO_VERSION"

################################################################################
# Configure Folders
################################################################################
#RUN cd $INSTALL_DIR \
#    && find . -type d -exec chmod 770 {} \; \
#    && find . -type f -exec chmod 660 {} \; \
#    && chmod u+x bin/magento




################################################################################
# Clean
################################################################################
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*



#COPY ./scripts/crontab /etc/cron.d/magento2-cron
#RUN chmod 0644 /etc/cron.d/magento2-cron \
#    && crontab -u www-data /etc/cron.d/magento2-cron


