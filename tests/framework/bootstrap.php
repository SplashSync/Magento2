<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

// phpcs:disable PSR1.Files.SideEffects

use Magento\Framework\App\Bootstrap;

if (!defined('MAGE_ROOT')) {
    define('MAGE_ROOT', '/var/www/html');
}

require_once MAGE_ROOT.'/app/autoload.php';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', '/tmp');
}

require_once __DIR__.'/autoload.php';
\Magento\Framework\Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());

error_reporting(E_ALL);
ini_set('display_errors', "1");
ini_set('precision', "14");
ini_set('serialize_precision', "14");

$bootstrap = Bootstrap::create(MAGE_ROOT, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
