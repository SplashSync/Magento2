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
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order;
use Splash\Core\SplashCore as Splash;
use Splash\Local\Helpers\MageHelper;
use Splash\Local\Helpers\OrderStatusHelper;
use Splash\Local\Helpers\ShipmentsHelper;
use Splash\Local\Objects\Order as SplashOrder;
use Splash\Models\Objects\Order\Status as SplashStatus;
use Splash\Tests\Tools\ObjectsCase;

/**
 * Local Objects Test Suite - Test Update of Orders Statuses.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class L51OrderStatusUpdatesTest extends ObjectsCase
{
    /**
     * Test Creation of a Basic Order
     *
     * @throws Exception
     *
     * @return void
     */
    public function testCreateOrder()
    {
        $this->assertTrue(ShipmentsHelper::isLogisticModeEnabled());
        //====================================================================//
        // Create a New Order
        $customer = $this->getOrCreateCustomer();
        $this->getOrCreateAddress($customer);
        $order = $this->createOrder($customer, $this->getOrCreateProduct());
        //====================================================================//
        // Verify New Order
        $this->assertNotEmpty($order->getEntityId());
        $this->assertEquals(Order::STATE_NEW, $order->getState());
        $this->assertEquals("OrderDraft", OrderStatusHelper::toSplash((string) $order->getState()));
        //====================================================================//
        // Load Splash Order Manager
        /** @var SplashOrder $splashOrder */
        $splashOrder = Splash::object("Order");
        $this->assertInstanceOf(SplashOrder::class, $splashOrder);

        //====================================================================//
        // Mark Order as Processing
        $this->assertNotEmpty(
            $splashOrder->set($order->getId(), array("state" => SplashStatus::PROCESSING))
        );
        //====================================================================//
        // Verify Order
        $processingOrder = $splashOrder->load($order->getId());
        $this->assertInstanceOf(Order::class, $processingOrder);
        $this->assertEquals(Order::STATE_PROCESSING, $processingOrder->getState());

        //====================================================================//
        // Mark Order as In Transit
        $trackingNumber = uniqid("track_");
        $this->assertNotEmpty(
            $splashOrder->set($order->getId(), array(
                "state" => SplashStatus::IN_TRANSIT,
                "track_number" => $trackingNumber,
            ))
        );
        //====================================================================//
        // Verify Order
        $completeOrder = $splashOrder->load($order->getId());
        $this->assertInstanceOf(Order::class, $completeOrder);
        $this->assertEquals(Order::STATE_COMPLETE, $completeOrder->getState());
        //====================================================================//
        // Verify Order Tracking Number
        $trackingOrder = $splashOrder->get($order->getId(), array("track_number"));
        $this->assertIsArray($trackingOrder);
        $this->assertArrayHasKey("track_number", $trackingOrder);
        $this->assertEquals($trackingNumber, $trackingOrder["track_number"]);

        //====================================================================//
        // Mark Order as Delivered
        $this->assertNotEmpty(
            $splashOrder->set($order->getId(), array("state" => SplashStatus::DELIVERED))
        );
        //====================================================================//
        // Verify Order
        $deliveredOrder = $splashOrder->load($order->getId());
        $this->assertInstanceOf(Order::class, $deliveredOrder);
        $this->assertEquals(Order::STATE_CLOSED, $deliveredOrder->getState());
    }

    /**
     * Create Customer Order
     *
     * @param Customer $customer
     * @param Product  $product
     *
     * @throws LocalizedException
     *
     * @return Order
     */
    private function createOrder(Customer $customer, Product $product): Order
    {
        /**
         * @var Quote $quote
         * @phpstan-ignore-next-line
         */
        $quote = MageHelper::getModel("Magento\\Quote\\Model\\QuoteFactory")->create();
        $quote
            ->setWebsite(MageHelper::getDefaultNewWebsite())
            ->setStore(MageHelper::getDefaultNewStore())
            ->setCurrency()
            ->assignCustomer($customer->getDataModel())
            ->save()
        ;
        //====================================================================//
        // Setup Shipping Address
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('freeshipping_freeshipping')
            ->setShippingDescription('This is an fake!')
        ;
        //====================================================================//
        // Set Sales Order Payment
        $quote->getPayment()->importData(array('method' => 'checkmo'));
        //====================================================================//
        // Add Product to Order
        $quoteItem = $quote->addProduct($product, (int) rand(3, 10));
        $this->assertIsObject($quoteItem);
        $quoteItem->calcRowTotal();
        $quote->collectTotals()->save();

        //====================================================================//
        // Convert to Order
        /** @var QuoteManagement $quoteManager */
        $quoteManager = MageHelper::getModel(QuoteManagement::class);
        $order = $quoteManager->submit($quote);
        $this->assertInstanceOf(Order::class, $order);

        return $order;
    }

    /**
     * Load of Create Customer
     *
     * @throws Exception
     *
     * @return Customer
     */
    private function getOrCreateCustomer(): Customer
    {
        /** @var Customer $model */
        $model = MageHelper::createModel(Customer::class);
        //====================================================================//
        // Load Customer By Email
        $customer = $model
            ->setWebsiteId(MageHelper::getDefaultNewWebsite()->getId())
            ->loadByEmail("test@splashsync.com")
        ;
        if (!$customer->getEntityId()) {
            //====================================================================//
            // If not available => Create this Customer
            $customer
                ->setStore(MageHelper::getDefaultNewStore())
                ->setData("lastname", "Sync")
                ->setData("firstname", "Splash")
                ->setData("email", "test@splashsync.com")
                ->setPassword("test@splashsync.com");
            $customer->save();
        }

        return $customer;
    }

    /**
     * Load of Create Customer Address
     *
     * @param Customer $customer
     *
     * @throws Exception
     *
     * @return Address
     */
    private function getOrCreateAddress(Customer $customer): Address
    {
        $address = $customer->getDefaultShippingAddress();
        if (empty($address)) {
            //====================================================================//
            // Create Empty Customer Address
            /** @var Address $address */
            $address = MageHelper::createModel(Address::class);
            $address->setParentId($customer->getEntityId());
            $address->setData("customer_id", $customer->getEntityId());
            $address->setData("firstname", "Splash");
            $address->setData("lastname", "Sync");
            $address->setData("street", "123 Av des Champs Elysées");
            $address->setData("postcode", "75000");
            $address->setData("city", "Paris");
            $address->setData("country_id", "FR");
            $address->setData("telephone", "0606060606");
            $address->save();
            //====================================================================//
            // Register as Customer Address
            $customer
                ->setDefaultShipping($address->getId())
                ->setDefaultBilling($address->getId())
                ->save()
            ;
        }

        return $address;
    }

    /**
     * Load of Create Test Product
     *
     * @throws Exception
     *
     * @return Product
     */
    private function getOrCreateProduct(): Product
    {
        /** @var ProductRepository $repository */
        $repository = MageHelper::createModel(ProductRepository::class);

        try {
            /** @var Product $product */
            $product = $repository->get("SPLASH-TEST");
        } catch (Exception $exception) {
            /** @var Product $product */
            $product = MageHelper::createModel(Product::class);
        }

        if (!$product->getEntityId()) {
            //====================================================================//
            // Init Product Class
            $product
                // Setup Product Status
                ->setStatus(1)
                ->setPrice(12.33)
                ->setData("tax_class_id", 0)
                ->setWeight(1)
                ->setVisibility(Product\Visibility::VISIBILITY_BOTH)
                // Setup Product Attribute Set
                ->setAttributeSetId(MageHelper::getStoreConfig('splashsync/sync/attribute_set'))
                // Setup Product Type => Always Simple when Created formOutside Magento
                ->setTypeId("simple")
                // Setup Product SKU & Name
                ->setSku("SPLASH-TEST")
                ->setName("Sample - Just for Testing")
                // Setup Website
                ->setData("website_id", MageHelper::getDefaultNewWebsite()->getId())
                ->setData("store_id", MageHelper::getDefaultNewStore()->getId())
                ->save()
            ;
        }

        $product->setQuantityAndStockStatus(array(
            'qty' => (int) 1000,
            'is_in_stock' => 1,
        ))->save();

        return $product;
    }
}
