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

use Magento\Directory\Model\Currency;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Invoice as MageInvoice;
use Splash\Local\Helpers\MageHelper;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\GenericFieldsTrait;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ListsTrait;
use Splash\Models\Objects\PricesTrait;

/**
 * Splash PHP Module For Magento 2 - Invoice Object Integration
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Invoice extends AbstractObject
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

    // Orders Traits
    use Order\ObjectListTrait;

    // Invoices Traits
    use Invoice\CoreTrait;
    use Invoice\CRUDTrait;
    use Order\MainTrait;
    use Invoice\ItemsTrait;
//    use Order\StatusTrait;

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
    protected static $modelClass = InvoiceInterface::class;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $repository;

    /**
     * Magento Product
     *
     * @var MageInvoice
     */
    protected $object;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME = "Customer Invoice";

    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION = "Magento 2 Customers Invoice Object";

    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO = "fa fa-money";

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
        /** @var InvoiceRepositoryInterface $repository */
        $repository = MageHelper::getModel(InvoiceRepositoryInterface::class);
        $this->repository = $repository;
    }

    /**
     * Get Currency
     *
     * @return null|Currency
     */
    protected function getCurrency(): ?Currency
    {
        return $this->object->getOrder()->getOrderCurrency();
    }
}
