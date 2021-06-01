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

set -e
cd /var/www/html

################################################################################
echo "Enable Developer Mode"
php bin/magento deploy:mode:set developer
################################################################################
echo "Compile Magento"
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:enable
php bin/magento cache:clean

chown -R www-data:www-data /var/www/html/
