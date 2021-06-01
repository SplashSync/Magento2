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
su www-data

################################################################################
# Ensure Splash Vendor DIR doesn't exists
if [ -d /builds/SplashSync/Magento2/vendor ]; then
    echo "Module Vendor MUST be Deleted before Docker Start"
    exit 1
fi

################################################################################
# INIT MAGENTO
################################################################################
wait-for-mysql.sh           # Wait for Mysql Server Wakeup
install-magento.sh          # First Time => INSTALL MAGENTO
setup-magento.sh            # Configure Magento
compile-magento.sh          # Compile Magento

#################################################################################
# Start Apache
exec apache2-foreground "$@"
