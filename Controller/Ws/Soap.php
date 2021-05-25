<?php
namespace SplashSync\Magento2\Controller\Ws;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Splash\Client\Splash;
use Splash\Server\SplashServer;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * Splash Public Soap Controller
 */
class Soap extends Action implements HttpPostActionInterface, HttpGetActionInterface, CsrfAwareActionInterface {

    /**
     * @var RawFactory
     */
    protected $factory;


    /**
     * Soap Action constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(Context $context, RawFactory $resultRawFactory)
    {
        $this->factory    = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * Handle Splash Soap Request
     *
     * @return void
     */
    public function execute()
    {
        //====================================================================//
        // Setup Php Specific Settings
        ini_set('display_errors', "0");
        error_reporting(E_ERROR);
        //====================================================================//
        // Create Raw Response
        $result = $this->factory->create();
        //====================================================================//
        // Detect SOAP requests send by Splash Server
        $userAgent = Splash::input("HTTP_USER_AGENT");
        if (!$userAgent || (false === strpos($userAgent, "SOAP"))) {
            return $result->setContents("This WebService Provide no Description");
        }
        //====================================================================//
        //   Declare WebService Available Functions
        $corePath = dirname((new \ReflectionClass(SplashServer::class))->getFileName(), 2);
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
    }

    /**
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
