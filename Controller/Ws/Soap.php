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
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Splash\Client\Splash;
use Splash\Server\SplashServer;

/**
 * Splash Public Soap Controller
 */
class Soap extends Action implements HttpPostActionInterface, HttpGetActionInterface, CsrfAwareActionInterface
{
    /**
     * @var ResultFactory
     */
    protected $factory;

    /**
     * Soap Action constructor.
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
     * Handle Splash Soap Request
     *
     * @return Raw
     */
    public function execute(): Raw
    {
        //====================================================================//
        // Setup Php Specific Settings
        ini_set('display_errors', "0");
        error_reporting(E_ERROR);
        //====================================================================//
        // Create Raw Response
        /** @var Raw $result */
        $result = $this->factory->create(ResultFactory::TYPE_RAW);
        //====================================================================//
        // Detect SOAP requests send by Splash Server
        $userAgent = Splash::input("HTTP_USER_AGENT");
        if (!$userAgent || (false === strpos($userAgent, "SOAP"))) {
            return $result->setContents("This WebService Provide no Description");
        }
        //====================================================================//
        //   Declare WebService Available Functions
        $corePath = dirname((string) (new \ReflectionClass(SplashServer::class))->getFileName(), 2);
        require_once($corePath."/inc/server.inc.php");
        Splash::log()->deb("Splash Started In Server Mode");
        //====================================================================//
        // Build SOAP Server & Register a method available for clients
        Splash::com()->buildServer();
        //====================================================================//
        // Register shutdown method available for fatal errors retrieval
        register_shutdown_function(array(SplashServer::class, 'fatalHandler'));
        //====================================================================//
        // Clean Output Buffer
        ob_get_clean();
        ob_get_clean();
        //====================================================================//
        // Force UTF-8 Encoding & Protect against Varnish ESI Transform
        if (function_exists('getallheaders') && array_key_exists("X-Varnish", getallheaders())) {
            echo "\xEF\xBB\xBF";
        }
        //====================================================================//
        // Process methods & Return the results.
        Splash::com()->handle();

        return $result;
    }

    /**
     * @param RequestInterface $request
     *
     * @return null|InvalidRequestException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     *
     * @return null|bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
