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

namespace Splash\Local\Objects\ThirdParty;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Helpers\AccessHelper;
use Splash\Local\Helpers\MageHelper;
use Throwable;

/**
 * Magento 2 Customers CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return false|object
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Load Object
        try {
            /** @var Customer $customer */
            $customer = $this->repository->getById((int) $objectId);
            $this->loadAddress($customer->getDefaultBilling());
            //====================================================================//
            // Check if Object is Managed by Splash
            AccessHelper::isManaged($customer, true);
        } catch (Throwable $exception) {
            return Splash::log()->err($exception->getMessage());
        }

        return $customer;
    }

    /**
     * Create Request Object
     *
     * @return false|object New Object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Required Fields
        if (empty($this->in["firstname"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "firstname");
        }
        if (empty($this->in["lastname"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "lastname");
        }
        if (empty($this->in["email"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "email");
        }
        //====================================================================//
        // Create Empty Customer
        /** @var Customer $customer */
        $customer = MageHelper::createModel(CustomerInterface::class);
        $customer->setData("firstname", $this->in["firstname"]);
        $customer->setData("lastname", $this->in["lastname"]);
        $customer->setData("email", $this->in["email"]);
        //====================================================================//
        // Save Object
        try {
            return $this->repository->save($customer);
        } catch (Throwable $exception) {
            return Splash::log()->err($exception->getMessage());
        }
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id
     */
    public function update($needed)
    {
        return $this->coreUpdate($needed);
    }

    /**
     * Delete requested Object
     *
     * @param int $objectId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($objectId = null): bool
    {
        //====================================================================//
        // Execute Generic Magento Delete Function ...
        return $this->coreDelete((int) $objectId);
    }
}
