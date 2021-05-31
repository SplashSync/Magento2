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
use Magento\Framework\Stdlib\DateTime as MageDateTime;

/**
 * Magento 2 Eav Dates Helper
 */
class DateHelper
{
    /**
     * Convert Magento DateTime to Splash
     *
     * @param null|string $mageDateTime
     *
     * @throws Exception
     *
     * @return null|string
     */
    public static function toSplash(?string $mageDateTime): ?string
    {
        if (empty($mageDateTime)) {
            return null;
        }

        return (new \DateTime($mageDateTime))->format(SPL_T_DATETIMECAST);
    }

    /**
     * Convert Magento DateTime to Splash
     *
     * @param null|string $mageDateTime
     *
     * @throws Exception
     *
     * @return null|string
     */
    public static function toSplashDate(?string $mageDateTime): ?string
    {
        if (empty($mageDateTime)) {
            return null;
        }

        return (new \DateTime($mageDateTime))->format(SPL_T_DATECAST);
    }

    /**
     * Convert Splash DateTime to Magento DateTime
     *
     * @param null|string $mageDateTime
     *
     * @return null|string
     */
    public static function toMage(?string $mageDateTime): ?string
    {
        return (new MageDateTime())->formatDate($mageDateTime);
    }
}
