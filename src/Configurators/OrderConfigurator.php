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

namespace Splash\Local\Configurators;

use Splash\Models\AbstractConfigurator;

/**
 * Main Order Fields Configurator
 */
class OrderConfigurator extends AbstractConfigurator
{
    const CONFIGURATION = array(
        //====================================================================//
        // System - Metadata
        //====================================================================//
        "created_at" => array(
            'write' => false,
            'required' => false,
            "itemtype" => "http://schema.org/DataFeedItem",
            "itemprop" => "dateCreated",
            'group' => "Meta",
        ),
        "updated_at" => array(
            'write' => false,
            'required' => false,
            "itemtype" => "http://schema.org/DataFeedItem",
            "itemprop" => "dateModified",
            'group' => "Meta",
        ),

        //====================================================================//
        // System - Read Only
        //====================================================================//

        "created_in" => array('group' => "Meta", "write" => false),
        "confirmation" => array('group' => "Meta", "write" => false),

        //====================================================================//
        // System - Excluded
        //====================================================================//
    );

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        return array(
            "Order" => array("fields" => self::CONFIGURATION)
        );
    }
}
