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
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as MageProduct;
use Splash\Local\Helpers\AttributesHelper;
use Splash\Local\Helpers\MageHelper;
use Splash\Tests\Tools\ObjectsCase;

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
     * @throws Exception
     *
     * @return void
     */
    public function testProductVariantAddAttributes(string $name, array $options, array $values)
    {
        /** @var MageProduct $model */
        $model = MageHelper::getModel(MageProduct::class);
        //====================================================================//
        // Debug => Delete Attribute
        if (!in_array($name, array("VariantA", "VariantB"), true)) {
            $initAttribute = $model->getAttribute($name);
            if ($initAttribute) {
                $initAttribute->delete();
            }
        }
        //====================================================================//
        // Load Product Attribute
        $attribute = $model->getAttribute($name);
        //====================================================================//
        // Create Configurable Attribute
        if (!$attribute) {
            AttributesHelper::addConfigurableAttribute($name, array("option" => $options));
            $attribute = $model->getAttribute($name);
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
        foreach ($values as $value => $label) {
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
        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertEquals(count($values) + 1, count((array) $attribute->getOptions()));
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
        // Build variant Attribute Values List
        $values = array();
        $options = array(
            "order" => array(),
            "value" => array(),
        );
        for ($i = 1; $i < 20; $i++) {
            $key = "option_".$i;
            $value = "Value ".$i;
            $values[$key] = $value;
            $options["order"][$key] = $i;
            $options["value"][$key] = array(0 => $value, 1 => $value);
        }
        //====================================================================//
        // Build Variant Attributes List
        return array(
            "VariantA" => array("VariantA", $options, $values),
            "VariantB" => array("VariantB", $options, $values),
            "VariantX" => array("VariantX", $options, $values),
        );
    }
}
