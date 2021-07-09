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

namespace Splash\Local\Objects\Order;

use Magento\Sales\Model\Order;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Helpers\AccessHelper;
use Throwable;

/**
 * Magento 2 Order CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return false|Order
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Load Object
        try {
            /** @var Order $order */
            $order = $this->repository->get((int) $objectId);
            $this->loadOrderAddress($order->getShippingAddress() ?: null);
            //====================================================================//
            // Check if Object is Managed by Splash
            AccessHelper::isManaged($order, true);
        } catch (Throwable $exception) {
            return Splash::log()->err($exception->getMessage());
        }

        return $order;
    }

    /**
     * Create Request Object
     *
     * @return false New Object
     */
    public function create()
    {
        return Splash::log()->err("Order creation is not implemented");
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
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (!$needed) {
            return $this->object->getEntityId();
        }

        return Splash::log()->err("Order Update is Forbidden");
    }

    /**
     * Delete requested Object
     *
     * @param int $objectId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($objectId = null)
    {
        return Splash::log()->err("Order Delete is Forbidden");
    }
}
