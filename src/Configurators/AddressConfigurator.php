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
 * Main Address Fields Configurator
 */
class AddressConfigurator extends AbstractConfigurator
{
    const CONFIGURATION = array(
        //====================================================================//
        // System - Core Informations
        //====================================================================//
        "company" => array(
            "itemtype" => "http://schema.org/Organization",
            "itemprop" => "legalName",
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
        "postcode" => array(
            "itemtype" => "http://schema.org/PostalAddress",
            "itemprop" => "postalCode",
            'required' => true
        ),
        "country_id" => array(
            'required' => false,
            'write' => false,
            'notest' => true
        ),

        //====================================================================//
        // System - Excluded
        //====================================================================//
        "created_at" => array("excluded" => true),
        "updated_at" => array("excluded" => true),
        "region" => array("excluded" => true),
        "attribute_set_id" => array("excluded" => true),
        "vat_is_valid" => array("excluded" => true),
        "vat_request_id" => array("excluded" => true),
        "vat_request_date" => array("excluded" => true),
        "vat_request_success" => array("excluded" => true),
    );

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        return array(
            "Address" => array("fields" => self::CONFIGURATION)
        );
    }
}
