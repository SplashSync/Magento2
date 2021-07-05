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
echo "Enable SplashSync Module"
php bin/magento module:disable SplashSync_Magento2
php bin/magento module:enable SplashSync_Magento2

################################################################################
# Configure Magento for Development & Tests
################################################################################

################################################################################
echo "Configure Store"
bin/magento config:set general/store_information/name                   "Magento 2"
bin/magento config:set general/store_information/phone                  "0123456789"
bin/magento config:set general/store_information/country_id             "FR"
bin/magento config:set general/store_information/region_id              "185"
bin/magento config:set general/store_information/postcode               "33000"
bin/magento config:set general/store_information/city                   "Bordeaux"
bin/magento config:set general/store_information/street_line1           "10 Place Gambetta"

################################################################################
echo "Configure Splash Connector"
bin/magento config:set splashsync/core/id                               "ThisIsMagento2Key"
bin/magento config:set splashsync/core/key                              "ThisTokenIsNotSoSecretChangeIt"
bin/magento config:set splashsync/core/expert                           "1"
bin/magento config:set splashsync/core/host                             "http://toolkit/ws/soap"
bin/magento config:set splashsync/security/username                     "admin"

################################################################################
echo "Configure Sync"
bin/magento config:set splashsync/sync/website                          "1"
bin/magento config:set splashsync/sync/attribute_set                    "4"
bin/magento config:set splashsync/sync/logistic                         "1"

################################################################################
echo "Configure Carriers"
bin/magento config:set carriers/freeshipping/active                     "1"



