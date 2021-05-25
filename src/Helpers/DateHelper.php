<?php


namespace Splash\Local\Helpers;

use Exception;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Stdlib\DateTime as MageDateTime;

/**
 * Magento 2 Eav Dates Helper
 */
class DateHelper
{
    /**
     * Convert Magento DateTime to Splash
     *
     * @param string|null $mageDateTime
     * @return string|null
     *
     * @throws Exception
     */
    public static function toSplash(?string $mageDateTime): ?string
    {
        if (empty($mageDateTime)) {
            return null;
        }

        return (new \DateTime($mageDateTime))->format(SPL_T_DATETIMECAST);
    }

    /**
     * Convert Splash DateTime to Magento DateTime
     *
     * @param string|null $mageDateTime
     *
     * @return string|null
     */
    public static function toMage(?string $mageDateTime): ?string
    {
        return (new MageDateTime())->formatDate($mageDateTime);
    }
}
