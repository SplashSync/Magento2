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
cd /var/www/html
su www-data

################################################################################
# Ensure Module is Installed
composer require splash/magento2:dev-master --no-scripts --no-progress --no-suggest

################################################################################
# First Time => INSTALL MAGENTO
if [ ! -f installed.txt ]; then

    echo "Run Magento Installer"
    /var/www/html/bin/magento setup:install \
        --base-url=$MAGENTO_URL \
        --backend-frontname=$MAGENTO_BACKEND_FRONTNAME \
        --language=$MAGENTO_LANGUAGE \
        --timezone=$MAGENTO_TIMEZONE \
        --currency=$MAGENTO_DEFAULT_CURRENCY \
        --db-host=$MYSQL_HOST \
        --db-name=$MYSQL_DATABASE \
        --db-user=$MYSQL_USER \
        --db-password=$MYSQL_PASSWORD \
        --use-secure=$MAGENTO_USE_SECURE \
        --base-url-secure=$MAGENTO_BASE_URL_SECURE \
        --use-secure-admin=$MAGENTO_USE_SECURE_ADMIN \
        --admin-firstname=$MAGENTO_ADMIN_FIRSTNAME \
        --admin-lastname=$MAGENTO_ADMIN_LASTNAME \
        --admin-email=$MAGENTO_ADMIN_EMAIL \
        --admin-user=$MAGENTO_ADMIN_USERNAME \
        --admin-password=$MAGENTO_ADMIN_PASSWORD \
        --elasticsearch-host=$ELASTICSEARCH_HOST \
        --elasticsearch-port=$ELASTICSEARCH_PORT \
        --enable-modules=$MAGENTO_ENABLE \
        --disable-modules=$MAGENTO_DISABLE

fi
echo "Installed" > installed.txt
