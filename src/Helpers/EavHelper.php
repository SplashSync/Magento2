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
use Magento\Eav\Model\Entity\Attribute;
use Splash\Client\Splash;

/**
 * Magento 2 Eav Helper
 */
class EavHelper
{
    /**
     * Convert Magento Attributes Types to Splash Types Names
     *
     * @var array
     */
    const TYPES = array(
        "int" => SPL_T_INT,
        "decimal" => SPL_T_DOUBLE,
        "varchar" => SPL_T_VARCHAR,
        "select" => SPL_T_VARCHAR,
        "text" => SPL_T_TEXT,
        "date" => SPL_T_DATE,
        "datetime" => SPL_T_DATETIME,
        "media_image" => SPL_T_IMG,
    );

    /**
     * Static Magento Attributes tSplash Types Names
     *
     * @var array
     */
    const STATIC = array(
        "entity_id" => SPL_T_INT,
        "attribute_set_id" => SPL_T_INT,
        "status" => SPL_T_BOOL,
        "created_at" => SPL_T_DATETIME,
        "updated_at" => SPL_T_DATETIME,
    );

    /**
     * Known Magento Attributes Splash Types Names
     *
     * @var array
     */
    const KNOWN = array(
        "status" => SPL_T_BOOL,
    );

    /**
     * Get Splash Field Type from Eav Attribute
     *
     * @param Attribute $attribute
     *
     * @return null|string
     */
    public static function toSplashType(Attribute $attribute): ?string
    {
        $backendType = $attribute->getBackendType();
        $frontendType = $attribute->getFrontendInput();
        if ('static' == $backendType) {
            if (isset(self::STATIC[$attribute->getName()])) {
                return self::STATIC[$attribute->getName()];
            }

            return self::TYPES[$frontendType] ?? null;
        }

        if (isset(self::KNOWN[$attribute->getName()])) {
            return self::KNOWN[$attribute->getName()];
        }

        if ($attribute->usesSource()) {
            return SPL_T_VARCHAR;
        }

        return self::TYPES[$frontendType] ?? (self::TYPES[$backendType] ?? null);
    }

    /**
     * Field type Is Read Only for Splash
     *
     * @param string $splashType
     *
     * @return bool
     */
    public static function isReadOnlyType(string $splashType): bool
    {
        return in_array($splashType, array(SPL_T_IMG, SPL_T_FILE), true);
    }

    /**
     * Get Splash Field Value
     *
     * @param Attribute                  $attribute
     * @param null|bool|float|int|string $value
     *
     * @throws Exception
     *
     * @return null|array|bool|float|int|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function toSplashValue(Attribute $attribute, $value)
    {
        //====================================================================//
        // Attribute Uses Sources
        if ($attribute->usesSource()) {
            return AttributesHelper::getLabelFromValue($attribute, $value);
        }
        //====================================================================//
        // Generic Attribute
        switch (self::toSplashType($attribute)) {
            case SPL_T_BOOL:
                return !empty($value);
            case SPL_T_INT:
                return (int) $value;
            case SPL_T_DOUBLE:
                return (float) $value;
            case SPL_T_VARCHAR:
            case SPL_T_TEXT:
                return $value;
            case SPL_T_DATE:
                return DateHelper::toSplashDate((string) $value);
            case SPL_T_DATETIME:
                return DateHelper::toSplash((string) $value);
            case SPL_T_IMG:
                return ImagesHelper::encode((string) $value);
        }

        return null;
    }

    /**
     * Get Magento Field Value
     *
     * @param Attribute        $attribute
     * @param float|int|string $value
     *
     * @throws Exception
     *
     * @return null|float|int|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function toMageValue(Attribute $attribute, $value)
    {
        //====================================================================//
        // Attribute Uses Sources
        if ($attribute->usesSource()) {
            return AttributesHelper::getValueFromLabel($attribute, $value);
        }
        //====================================================================//
        // Generic Attribute
        switch (self::toSplashType($attribute)) {
            case SPL_T_BOOL:
                return !empty($value) ? 1 : 0;
            case SPL_T_INT:
                return (int) $value;
            case SPL_T_DOUBLE:
                return (float) $value;
            case SPL_T_VARCHAR:
            case SPL_T_TEXT:
                return (string) $value;
            case SPL_T_DATE:
            case SPL_T_DATETIME:
                return DateHelper::toMage((string) $value);
        }

        return null;
    }
}
