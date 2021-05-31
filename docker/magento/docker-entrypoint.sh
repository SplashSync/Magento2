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

################################################################################
# Ensure Splash Vendor DIR doesn't exists
if [ -d /builds/SplashSync/Magento2/vendor ]; then
    echo "Module Vendor MUST be Deleted before Docker Start"
    exit 1
fi

################################################################################
# Wait for Mysql Server Wakeup
sh /builds/SplashSync/Magento2/scripts/wait-for-mysql.sh

################################################################################
# First Time => INSTALL MAGENTO + SAMPLE DATA
if [ ! -f installed.txt ]; then
    echo "Install Magento"
    install-magento
#    echo "Install Sample data"
#    install-sampledata
fi
echo "Installed" > installed.txt

su www-data

################################################################################
# Install Phpunit
sh /builds/SplashSync/Magento2/scripts/install-phpunit.sh
################################################################################
# Install SplashSync Module
sh /builds/SplashSync/Magento2/scripts/install-dev-module.sh
################################################################################
# Configure Magento for Development & Tests
sh /builds/SplashSync/Magento2/scripts/setup-magento.sh
################################################################################
# Compile Magento
sh /builds/SplashSync/Magento2/scripts/compile-magento.sh

################################################################################
echo "Init Magento"
exec /sbin/my_init
