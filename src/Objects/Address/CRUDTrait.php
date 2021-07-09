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

namespace Splash\Local\Objects\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Helpers\AccessHelper;
use Splash\Local\Helpers\MageHelper;
use Throwable;

/**
 * Magento 2 Customers Address CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return Address|false
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Load Object
        try {
            /** @var Address $address */
            $address = $this->registry->retrieve((int) $objectId);
            //====================================================================//
            // Check if Object is Managed by Splash
            AccessHelper::isManaged($address, true);
        } catch (Throwable $exception) {
            return Splash::log()->err($exception->getMessage());
        }

        return $address;
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
        if (!$this->verifyRequiredFields()) {
            return false;
        }
        //====================================================================//
        // Create Empty Customer Address
        /** @var Address $address */
        $address = MageHelper::createModel(AddressInterface::class);
        $address->setData("customer_id", self::objects()->id($this->in["customer_id"]) ?: null);
        $address->setData("firstname", $this->in["firstname"]);
        $address->setData("lastname", $this->in["lastname"]);
        $address->setData("street", $this->in["street_1"]);
        $address->setData("postcode", $this->in["postcode"]);
        $address->setData("city", $this->in["city"]);
        $address->setData("country_id", $this->in["country_iso"]);
        $address->setData("telephone", $this->in["telephone"]);

        //====================================================================//
        // Save Object
        try {
            /** @phpstan-ignore-next-line */
            return $this->repository->save($address);
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
     * @param string $objectId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Execute Generic Magento Delete Function ...
        return $this->coreDelete((int) $objectId);
    }
}
