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

namespace Splash\Local\Objects\Order;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment\Track;
use Splash\Client\Splash;
use Splash\Local\Helpers\MageHelper;
use Splash\Local\Helpers\ShipmentsHelper;

/**
 * Magento 2 Orders Tracking Fields Access
 */
trait TrackingTrait
{
    /**
     * Name of Field to use tracking Url Storage
     *
     * @var string
     */
    private static $trackingUrlField = "url_tracking";

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    protected function buildFirstTrackingFields(): void
    {
        //====================================================================//
        // Order Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("track_number")
            ->name("Tracking Number")
            ->microData("http://schema.org/ParcelDelivery", "trackingNumber")
            ->group("First Track")
            ->isReadOnly(!ShipmentsHelper::isLogisticModeEnabled())
            ->setPreferWrite()
        ;
        //====================================================================//
        // Order Shipping Method
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("title")
            ->name("Shipping Method Name")
            ->microData("http://schema.org/ParcelDelivery", "provider")
            ->group("First Track")
            ->isReadOnly(!ShipmentsHelper::isLogisticModeEnabled())
            ->setPreferWrite()
        ;
        //====================================================================//
        // Order Shipping Carrier Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("carrier_code")
            ->name("Carrier Code")
            ->microData("http://schema.org/ParcelDelivery", "alternateName")
            ->group("First Track")
            ->isReadOnly(!ShipmentsHelper::isLogisticModeEnabled())
            ->setPreferWrite()
        ;
        //====================================================================//
        // Order Shipping Carrier Code Formatted
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("carrier_code_snake")
            ->name("Carrier Code Formatted")
            ->description("Carrier Code, converted to lowercase and snake_case formatted")
            ->group("First Track")
            ->isWriteOnly()
        ;
        //====================================================================//
        // Order Tracking Url
        $this->fieldsFactory()->create(SPL_T_URL)
            ->identifier($this->getTrackingUrlField())
            ->name("Tracking Url")
            ->microData("http://schema.org/ParcelDelivery", "trackingUrl")
            ->group("First Track")
            ->isReadOnly(!ShipmentsHelper::isLogisticModeEnabled())
            ->setPreferWrite()
            ->isNotTested()
        ;
    }

    /**
     * Build Fields using FieldFactory
     */
    protected function buildTrackingFields(): void
    {
        //====================================================================//
        // Order Shipping Increment Id
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("increment_id")
            ->inList("tracking")
            ->name("Shipment")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipping Method Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("title")
            ->inList("tracking")
            ->name("Method")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Carrier Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("carrier_code")
            ->inList("tracking")
            ->name("Carrier Code")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("track_number")
            ->inList("tracking")
            ->name("Number")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getTrackingFields(string $key, string $fieldName): void
    {
        $index = 0;
        //====================================================================//
        // Decode Field Name
        $listFieldName = self::lists()->initOutput($this->out, "tracking", $fieldName);
        if (!$listFieldName) {
            return;
        }

        //====================================================================//
        // Fill List with Data
        /** @var Track $track */
        foreach ($this->object->getTracksCollection()->getItems() as $track) {
            //====================================================================//
            // READ Fields
            switch ($listFieldName) {
                //====================================================================//
                // Generic Infos
                case 'title':
                case 'carrier_code':
                case 'track_number':
                    $value = $track->getData($listFieldName);

                    break;
                case 'increment_id':
                    try {
                        $value = $track->getShipment()->getIncrementId();
                    } catch (LocalizedException $e) {
                        $value = null;
                    }

                    break;
                default:
                    return;
            }
            //====================================================================//
            // Do Fill List with Data
            self::lists()->insert($this->out, "tracking", $fieldName, $index, $value);
            $index++;
        }
        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getFirstTrackingFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Generic Infos
            case 'title':
            case 'carrier_code':
            case 'track_number':
            case $this->getTrackingUrlField():
                //====================================================================//
                // Load First Order Tracking Collection
                /** @var Track $track */
                $track = $this->object->getTracksCollection()->getFirstItem();
                $this->out[$fieldName] = $track->getEntityId() ? $track->getData($fieldName) : null;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $data      Field Data
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setFirstTrackingFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'title':
            case 'carrier_code':
            case $this->getTrackingUrlField():
                $this->setFirstTrackingField($fieldName, $data);

                break;
            case 'carrier_code_snake':
                //====================================================================//
                // Convert Data to Snake Case
                $data = str_replace(" ", "_", strtolower($data));
                $this->setFirstTrackingField('carrier_code', $data);

                break;
            case 'track_number':
                if (!empty($data) && is_string($data)) {
                    ShipmentsHelper::setOrderTrackingNumber($this->object, $data);
                }

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Detect Name of Field Used for Tracking Url
     *
     * @return string
     */
    private function getTrackingUrlField(): string
    {
        static $trackingUrlField;

        if (!isset($trackingUrlField)) {
            $trackingUrlField = MageHelper::getConfig('splashsync/sync/tracking_url_field') ?: "url_tracking";
        }

        return $trackingUrlField;
    }

    /**
     * Write A Specific First Tracking Item Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $data      Field Data
     *
     * @return void
     */
    private function setFirstTrackingField(string $fieldName, $data): void
    {
        //====================================================================//
        // Filter Empty Values
        if (empty($data) || !is_string($data)) {
            return;
        }
        //====================================================================//
        // Load First Order Tracking Collection
        /** @var null|Track $track */
        $track = $this->object->getTracksCollection()->getFirstItem();
        if (!$track || ($track->getData($fieldName) == $data)) {
            return;
        }
        //====================================================================//
        // Update Tracking Number
        try {
            $track->setData($fieldName, $data);
            $track->save();
        } catch (Exception $exception) {
            Splash::log()->err($exception->getMessage());
        }
    }
}
