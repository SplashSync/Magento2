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

use Magento\Sales\Model\Order\Invoice;
use Splash\Models\Objects\Invoice\Status;

/**
 * Override Splash Core Helper to Manage Magento 2 Invoices Statuses
 */
class InvoiceStatusHelper extends Status
{
    /**
     * List of Available Magento 2 Invoices Statuses
     *
     * @var string[]
     */
    private static $statuses = array(
        Invoice::STATE_CANCELED => self::CANCELED,
        Invoice::STATE_OPEN => self::PAYMENT_DUE,
        Invoice::STATE_PAID => self::COMPLETE,
    );

    /**
     * Convert Magento Status to Splash
     *
     * @param string $mageStatus
     *
     * @return string
     */
    public static function toSplash(string $mageStatus): string
    {
        //====================================================================//
        // Use Generic Status
        return self::$statuses[$mageStatus] ?: self::UNKNOWN;
    }

    /**
     * Convert Splash Status to Magento
     *
     * @param string $splashStatus
     *
     * @return string
     */
    public static function toMage(string $splashStatus): string
    {
        //====================================================================//
        // Use Generic Status
        return (string) (array_flip(self::$statuses)[$splashStatus] ?: self::UNKNOWN);
    }
}
