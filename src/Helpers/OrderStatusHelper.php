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

use Magento\Sales\Model\Order;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Objects\Order\Status;

class OrderStatusHelper extends Status
{
    /**
     * List of Available Magento 2 Order Statuses
     *
     * @var string[]
     */
    private static $statuses = array(
        Order::STATE_PENDING_PAYMENT => self::DRAFT,
        Order::STATE_PROCESSING => self::PROCESSING,
        Order::STATE_COMPLETE => self::IN_TRANSIT,
        Order::STATE_CLOSED => self::DELIVERED,
        Order::STATE_CANCELED => self::CANCELED,
        Order::STATE_HOLDED => self::PROBLEM,
        Order::STATE_PAYMENT_REVIEW => self::DRAFT,
        Order::STATE_NEW => self::DRAFT,
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
        return array_flip(self::$statuses)[$splashStatus] ?: self::UNKNOWN;
    }
}
