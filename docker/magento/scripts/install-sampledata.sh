#!/bin/bash
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

cd /var/www
#su www-data

################################################################################
echo "Clone Sample Data Repository"
git clone https://github.com/magento/magento2-sample-data.git sampledata $MAGENTO_VERSION
cd sampledata
git checkout $MAGENTO_VERSION
################################################################################
echo "Deploy Sample Data"
chown -R www-data:www-data /var/www/sampledata/
php -f /var/www/sampledata/dev/tools/build-sample-data.php -- --ce-source="/var/www/html"
chown -R www-data:www-data /var/www/html/
################################################################################
# ReCompile Magento
compile-magento.sh

