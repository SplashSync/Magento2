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

namespace Splash\Tests;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product as MageProduct;
use Splash\Local\Helpers\MageHelper;
use Splash\Local\Objects\Order;
use Splash\Tests\Tools\ObjectsCase;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Splash\Local\Helpers\AttributesHelper;

/**
 * Local Objects Test Suite - Populate catalog with Tests Attributes.
 */
class L01PopulateAttributesTest extends ObjectsCase
{
    /**
     * Test Creation of Product Variants Attributes
     *
     * @dataProvider productVariantAttributesProvider
     *
     * @return void
     * @throws Exception
     */
    public function testProductVariantAttributes(string $name, array $options)
    {
//        //====================================================================//
//        // Load Product Attributes List
//        $initAttribute = MageHelper::getModel(MageProduct::class)->getResource()->getAttribute($name);
//        //====================================================================//
//        // Debug => Delete Attribute
//        if ($initAttribute) {
//            $initAttribute->delete();
//        }
        //====================================================================//
        // Load Product Attributes List
        /** @var MageProduct $model */
        $model = MageHelper::getModel(MageProduct::class);
        $attribute = $model->getAttribute($name);
        //====================================================================//
        // Create Configurable Attribute
        if (!$attribute) {
            $attribute = AttributesHelper::addConfigurableAttribute($name, array());
        }
        $this->assertInstanceOf(Attribute::class, $attribute);
        //====================================================================//
        // Verify Attribute
        $this->assertTrue($attribute->isScopeGlobal());
        $this->assertEquals(1, $attribute->getIsUserDefined());
        $this->assertEquals("select", $attribute->getFrontendInput());
        $this->assertNotEmpty($attribute->getOptions());
        //====================================================================//
        // Add Attribute Options
        foreach ($options as $value => $label) {
            //====================================================================//
            // Check if Option Exists
            if (AttributesHelper::getValueFromLabel($attribute, $label)) {
                continue;
            }
            $this->assertTrue(
                AttributesHelper::addAttributeOption($attribute, $value, $label)
            );
        }
        //====================================================================//
        // Verify Attribute
        $upAttribute = $model->getAttribute($name);
        $this->assertInstanceOf(Attribute::class, $upAttribute);
        $this->assertEquals(count($options)+1, count((array) $upAttribute->getOptions()));
    }

    /**
     * Test Creation of Product Variants Attributes
     *
     * @return array
     */
    public function productVariantAttributesProvider(): array
    {
        //====================================================================//
        // Build variant Attribute Options List
        $options = array();
        for($i=1; $i<20; $i++) {
            $options["option_".$i] = "Value ".$i;
        }
        //====================================================================//
        // Build Variant Attributes List
        return array(
            "VariantA" => array("VariantA", $options),
            "VariantB" => array("VariantB", $options),
        );
    }
}
