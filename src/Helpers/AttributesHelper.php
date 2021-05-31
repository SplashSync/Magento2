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
use Magento\Eav\Model\Entity;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Setup\EavSetup;
use Splash\Client\Splash;

/**
 * Attributes Manager: Access to Advanced Magento2 Attributes Features
 */
class AttributesHelper
{
    /**
     * Get Attribute Label from Source Value
     *
     * @param Attribute                        $attribute
     * @param null|array|bool|float|int|string $value
     *
     * @return null|string
     */
    public static function getLabelFromValue(Attribute $attribute, $value): ?string
    {
        //====================================================================//
        // Identify Final Value from Attribute Source
        foreach ($attribute->getOptions() ?: array() as $option) {
            if ($option->getValue() == $value) {
                return $option->getLabel();
            }
        }
        //====================================================================//
        // Value not Found => Skip
        return $attribute->getDefaultValue();
    }

    /**
     * Get Attribute Value form Source Label
     *
     * @param Attribute              $attribute
     * @param array|float|int|string $value
     *
     * @return null|string
     */
    public static function getValueFromLabel(Attribute $attribute, $value): ?string
    {
        //====================================================================//
        // Safety Check
        if (!$attribute->usesSource()) {
            return null;
        }
        //====================================================================//
        // Identify Attribute Value from Options
        foreach ($attribute->getOptions() ?: array() as $option) {
            if ($option->getLabel() == $value) {
                return $option->getValue();
            }
        }

        return null;
    }

    /**
     * Get Attribute Options for Splash Choices
     *
     * @param Attribute $attribute
     *
     * @return array
     */
    public static function getSplashChoices(Attribute $attribute): array
    {
        $choices = array();
        if (empty($attribute->getOptions())) {
            return $choices;
        }
        //====================================================================//
        // Walk on Attribute Options
        foreach ($attribute->getOptions() as $option) {
            if (is_scalar($option->getLabel()) && !empty($option->getValue())) {
                $choices[$option->getLabel()] = $option->getLabel();
            }
        }

        return $choices;
    }

    /**
     * Create a Product Configurable Attribute with Given Options
     *
     * @param string $name
     * @param array  $options
     *
     * @throws Exception
     *
     * @return null|Attribute
     */
    public static function addConfigurableAttribute(string $name, array $options): ?Attribute
    {
        //====================================================================//
        // Only in Debug Mode (PhpUnit)
        if (!Splash::isDebugMode()) {
            Splash::log()->err("Create Configurable Attributes is Forbidden, uses DEV Mode");

            return null;
        }
        //====================================================================//
        // Connect to Attributes Factory
        /** @var Attribute $attributeModel */
        $attributeModel = MageHelper::createModel(Attribute::class);
        //====================================================================//
        // Prepare Default Data
        $attributeData = array(
            'attribute_code' => $name,
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_label' => $name,
            'frontend_input' => 'select',
            'is_configurable' => 1,
            'backend_type' => 'int',
        );
        //====================================================================//
        // Add Attribute
        $attributeModel->addData(array_replace_recursive($attributeData, $options));
        //====================================================================//
        // Setup Attribute Entity Type Id
        /** @var Entity $entityModel */
        $entityModel = MageHelper::getModel(Entity::class);
        $entityTypeID = $entityModel->setType('catalog_product')->getTypeId();
        $attributeModel->setEntityTypeId($entityTypeID);

        return $attributeModel->save();
    }

    /**
     * Add Option Value Attribute with Given Options
     *
     * @param Attribute   $attribute
     * @param string      $value
     * @param null|string $label
     *
     * @return bool
     */
    public static function addAttributeOption(Attribute &$attribute, string $value, ?string $label): bool
    {
        //====================================================================//
        // Only in Debug Mode (PhpUnit)
        if (!Splash::isDebugMode()) {
            return Splash::log()->err("Create Configurable Attributes is Forbidden, uses DEV Mode");
        }
        //====================================================================//
        // Connect to Eav Setup
        /** @var EavSetup $eavSetup */
        $eavSetup = MageHelper::createModel(EavSetup::class);
        //====================================================================//
        // Add Option to Attribute
        try {
            $eavSetup->addAttributeOption(array(
                'values' => array(
                    $value => $label ?: $value,
                ),
                'attribute_id' => $attribute->getId(),
            ));
        } catch (\Throwable $throwable) {
            return Splash::log()->report($throwable);
        }

        return true;
    }
}
