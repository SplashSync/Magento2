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

use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\User\Model\User;

/**
 * Access to Magento 2 Services
 */
class MageHelper
{
    /**
     * @var ObjectManager
     */
    private static $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private static $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private static $scopeConfig;

    /**
     * @var null|User
     */
    private static $adminUser;

    /**
     * @var Store
     */
    private static $store;

    /**
     * @var Currency
     */
    private static $currency;

    /**
     * @var Website
     */
    private static $newWebsite;

    /**
     * Get Magento Model
     *
     * @param class-string $modelClass
     *
     * @return object
     */
    public static function getModel(string $modelClass): object
    {
        return self::getObjectManager()->get($modelClass);
    }

    /**
     * Create Magento Model
     *
     * @param class-string $modelClass
     *
     * @return mixed
     */
    public static function createModel(string $modelClass): object
    {
        return self::getObjectManager()->create($modelClass);
    }

    /**
     * Get a Store Config Value
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function getStoreConfig(string $key)
    {
        //====================================================================//
        // Ensure Connexion with Scope Config
        if (!isset(self::$scopeConfig)) {
            self::$scopeConfig = self::getObjectManager()->get(ScopeConfigInterface::class);
        }

        return self::$scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get a Config Value with Website or Store Select
     *
     * @param string   $key
     * @param null|int $websiteId
     * @param null|int $storeId
     *
     * @return mixed
     */
    public static function getConfig(string $key, ?int $websiteId = null, ?int $storeId = null)
    {
        //====================================================================//
        // Ensure Connexion with Scope Config
        if (!isset(self::$scopeConfig)) {
            self::$scopeConfig = self::getObjectManager()->get(ScopeConfigInterface::class);
        }
        //====================================================================//
        // Get a Website Config
        if ($websiteId) {
            return self::$scopeConfig->getValue($key, ScopeInterface::SCOPE_WEBSITE, $websiteId);
        }

        return self::$scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get Store Manager
     *
     * @return StoreManagerInterface
     */
    public static function getStoreManager(): StoreManagerInterface
    {
        //====================================================================//
        // Ensure Connexion with Store Manager
        if (!isset(self::$storeManager)) {
            self::$storeManager = self::getObjectManager()->get(StoreManagerInterface::class);
        }

        return self::$storeManager;
    }

    /**
     * Get Session Admin User
     *
     * @return null|User
     */
    public static function getAdminUser(): ?User
    {
        if (!isset(self::$adminUser)) {
            self::$adminUser = self::getObjectManager()->get(Session::class)->getUser();
        }

        return self::$adminUser;
    }

    /**
     * Get Magento Default Store
     *
     * @return Store
     */
    public static function getStore():Store
    {
        if (!isset(self::$store)) {
            self::$store = self::getStoreManager()->getStore();
        }

        return self::$store;
    }

    /**
     * Get Magento Default Currency
     *
     * @return Currency
     */
    public static function getCurrency():Currency
    {
        if (!isset(self::$currency)) {
            self::$currency = self::getStore()->getDefaultCurrency();
        }

        return self::$currency;
    }

    /**
     * Get Magento Default Website for Creation
     *
     * @return Website
     */
    public static function getDefaultNewWebsite():Website
    {
        if (!isset(self::$newWebsite)) {
            $websiteId = self::getStoreConfig("splashsync/sync/website") ?: 1;
            self::$newWebsite = self::getStoreManager()->getWebsite($websiteId);
        }

        return self::$newWebsite;
    }

    /**
     * Get Magento Default Store for Creation
     *
     * @return Store
     */
    public static function getDefaultNewStore():Store
    {
        return self::getDefaultNewWebsite()->getDefaultStore();
    }

    /**
     * Check if Magento is in Secured Area
     *
     * @return bool
     */
    public static function isSecuredArea(): bool
    {
        /** @var null|bool $isSecured */
        static $isSecured;

        if (!isset($isSecured)) {
            try {
                /** @var State $state */
                $state = self::getModel(State::class);
                $isSecured = (Area::AREA_ADMINHTML == $state->getAreaCode());
            } catch (LocalizedException $e) {
                $isSecured = false;
            }
        }

        return $isSecured;
    }

    /**
     * Get Magento Object Manager
     *
     * @return ObjectManager
     */
    protected static function getObjectManager():ObjectManager
    {
        //====================================================================//
        // Ensure Connexion with Object Manager
        if (!isset(self::$objectManager)) {
            self::$objectManager = ObjectManager::getInstance();
        }

        return self::$objectManager;
    }
}
