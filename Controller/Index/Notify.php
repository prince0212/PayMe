<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Controller\Index;

use Deloitte\PayMe\Api\PayMeRepositoryInterface;
use Deloitte\PayMe\Model\PayMeFactory;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;

class Notify extends Action
{
    /**
     * @var Quote
     */
    protected $_quote = false;

    /**
     * @var checkoutSession
     */
    protected $_checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var HistoryFactory
     */
    protected $_orderHistoryFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    
    /**
     * @var PayMeRepositoryInterface
     */
    private $payMeRepository;
    
    /**
     * @var PayMeFactory
     */
    private $payMeFactory;

    protected $_logger;
    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param HistoryFactory $_orderHistoryFactory
     * @param PayMeRepositoryInterface $payMeRepository
     * @param PayMeFactory $payMeFactory
     * @param OrderFactory $_orderFactory
     * @throws LocalizedException
     */
    public function __construct(
        Context                  $context,
        CheckoutSession          $checkoutSession,
        CartRepositoryInterface  $quoteRepository,
        HistoryFactory           $_orderHistoryFactory,
        PayMeRepositoryInterface $payMeRepository,
        PayMeFactory             $payMeFactory,
        \Psr\Log\LoggerInterface $logger,
        OrderFactory             $_orderFactory
    )
    {
        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $objectManager = ObjectManager::getInstance();
            $request = $objectManager->get('\Magento\Framework\App\Request\Http');

            if ($request->isPost()) {
                $request->setParam('isAjax', true);
                if (empty($request->getParam('form_key'))) {
                    $formKey = $objectManager->get(FormKey::class);
                    $request->setParam('form_key', $formKey->getFormKey());
                }
            }
        }
        $this->_checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->_orderHistoryFactory = $_orderHistoryFactory;
        $this->payMeFactory = $payMeFactory;
        $this->payMeRepository = $payMeRepository;
        $this->_orderFactory = $_orderFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Set redirect
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute()
    {
        $content = $this->getRequest()->getContent();
        //Custom Logs
        $this->_logger->debug("PayMe: Content: ".print_r($content)); 
        
        $returnArray = json_decode($content, true);
        if(isset($returnArray['orderId'])){
            //Custom Logs
            $this->_logger->debug("PayMe: Return Array: ".print_r($returnArray,true)); 
            $statusCode = $returnArray['statusCode'];
            $statusDescription = $returnArray['statusDescription'];
            $quoteId = $returnArray['orderId'];
            $transactionId = $returnArray['transactions'][0]['transactionId'];
            
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $this->quoteRepository->get($quoteId);
            if ($this->validateStatusCode($statusCode) != 'Completed') {
                $responses = ['status' => false, 'message' => __($statusDescription)];
                $this->createPayMeHistory($quoteId, $transactionId, $returnArray);
                return $this->getResponse()->setBody(json_encode($responses, 1));
            }

            $this->_eventManager->dispatch('payme_payment_success', ['quote' => $quote, 'returnArray' => $returnArray]);
            $responses = ['status' => true, 'message' => __($statusDescription)];
            return $this->getResponse()->setBody(json_encode($responses, 1));
        }else{
            $responses = ['status' => true, 'message' => __('No data')];
            return $this->getResponse()->setBody(json_encode($responses, 1));
        }
    }

    /**
     * @param $quoteId
     * @param $transactionId
     * @param $returnArray
     * @param $status
     * @throws LocalizedException
     */
    private function createPayMeHistory($quoteId, $transactionId, $returnArray)
    {
        //Custom Logs
        $this->_logger->debug("PayMe History Transaction ID: ".$transactionId); 
        $paymeData = $this->payMeFactory->create();
        $paymeData->setQuoteId($quoteId);
        $paymeData->setTransactionId($transactionId);
        $paymeData->setTransactions(json_encode($returnArray));
        $paymeData->setStatus($returnArray['statusDescription']);
        $paymeData->setStatusCode($returnArray['statusCode']);
        $paymeData->setCreatedAt(date("Y-m-d h:i:s"));
        $paymeData->setUpdatedAt(date("Y-m-d h:i:s"));
        $this->payMeRepository->save($paymeData);
    }
    
    /**
     * Validate status code
     * 
     * @param string $statusCode
     * @return string
     */
    private function validateStatusCode($statusCode)
    {
        //Custom Logs
        $this->_logger->debug("Status Code: ".$statusCode); 
        switch ($statusCode):
            case('PR001'):
                return 'Active';
            case('PR004'):
                return 'Aborted';
            case('PR005'):
                return 'Completed';
            case('PR006'):
                return 'CPQR Payment Failure';
            case('PR007'):
                return 'Expired';
        endswitch;
    }
}