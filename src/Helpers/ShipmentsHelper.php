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
use Magento\Sales\Model\Convert\Order as OrderConverter;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\Shipping\Model\ShipmentNotifier;
use Splash\Client\Splash;
use Splash\Models\Helpers\InlineHelper;
use Throwable;

/**
 * Manage Shipments Operations
 */
class ShipmentsHelper
{
    /**
     * Check if Logistic Mode is Enabled
     *
     * @return bool
     */
    public static function isLogisticModeEnabled(): bool
    {
        /** @var null|bool $isEnabled */
        static $isEnabled;

        if (!isset($isEnabled)) {
            $isEnabled = !empty(MageHelper::getStoreConfig("splashsync/sync/logistic"));
        }

        return $isEnabled;
    }

    /**
     * Create Shipment for an Order
     *
     * @param Order $order
     *
     * @return null|Shipment
     */
    public static function createOrderShipment(Order $order): ?Shipment
    {
        //====================================================================//
        // Check if order can be shipped or has already shipped
        if (! $order->canShip()) {
            return null;
        }

        try {
            //====================================================================//
            // Initialize the order shipment object
            /** @var OrderConverter $convertOrder */
            $convertOrder = MageHelper::createModel(OrderConverter::class);
            $shipment = $convertOrder->toShipment($order);
            //====================================================================//
            // Loop through order items
            foreach ($order->getAllItems() as $orderItem) {
                //====================================================================//
                // Check if order item has qty to ship or is virtual
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                //====================================================================//
                // Create shipment item with qty
                $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)
                    ->setQty($orderItem->getQtyToShip())
                ;
                //====================================================================//
                // Add shipment item to shipment
                $shipment->addItem($shipmentItem);
            }
            //====================================================================//
            // Register shipment
            $shipment->register();
            //====================================================================//
            // Save created shipment and order
            $shipment->save();
            $shipment->getOrder()->save();
            Splash::log()->war("Order Shipment Created : ".$shipment->getIncrementId());
            //====================================================================//
            // Add Shipment to Order
            /** @var Collection $shipmentsCollection */
            $shipmentsCollection = $order->getShipmentsCollection();
            $shipmentsCollection->addItem($shipment);

            //====================================================================//
            // Send email
            try {
                /** @var ShipmentNotifier $notifier */
                $notifier = MageHelper::createModel(ShipmentNotifier::class);
                $notifier->notify($shipment);
                //====================================================================//
                // Save Shipment
                $shipment->save();
            } catch (Throwable $throwable) {
                Splash::log()->err($throwable->getMessage());
            }

            return $shipment;
        } catch (Exception $exception) {
            Splash::log()->err($exception->getMessage());
        }

        return null;
    }

    /**
     * Set Order Shipment first Tracking Number
     *
     * @param Order  $order
     * @param string $trackingNumber
     *
     * @return bool
     */
    public static function setOrderTrackingNumber(Order $order, string $trackingNumber): bool
    {
        //====================================================================//
        // Ensure at Least One Shipment Exists
        if (!$order->hasShipments()) {
            return Splash::log()->err("Unable to set tracking number... Order has no Shipment!");
        }
        //====================================================================//
        // Load Order First Shipment
        /** @var Collection $shipmentsCollection */
        $shipmentsCollection = $order->getShipmentsCollection();
        /** @var Shipment $shipment */
        $shipment = $shipmentsCollection->getFirstItem();
        //====================================================================//
        // If Shipment has No Tracking Number
        if (0 == $shipment->getTracksCollection()->count()) {
            return self::addTrackingToShipment($shipment, $trackingNumber);
        }

        return self::updateTrackingInShipment($shipment, $trackingNumber);
    }

    /**
     * Extract Tracking Info from Shipment as Inline String
     *
     * @param Shipment $shipment
     * @param string   $fieldId
     *
     * @return string
     */
    public static function getTrackingInlineField(Shipment $shipment, string $fieldId): string
    {
        /** @var Track[] $tracks */
        $tracks = $shipment->getAllTracks();
        $value = array();
        foreach ($tracks as $track) {
            $value[] = $track->getData($fieldId);
        }

        return InlineHelper::fromArray($value);
    }

    /**
     * Set Order Shipping Method
     *
     * @param Order $order
     *
     * @return null|string
     */
    public static function getOrderShippingMethod(Order $order): ?string
    {
        $method = $order->getShippingMethod(true);
        if (is_object($method)) {
            return (string) $method->getData("method");
        }

        return null;
    }

    /**
     * Get Order Shipping Method Carrier Code
     *
     * @param Order $order
     *
     * @return null|string
     */
    public static function getOrderCarrierCode(Order $order): ?string
    {
        $method = $order->getShippingMethod(true);
        if (is_object($method)) {
            return (string) $method->getData("carrier_code");
        }

        return null;
    }

    /**
     * Add Tracking Number to a Shipment
     *
     * @param Shipment $shipment
     * @param string   $trackingNumber
     *
     * @return bool
     */
    private static function addTrackingToShipment(Shipment $shipment, string $trackingNumber): bool
    {
        //====================================================================//
        // Create Track
        /**
         * @var Track $track
         * @phpstan-ignore-next-line
         */
        $track = MageHelper::getModel("Magento\\Sales\\Model\\Order\\Shipment\\TrackFactory")->create();
        $track
            ->setCarrierCode((string) self::getOrderCarrierCode($shipment->getOrder()))
            ->setTitle((string) $shipment->getOrder()->getShippingDescription())
            ->setTrackNumber($trackingNumber)
        ;
        //====================================================================//
        // Add Track to Shipment
        try {
            $shipment->addTrack($track);
            $shipment->save();
        } catch (Exception $exception) {
            return Splash::log()->err($exception->getMessage());
        }

        return true;
    }

    /**
     * Update Shipment First Tracking Number
     *
     * @param Shipment $shipment
     * @param string   $trackingNumber
     *
     * @return bool
     */
    private static function updateTrackingInShipment(Shipment $shipment, string $trackingNumber): bool
    {
        //====================================================================//
        // Get first Track of Shipment
        /** @var Track $track */
        $track = $shipment->getTracksCollection()->getFirstItem();
        //====================================================================//
        // Tracking Number Should Exists Here
        if (empty($track->getId())) {
            return Splash::log()->err("No tracking number already added... should never happen.");
        }
        //====================================================================//
        // Compare Tracking Numbers
        if ($track->getTrackNumber() == $trackingNumber) {
            return true;
        }
        //====================================================================//
        // Update Tracking Number
        try {
            $track->setTrackNumber($trackingNumber);
            $track->save();
        } catch (Exception $exception) {
            Splash::log()->err($exception->getMessage());
        }

        return true;
    }
}
