<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Block\Index;

use Deloitte\PayMe\Gateway\Request\PayMeAlgo;
use Exception;
use Magento\Framework\HTTP\Header;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session AS CheckoutSession;

class Request extends Template
{
    /**
     * @var PayMeAlgo
     */
    protected $_paymentRequest;
    
    /**
     * @var Repository
     */
    protected $_assetRepo;
    
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    
    /**
     * @var Header
     */
    private $httpHeader;

    /**
     * Initialization
     * 
     * @param Context $context
     * @param PayMeAlgo $_paymentRequest
     * @param Repository $_assetRepo
     * @param CheckoutSession $_checkoutSession
     * @param Header $httpHeader
     * @param array $data
     */
    public function __construct(
        Context $context,
        PayMeAlgo $_paymentRequest,
        Repository $_assetRepo,
        CheckoutSession $_checkoutSession,
        Header $httpHeader,
        array $data = []
    )
    { 
        $this->_paymentRequest = $_paymentRequest;
        $this->_assetRepo = $_assetRepo;
        $this->_checkoutSession = $_checkoutSession;
        $this->httpHeader = $httpHeader;
        parent::__construct($context, $data);
    }

    /**
     * @throws Exception
     */
    public function getQrCode()
    {
        return json_decode($this->_paymentRequest->buildQR($this->getQuote()), true);
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->_assetRepo->getUrl("Deloitte_PayMe::images/payme_logo_color_oneline.png");
    }

    /**
     * @return string
     */
    public function getMobileImage()
    {
        return $this->_assetRepo->getUrl("Deloitte_PayMe::images/paymebutton_round.png");
    }
    public function getMobileImageTop()
    {
        return $this->_assetRepo->getUrl("Deloitte_PayMe::images/mobile_img.svg");
    }
    /**
     * @return string
     */
    public function getScanEventUrl()
    {
        $url= $this->_urlBuilder->getUrl("payme/index/scanevent");
        if (substr($url,-1)=='/'){
            $url = substr($url,0,strlen($url)-1);
        }
        return $url;
    }
    
    /**
     * @return string
     */
    public function getFailureUrl()
    {
        return $this->_urlBuilder->getUrl("payme/index/error");
    }
    
    /**
     * @return string
     */
    public  function getSuccessUrl()
    {
        return $this->_urlBuilder->getUrl("checkout/onepage/success");
    }
    
    /**
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->_urlBuilder->getUrl();
    }
    
    /**
     * @return false|\Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quote = $this->_checkoutSession->getQuote();
        if (empty($quote) || $quote === null) {
            return false;
        }
        return $quote;
    }

    /**
     * Check if mobile user
     *
     * @return bool
     */
    public function isMobileRequest()
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        $isMobile = \Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
        if ($isMobile) {
            return true;
        }
        return false;
    }
}
