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

namespace Splash\Local\Objects\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Helpers\AccessHelper;
use Throwable;

/**
 * Magento 2 Invoice CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return false|Invoice
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Load Object
        try {
            /** @var Invoice $invoice */
            $invoice = $this->repository->get((int) $objectId);
            $this->loadOrderAddress($invoice->getShippingAddress());
            //====================================================================//
            // Check if Object is Managed by Splash
            AccessHelper::isManaged($invoice, true);
        } catch (Throwable $exception) {
            return Splash::log()->err($exception->getMessage());
        }

        return $invoice;
    }

    /**
     * Create Request Object
     *
     * @return false New Object
     */
    public function create(): bool
    {
        return Splash::log()->err("Invoice creation is not implemented");
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object ID
     */
    public function update(bool $needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (!$needed) {
            return $this->object->getEntityId();
        }

        return Splash::log()->err("Invoice Update is Forbidden");
    }

    /**
     * Delete requested Object
     *
     * @param int $objectId Object ID.  If NULL, Object needs to be created.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($objectId = null): bool
    {
        return Splash::log()->err("Invoice Delete is Forbidden");
    }
}
