<?php


namespace Splash\Local\Helpers;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Directory\Model\Currency;

use Splash\Client\Splash;
use Magento\Backend\Model\Auth\Session;
use Magento\User\Model\User;
use Magento\Catalog\Model\AbstractModel;

/**
 * Access to Magento 2 Services
 */
trait MageHelper
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

        return self::$scopeConfig->getValue($key,ScopeInterface::SCOPE_STORE);
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
