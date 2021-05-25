<?php
namespace SplashSync\Magento2\Controller\Ws;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\UrlFactory;
use Splash\Client\Splash;
use Splash\Server\SplashServer;
use Magento\Framework\Controller\AbstractResult;

/**
 * Test Splash Server Connexion
 */
class Test extends Action {

    /**
     * @var RawFactory
     */
    protected $factory;

    /**
     * Test constructor.
     * @param Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(Context $context, RawFactory $resultRawFactory)
    {
        $this->factory    = $resultRawFactory;

        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function execute(): Raw
    {
        //====================================================================//
        // Create Raw Response
        $result = $this->factory->create();
        //====================================================================//
        // Security Check
        $nodeId = $this->getRequest()->getParam('node');
        if (!$nodeId || $nodeId !== Splash::configuration()->WsIdentifier) {
            return $result->setContents("This WebService Provide no Description");;
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
