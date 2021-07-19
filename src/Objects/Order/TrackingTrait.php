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

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment\Track;
use Splash\Local\Helpers\ShipmentsHelper;

/**
 * Magento 2 Orders Tracking Fields Access
 */
trait TrackingTrait
{
    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    protected function buildFirstTrackingFields(): void
    {
        //====================================================================//
        // Order Shipping Method
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("title")
            ->name("Shipping Method Name")
            ->microData("http://schema.org/ParcelDelivery", "provider")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipping Method
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("carrier_code")
            ->name("Carrier Code")
            ->microData("http://schema.org/ParcelDelivery", "alternateName")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("track_number")
            ->name("Tracking Number")
            ->microData("http://schema.org/ParcelDelivery", "trackingNumber")
            ->isReadOnly(!ShipmentsHelper::isLogisticModeEnabled())
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
     */
    protected function setFirstTrackingFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // ORDER Currency Data
            //====================================================================//
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
}
