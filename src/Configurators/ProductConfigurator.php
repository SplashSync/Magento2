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
 * Main Product Fields Configurator
 */
class ProductConfigurator extends AbstractConfigurator
{
    const CONFIGURATION = array(
        //====================================================================//
        // System - Core Informations
        //====================================================================//
        "status" => array("itemtype" => "http://schema.org/Product", "itemprop" => "offered"),
        "visibility" => array(
            'group' => "Meta",
            "choices" => array(
                'Not Visible Individually' => 'Not Visible Individually',
                'Catalog' => 'Catalog',
                'Search' => 'Search',
                'Catalog, Search' => 'Catalog, Search',
            ),
            'notest' => true,
        ),
        "sku_type" => array("excluded" => true),
        "weight_type" => array("excluded" => true),
        "shipment_type" => array("excluded" => true),

        //====================================================================//
        // System - Pricing
        //====================================================================//
        'news_from_date' => array("write" => false, 'group' => "Pricing"),
        'news_to_date' => array("write" => false, 'group' => "Pricing"),
        'special_from_date' => array("write" => false, 'group' => "Pricing"),
        'special_to_date' => array("write" => false, 'group' => "Pricing"),
        "price_view" => array("excluded" => true),
        "price_type" => array("excluded" => true),
        "tier_price" => array("excluded" => true),
        "msrp_display_actual_price_type" => array("excluded" => true),

        //====================================================================//
        // System - Images
        //====================================================================//

        "image_label" => array("excluded" => true),
        "small_image" => array("excluded" => true),
        "small_image_label" => array("excluded" => true),
        "thumbnail_label" => array("excluded" => true),

        //====================================================================//
        // System - Read Only
        //====================================================================//
        "attribute_set_id" => array("write" => false, 'group' => "Meta"),
        "tax_class_id" => array("write" => false),
        "country_of_manufacture" => array("write" => false),

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
        // System - Layout Informations
        //====================================================================//
        "page_layout" => array('write' => false, 'group' => "Meta"),
        "custom_design" => array('write' => false, 'group' => "Meta"),
        "custom_layout" => array('write' => false, 'group' => "Meta"),
        'custom_layout_update' => array('write' => false, 'group' => "Meta"),
        'custom_design_from' => array('write' => false, 'group' => "Meta"),
        'custom_design_to' => array('write' => false, 'group' => "Meta"),

        //====================================================================//
        // Tests - Variants Attributes
        //====================================================================//
        "VariantA" => array('write' => false),
        "VariantB" => array('write' => false),

        //====================================================================//
        // System - Excluded
        //====================================================================//
        "has_options" => array("excluded" => true),
        "required_options" => array("excluded" => true),
        "options_container" => array("excluded" => true),
        "gift_message_available" => array("excluded" => true),
        "links_purchased_separately" => array("excluded" => true),
        "samples_title" => array("excluded" => true),
        "links_title" => array("excluded" => true),
        "links_exist" => array("excluded" => true),
    );

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        return array(
            "Product" => array("fields" => self::CONFIGURATION)
        );
    }
}
