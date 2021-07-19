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
use Magento\Sales\Model\Order;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Helpers\OrderStatusHelper;
use Splash\Local\Helpers\ShipmentsHelper;

/**
 * Magento 2 Order Status Access
 */
trait StatusTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildStatusFields(): void
    {
        //====================================================================//
        // Order State
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("state")
            ->name("Status")
            ->microData("http://schema.org/Order", "orderStatus")
            ->isListed()
            ->addChoices(OrderStatusHelper::getAllChoices())
            ->isReadOnly(!ShipmentsHelper::isLogisticModeEnabled())
            ->isNotTested()
        ;
        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("isValidated")
            ->name("Is Valid")
            ->microData("http://schema.org/OrderStatus", "OrderProcessing")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Processing
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isProcessing")
            ->name("Is Processing")
            ->microData("http://schema.org/OrderStatus", "OrderProcessing")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Closed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isClosed")
            ->name("Is Closed")
            ->microData("http://schema.org/OrderStatus", "OrderDelivered")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("isCanceled")
            ->name("Is Canceled")
            ->microData("http://schema.org/OrderStatus", "OrderCancelled")
            ->group("Meta")
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
    protected function getStatusFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'state':
                $this->out[$fieldName] = OrderStatusHelper::toSplash((string) $this->object->getState());

                break;
            case 'isValidated':
                $this->out[$fieldName] = !$this->object->canEdit();
                ;

                break;
            case 'isProcessing':
                $this->out[$fieldName] = OrderStatusHelper::isProcessing(
                    OrderStatusHelper::toSplash((string) $this->object->getState())
                );

                break;
            case 'isClosed':
                $this->out[$fieldName] = OrderStatusHelper::isDelivered(
                    OrderStatusHelper::toSplash((string) $this->object->getState())
                );

                break;
            case 'isCanceled':
                $this->out[$fieldName] = $this->object->isCanceled();

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
    protected function setStatusFields(string $fieldName, $data): void
    {
        if ('state' != $fieldName || !ShipmentsHelper::isLogisticModeEnabled()) {
            return;
        }
        unset($this->in[$fieldName]);
        //====================================================================//
        // Compare Magento Order State with new Status
        $current = OrderStatusHelper::toSplash((string) $this->object->getState());
        if ($data == $current) {
            return;
        }
        //====================================================================//
        // Update Order State if Required
        try {
            //====================================================================//
            // EXECUTE SYSTEM ACTIONS if Necessary
            $this->doOrderStatusUpdate($this->object, $data);
        } catch (Exception $exception) {
            Splash::log()->errTrace($exception->getMessage());
        }
    }

    /**
     * Try Update of Order Status
     *
     * @param Order  $order
     * @param string $status
     *
     * @throws LocalizedException
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function doOrderStatusUpdate(Order $order, string $status): bool
    {
        //====================================================================//
        // Convert to Magento Status
        $mageStatus = OrderStatusHelper::toMage($status);
        switch ($mageStatus) {
            //====================================================================//
            // Set Order as NEW
            case Order::STATE_NEW:
                return Splash::log()->err("You can't revert an order as new...");
            //====================================================================//
            // Set Order as Processing
            case Order::STATE_PROCESSING:
                //====================================================================//
                // Un Hold Order if Possible
                if ($order->canUnhold()) {
                    $order->unhold()->save();

                    break;
                }
                //====================================================================//
                // Only if Order is New
                if (Order::STATE_NEW == $order->getState()) {
                    $order->setData("is_in_process", 1)->save();

                    break;
                }

                return Splash::log()->war("You can't un hold this order...");
            //====================================================================//
            // Ship the Order
            case Order::STATE_COMPLETE:
                //====================================================================//
                // Order is Waiting for Shipment
                if ($order->canShip()) {
                    //====================================================================//
                    // Create Order Shipping
                    if (!$order->hasShipments()) {
                        ShipmentsHelper::createOrderShipment($order);
                    }
                }
                //====================================================================//
                // Only if Order is Processing
                if (Order::STATE_PROCESSING == $order->getState()) {
                    $order->setState(Order::STATE_COMPLETE);
                    $order->setStatus(Order::STATE_COMPLETE);
                    $order->addStatusHistoryComment('Updated by SplashSync', false);
                    $order->setData("is_in_process", 1);
                    $order->save();

                    break;
                }

                return Splash::log()->err("You can't ship this order");
            case Order::STATE_CLOSED:
                $order->setState(Order::STATE_CLOSED);
                $order->setStatus(Order::STATE_CLOSED);
                $order->addStatusHistoryComment('Updated by SplashSync Module', false);
                $order->save();

                break;
            //====================================================================//
            // Hold Order
            case Order::STATE_HOLDED:
                //====================================================================//
                // Hold Order if Possible
                $order->hold()->save();

                break;
            //====================================================================//
            // Cancel Order
            case Order::STATE_CANCELED:
                if (!$order->canCancel()) {
                    $order->cancel()->save();
                } else {
                    return Splash::log()->err("You can't cancel this order");
                }

                break;
            //====================================================================//
            // Other Order Status
            default:
                if (!empty($mageStatus)) {
                    try {
                        $order->setState($mageStatus);
                        $order->setStatus($mageStatus);
                        $order->addStatusHistoryComment('Updated by SplashSync Module', false);
                        $order->save();
                    } catch (\Throwable $exception) {
                        return Splash::log()->report($exception);
                    }
                }

                break;
        }

        return true;
    }
}
