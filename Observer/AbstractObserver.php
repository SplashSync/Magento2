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

namespace SplashSync\Magento2\Observer;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Splash\Client\Splash;
use Splash\Components\Logger;
use Splash\Local\Helpers\AccessHelper;
use Splash\Local\Helpers\MageHelper;

/**
 * Splash PHP Module For Magento 2 - Data Observer
 */
class AbstractObserver
{
    /**
     * @var string
     */
    protected static $action;

    /**
     * @var ManagerInterface
     */
    private static $messagesManager;

    /**
     * Objects Ressources Filter
     *
     * @var class-string[]
     */
    private static $resourceFilter = array(
        Address::class,
        Customer::class,
        Product::class,
        //        "sales/order",
        //        "sales/order_invoice"
    );

    /**
     * Objects Ressources Types
     *
     * @var array<class-string, string>
     */
    private static $resourceTypes = array(
        Customer::class => "ThirdParty",
        Address::class => "Address",
        Product::class => "Product",
        //        "sales/order" => "Order",
        //        "sales/order_invoice" => "Invoice"
    );

    /**
     * Objects Ressources Names
     *
     * @var array<class-string, string>
     */
    private static $resourceNames = array(
        Customer::class => "ThirdParty",
        Address::class => "Customer Address",
        Product::class => "Product",
        //        "sales/order" => "Customer Order",
        //        "sales/order_invoice" => "Customer Invoice"
    );

    /**
     * Ensure Event is in Required Scope (Object action, Resources Filter)
     *
     * @param Observer $observer
     *
     * @return null|Address|Customer|Product
     */
    protected function filterEvent(Observer $observer): ?object
    {
        //====================================================================//
        // Get Object From Event Class
        $object = $observer->getEvent()->getData("object");
        if (is_null($object)) {
            return null;
        }
        //====================================================================//
        // Filter Object Type
        foreach (self::$resourceFilter as $resourceClass) {
            //====================================================================//
            // Check if Object is Tracked by Splash
            if (!self::isTrackedClass($object, $resourceClass)) {
                continue;
            }

            /** @var Address|Customer|Product $object */
            return $object;
        }

        return null;
    }

    /**
     * Get Object Splash Type Name
     *
     * @param object $object
     *
     * @return null|string
     */
    protected static function getObjectType(object $object): ?string
    {
        foreach (self::$resourceTypes as $classString => $objectType) {
            if ($object instanceof $classString) {
                return $objectType;
            }
            if (is_subclass_of($object, $classString)) {
                return $objectType;
            }
        }

        return null;
    }

    /**
     * Get Object Splash Name
     *
     * @param object $object
     *
     * @return null|string
     */
    protected static function getObjectName(object $object): ?string
    {
        foreach (self::$resourceNames as $classString => $objectName) {
            if ($object instanceof $classString) {
                return $objectName;
            }
            if (is_subclass_of($object, $classString)) {
                return $objectName;
            }
        }

        return null;
    }

    /**
     * Generic Splash Object Changes Commit Function
     *
     * @param string $objectType
     * @param string $action
     * @param mixed  $local
     * @param string $comment
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function commitChanges(string $objectType, string $action, $local, string $comment): bool
    {
        //====================================================================//
        // Complete Comment for Logging
        $comment .= " ".$action." on Magento 2";
        //====================================================================//
        // Prepare User Name for Logging
        $adminUser = MageHelper::getAdminUser();
        $user = $adminUser ? $adminUser->getUsername() : 'Unknown Employee';
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if ((SPL_A_UPDATE == $action) && Splash::object($objectType)->isLocked()) {
            return true;
        }
        //====================================================================//
        // Commit Action on remotes nodes (Master & Slaves)
        $result = Splash::commit($objectType, $local, $action, $user, $comment);
        //====================================================================//
        // Post Splash Messages
        $this->importLog(Splash::log());
        //====================================================================//
        // Commit Action on remotes nodes (Master & Slaves)
        return $result;
    }

    /**
     * Check if Object is on Splash Tracked Classes
     *
     * @param object       $object
     * @param class-string $resourceClass
     *
     * @return bool
     */
    private static function isTrackedClass(object $object, string $resourceClass): bool
    {
        //====================================================================//
        // Check if Object is Tracked by Splash
        if (!($object instanceof $resourceClass)) {
            return false;
        }
        //====================================================================//
        // Check if Object is Managed by Splash
        if (!($object instanceof Product)) {
            try {
                if (!AccessHelper::isManaged($object)) {
                    return false;
                }
            } catch (Exception $exception) {
                return false;
            }
        }

        return true;
    }

    /**
     * Import Splash Logs to User Session
     *
     * @param Logger $log
     */
    private function importLog(Logger $log): void
    {
        //====================================================================//
        // Ensure we are in Secured Area
        if (!MageHelper::isSecuredArea()) {
            return;
        }
        //====================================================================//
        // Import Errors
        if (!empty($log->err)) {
            $this->importMessages($log->err, "addError");
        }
        //====================================================================//
        // Import Warnings
        if (!empty($log->war)) {
            $this->importMessages($log->war, "addWarning");
        }
        //====================================================================//
        // Import Messages
        if (!empty($log->msg)) {
            $this->importMessages($log->msg, "addSuccess");
        }
    }

    /**
     * Import Messages to Admin Session
     *
     * @param array  $messagesArray
     * @param string $method
     */
    private function importMessages(array $messagesArray, string $method): void
    {
        //====================================================================//
        // Ensure Connexion with Scope Config
        if (!isset(self::$messagesManager)) {
            /** @var ManagerInterface $messagesManager */
            $messagesManager = MageHelper::getModel(ManagerInterface::class);
            self::$messagesManager = $messagesManager;
        }
        foreach ($messagesArray as $message) {
            self::$messagesManager->{$method}($message);
        }
    }
}
