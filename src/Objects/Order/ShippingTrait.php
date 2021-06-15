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

use Magento\Sales\Model\Order\Shipment;
use Splash\Local\Helpers\ShipmentsHelper;

/**
 * Magento 2 Order Shipping Fields Access
 */
trait ShippingTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildShippingFields(): void
    {
        //====================================================================//
        // Order Shipment ID
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("increment_id")
            ->name("Shippment ID")
            ->inList("shipping")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipment Total Qty
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("total_qty")
            ->name("Total Qty")
            ->description("Total Qty of Items Shipped")
            ->inList("shipping")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipment Total Weight
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("total_weight")
            ->name("Total Weight")
            ->description("Total Weight of Items Shipped")
            ->inList("shipping")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipment Tracking Codes
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("tracking_codes")
            ->name("Tracking Codes")
            ->inList("shipping")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipment Tracking Titles
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("tracking_titles")
            ->name("Tracking Titles")
            ->inList("shipping")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipment Tracking Numbers
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("tracking_numbers")
            ->name("Tracking Numbers")
            ->inList("shipping")
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
    protected function getShippingFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "shipping", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify List is Not Empty
        $collection = $this->object->getShipmentsCollection();
        $shipments = $collection ? $collection->getItems() : null;
        if (!is_array($shipments)) {
            return;
        }
        //====================================================================//
        // Fill List with Data
        /** @var  Shipment $shipment */
        foreach ($shipments as $index => $shipment) {
            //====================================================================//
            // Do Fill List with Data
            self::lists()->insert(
                $this->out,
                "shipping",
                $fieldName,
                $index,
                self::getShipmentValues($shipment, $fieldId)
            );
        }
        unset($this->in[$key]);
    }

    /**
     * Read Shipment Informations
     *
     * @param Shipment $shipment
     * @param string   $fieldId
     *
     * @return null|float|int|string
     */
    private static function getShipmentValues(Shipment $shipment, string $fieldId)
    {
        switch ($fieldId) {
            case 'increment_id':
                return $shipment->getIncrementId();
            case 'total_qty':
                return (int) $shipment->getTotalQty();
            case 'total_weight':
                return $shipment->getTotalWeight();
            case 'tracking_codes':
                return ShipmentsHelper::getTrackingInlineField($shipment, "carrier_code");
            case 'tracking_titles':
                return ShipmentsHelper::getTrackingInlineField($shipment, "title");
            case 'tracking_numbers':
                return ShipmentsHelper::getTrackingInlineField($shipment, "track_number");
            default:
                return null;
        }
    }
}
