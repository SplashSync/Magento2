<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Bootstrap;

if (!defined('MAGE_ROOT')) {
    define('MAGE_ROOT', '/var/www/html');
}

require_once MAGE_ROOT . '/app/autoload.php';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/tmp');
}

require_once __DIR__ . '/autoload.php';
\Magento\Framework\Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());

error_reporting(E_ALL);
ini_set('display_errors', "1");
ini_set('precision', "14");
ini_set('serialize_precision', "14");

$bootstrap = Bootstrap::create(MAGE_ROOT, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
