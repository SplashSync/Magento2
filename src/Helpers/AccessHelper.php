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

namespace Splash\Local\Helpers;

use Exception;
use Magento\Store\Model\Website;

/**
 * Manage Access to Websites Objects
 *
 * @package Splash\Local\Helpers
 */
class AccessHelper
{
    const CFG_PATH = "splashsync/filters/website";

    /**
     * @var array
     */
    private static $allowedWebsites;

    /**
     * @var array
     */
    private static $refusedWebsites;

    /**
     * @var array
     */
    private static $allowedStores;

    /**
     * @var array
     */
    private static $refusedStores;

    /**
     * Check if Object is Managed by Splash
     *
     * @param object $mageObject
     * @param bool   $throwException
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function isManaged(object $mageObject, bool $throwException = false): bool
    {
        //====================================================================//
        // Detect Store Id from  Object
        $storeId = null;
        if (method_exists($mageObject, "getStore")) {
            $storeId = $mageObject->getStore()->getId();
        }
        if (method_exists($mageObject, "getStoreId")) {
            $storeId = $mageObject->getStoreId();
        }
        //====================================================================//
        // Check Store Id
        if (!empty($storeId) && !self::isAllowedStore($storeId)) {
            if ($throwException) {
                throw new Exception("Access not allowed for Splash on this Website/Store");
            }

            return false;
        }

        return true;
    }

    /**
     * Get Ids of Allowed Websites
     *
     * @return array
     */
    public static function getAllowedWebsites(): array
    {
        if (!isset(self::$allowedWebsites)) {
            self::loadConfiguration();
        }

        return self::$allowedWebsites ?? array();
    }

    /**
     * Check if Website is Allowed
     *
     * @param int $websiteId
     *
     * @return bool
     */
    public static function isAllowedWebsite(int $websiteId): bool
    {
        return in_array($websiteId, self::getAllowedWebsites(), true);
    }

    /**
     * Get Ids of Refused Websites
     *
     * @return array
     */
    public static function getRefusedWebsites(): array
    {
        if (!isset(self::$refusedWebsites)) {
            self::loadConfiguration();
        }

        return self::$refusedWebsites ?? array();
    }

    /**
     * Get Ids of Allowed Stores
     *
     * @return array
     */
    public static function getAllowedStores(): array
    {
        if (!isset(self::$allowedStores)) {
            self::loadConfiguration();
        }

        return self::$allowedStores ?? array();
    }

    /**
     * Check if Store is Allowed
     *
     * @param int $storeId
     *
     * @return bool
     */
    public static function isAllowedStore(int $storeId): bool
    {
        return in_array($storeId, self::getAllowedStores(), true);
    }

    /**
     * Get Ids of Refused Stores
     *
     * @return array
     */
    public static function getRefusedStores(): array
    {
        if (!isset(self::$refusedStores)) {
            self::loadConfiguration();
        }

        return self::$refusedStores ?? array();
    }

    /**
     * Load Configuration
     *
     * @return void
     */
    private static function loadConfiguration(): void
    {
        $websites = MageHelper::getStoreManager()->getWebsites();

        self::$allowedWebsites = self::$allowedStores = array(0);
        self::$refusedWebsites = self::$refusedStores = array();

        /** @var Website $website */
        foreach ($websites as $website) {
            $configValue = MageHelper::getConfig(self::CFG_PATH, $website->getId());
            if (empty($configValue)) {
                self::$allowedWebsites[] = $website->getId();
                self::$allowedStores = array_merge_recursive(self::$allowedStores, array_keys($website->getStores()));
            } else {
                self::$refusedWebsites[] = $website->getId();
                self::$refusedStores = array_merge_recursive(self::$refusedStores, array_keys($website->getStores()));
            }
        }
    }
}
