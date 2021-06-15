#!/usr/bin/env bash
################################################################################
#
#  This file is part of SplashSync Project.
#
#  Copyright (C) Splash Sync <www.splashsync.com>
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

set -e
cd /var/www

################################################################################
# First Time => INSTALL MAGENTO
if [ ! -f /var/www/html/samples.txt ]; then

    ################################################################################
    echo "Clone Sample Data Repository"
    rm -Rf /var/www/sampledata
    git clone https://github.com/magento/magento2-sample-data.git /var/www/sampledata
    cd /var/www/sampledata
    git checkout $MAGENTO_VERSION
    ################################################################################
    echo "Deploy Sample Data"
    php -f /var/www/sampledata/dev/tools/build-sample-data.php -- --ce-source="/var/www/html"
    chown -Rf www-data:www-data /var/www/html/
    chown -R www-data:www-data /var/www/sampledata/
    chmod g+ws -Rf /var/www/sampledata
    rm -rf var/cache/* var/page_cache/* generated/*
    cd /var/www/html
    php bin/magento setup:upgrade

fi
echo "Installed" > /var/www/html/samples.txt
