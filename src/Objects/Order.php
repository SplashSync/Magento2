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

namespace   Splash\Local\Objects;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as MageOrder;
use Splash\Local\Helpers\MageHelper;
use Splash\Local\Helpers\ShipmentsHelper;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\GenericFieldsTrait;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ListsTrait;
use Splash\Models\Objects\PricesTrait;

/**
 * Splash PHP Module For Magento 2 - Order Object Intégration
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Order extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use GenericFieldsTrait;
    use PricesTrait;
    use ListsTrait;

    // Core / Common Traits
    use Core\CRUDTrait;
    use Core\ObjectListTrait;
    use Core\WebsitesTrait;
//    use Core\DataAccessTrait;

    // Order Traits
    use Order\CRUDTrait;
    use Order\ObjectListTrait;
    use Order\CoreTrait;
    use Order\MainTrait;
    use Order\ItemsTrait;
    use Order\StatusTrait;
    use Order\ShippingTrait;
    use Order\TrackingTrait;

    use ThirdParty\AddressTrait;

    // Core EAV Parser
    use Core\EavParserTrait;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var string
     */
    const SHIPPING_LABEL = "__Shipping";

    /**
     * @var string
     */
    const SPLASH_LABEL = "__Splash__";

    //====================================================================//
    // Magento Definition
    //====================================================================//

    /**
     * Magento Model Name
     *
     * @var class-string
     */
    protected static $modelClass = OrderInterface::class;

    /**
     * @var OrderRepositoryInterface
     */
    protected $repository;

    /**
     * Magento Product
     *
     * @var MageOrder
     */
    protected $object;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME = "Customer Order";

    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION = "Magento 2 Customers Order Object";

    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO = "fa fa-shopping-cart ";

    //====================================================================//
    // Object Synchronization Limitations
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//

    /**
     * Allow Creation Of New Local Objects
     *
     * @var bool
     */
    protected static $ALLOW_PUSH_CREATED = false;

    /**
     * Allow Update Of Existing Local Objects
     *
     * @var bool
     */
    protected static $ALLOW_PUSH_UPDATED = false;

    /**
     * Allow Delete Of Existing Local Objects
     *
     * @var bool
     */
    protected static $ALLOW_PUSH_DELETED = false;

    //====================================================================//
    // Object Synchronization Recommended Configuration
    //====================================================================//

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * @var bool
     */
    protected static $ENABLE_PUSH_CREATED = false;

    /**
     * Enable Update Of Existing Local Objects when Modified Remotely
     *
     * @var bool
     */
    protected static $ENABLE_PUSH_UPDATED = false;

    /**
     * Enable Delete Of Existing Local Objects when Deleted Remotely
     *
     * @var bool
     */
    protected static $ENABLE_PUSH_DELETED = false;

    /**
     * Splash Order constructor.
     */
    public function __construct()
    {
        /** @var OrderRepositoryInterface $repository */
        $repository = MageHelper::getModel(OrderRepositoryInterface::class);
        $this->repository = $repository;
        if (ShipmentsHelper::isLogisticModeEnabled()) {
            self::$ALLOW_PUSH_UPDATED = true;
            self::$ENABLE_PUSH_UPDATED = true;
        }
    }
}
