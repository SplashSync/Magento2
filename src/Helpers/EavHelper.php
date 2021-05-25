<?php


namespace Splash\Local\Helpers;

use Exception;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Splash\Client\Splash;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Splash\Local\Helpers\AttributesHelper;

/**
 * Magento 2 Eav Helper
 */
class EavHelper
{
    const TYPES = array(
        "int" => SPL_T_INT,
        "decimal" => SPL_T_DOUBLE,
        "varchar" => SPL_T_VARCHAR,
        "select" => SPL_T_VARCHAR,
        "text" => SPL_T_TEXT,
        "datetime" => SPL_T_DATETIME,
        "media_image" => SPL_T_IMG,
    );

    const STATIC = array(
        "entity_id" => SPL_T_INT,
        "attribute_set_id" => SPL_T_INT,
        "status" => SPL_T_BOOL,
        "created_at" => SPL_T_DATETIME,
        "updated_at" => SPL_T_DATETIME,
    );

    const KNOWN = array(
        "status" => SPL_T_BOOL,
    );

    /**
     * Get Splash Field Type from Eav Attribute
     *
     * @param Attribute $attribute
     * @return string|null
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
     * @param Attribute $attribute
     * @param float|int|array|string $value
     *
     * @return float|int|array|string|null
     *
     * @throws Exception
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

            case SPL_T_DATETIME:
                return DateHelper::toSplash($value);

            case SPL_T_IMG:
                return ImagesHelper::encode($value);
        }

        return null;
    }

    /**
     * Get Magento Field Value
     *
     * @param Attribute $attribute
     * @param float|int|array|string $value
     *
     * @return float|int|array|string|null
     *
     * @throws Exception
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
                return $value;

            case SPL_T_DATETIME:
                return DateHelper::toMage($value);
        }

        return null;
    }
}
