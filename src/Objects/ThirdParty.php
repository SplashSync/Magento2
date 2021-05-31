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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Splash\Local\Helpers\MageHelper;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\GenericFieldsTrait;
use Splash\Models\Objects\IntelParserTrait;

/**
 * Splash PHP Module For Magento 2 - ThirdParty Object Integration
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class ThirdParty extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use GenericFieldsTrait;

    // Core / Common Traits
    use Core\CRUDTrait;
    use Core\ObjectListTrait;

    // Customer Traits
    use ThirdParty\CRUDTrait;
    use ThirdParty\ObjectListTrait;
    use ThirdParty\CoreTrait;
    use ThirdParty\MainTrait;
    use ThirdParty\AddressTrait;
    use ThirdParty\AddressesTrait;

    // Core EAV Parser
    use Core\EavParserTrait;

    //====================================================================//
    // Magento Definition
    //====================================================================//

    /**
     * Magento Model Name
     *
     * @var class-string
     */
    protected static $modelClass = Customer::class;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $repository;

    /**
     * Magento Model
     *
     * @var CustomerData
     */
    protected $object;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     */
    protected static $NAME = "ThirdParty";

    /**
     * Object Description (Translated by Module)
     */
    protected static $DESCRIPTION = "Magento 2 Customer Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO = "fa fa-user";

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
     * Splash Product constructor.
     */
    public function __construct()
    {
        /** @var CustomerRepositoryInterface $repository */
        $repository = MageHelper::getModel(CustomerRepositoryInterface::class);
        $this->repository = $repository;
    }
}
