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
 * Main ThirdParty Fields Configurator
 */
class ThirdPartyConfigurator extends AbstractConfigurator
{
    const CONFIGURATION = array(
        //====================================================================//
        // System - Core Informations
        //====================================================================//
        "email" => array(
            "type" => SPL_T_EMAIL,
            "itemtype" => "http://schema.org/ContactPoint",
            "itemprop" => "email",
            'inlist' => true
        ),
        "firstname" => array(
            "itemtype" => "http://schema.org/Person",
            "itemprop" => "familyName",
            'inlist' => true
        ),
        "lastname" => array(
            "itemtype" => "http://schema.org/Person",
            "itemprop" => "givenName",
            'inlist' => true
        ),
        "prefix" => array(
            "itemtype" => "http://schema.org/Person",
            "itemprop" => "honorificPrefix"
        ),
        "middlename" => array(
            "itemtype" => "http://schema.org/Person",
            "itemprop" => "additionalName"
        ),
        "suffix" => array(
            "itemtype" => "http://schema.org/Person",
            "itemprop" => "honorificSuffix"
        ),

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

        "website_id" => array('group' => "Meta", "excluded" => true),
        "store_id" => array('group' => "Meta", "excluded" => true),
        "created_in" => array('group' => "Meta", "write" => false),
        "confirmation" => array('group' => "Meta", "write" => false),

        //====================================================================//
        // System - Excluded
        //====================================================================//
        "first_failure" => array("excluded" => true),
        "rp_token_created_at" => array("excluded" => true),
        "lock_expires" => array("excluded" => true),

        "group_id" => array("excluded" => true),
        "default_billing" => array("excluded" => true),
        "default_shipping" => array("excluded" => true),
    );

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        return array(
            "ThirdParty" => array("fields" => self::CONFIGURATION)
        );
    }
}
