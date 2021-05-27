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

namespace SplashSync\Magento2\Controller\Ws;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Splash\Client\Splash;

/**
 * Test Splash Server Connexion
 */
class Test extends Action
{
    /**
     * @var ResultFactory
     */
    protected $factory;

    /**
     * Test constructor.
     *
     * @param Context       $context
     * @param ResultFactory $resultFactory
     */
    public function __construct(Context $context, ResultFactory $resultFactory)
    {
        parent::__construct($context);
        $this->factory = $resultFactory;
    }

    /**
     * @return Raw
     */
    public function execute(): Raw
    {
        //====================================================================//
        // Create Raw Response
        /** @var Raw $result */
        $result = $this->factory->create(ResultFactory::TYPE_RAW);
        //====================================================================//
        // Security Check
        $nodeId = $this->getRequest()->getParam('node');
        if (!$nodeId || $nodeId !== Splash::configuration()->WsIdentifier) {
            return $result->setContents("This WebService Provide no Description");
            ;
        }
        //====================================================================//
        // Execute Ping Request
        if (Splash::ping()) {
            //====================================================================//
            // Execute Connect Request
            Splash::connect();
        }
        //====================================================================//
        // Output Module Complete Log
        $html = Splash::log()->getHtmlLogList();

        return $result->setContents($html);
    }
}
