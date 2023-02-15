<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Deloitte\PayMe\Model\ResourceModel\PayMe\CollectionFactory;
use Deloitte\PayMe\Helper\ApiConfig;
use Magento\Checkout\Model\Session AS CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;

class ScanEvent extends Action
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;
    
    /**
     * @var ApiConfig
     */
    protected $_apiConfig;
    
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;
    protected $_logger;

    /**
     * Initialization
     * 
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param ApiConfig $_apiConfig
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepo
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        ApiConfig $_apiConfig,
        CheckoutSession $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        OrderRepositoryInterface $orderRepo
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_apiConfig = $_apiConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->orderRepo = $orderRepo;
        $this->_logger = $logger;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('cart_id') ;
        //Custom Logs
        $this->_logger->debug("PayMe: Quote ID: ".$quoteId); 
        $responses = ['status' => 'no', 'message' => ''];
        if (empty($quoteId)) {
            return $this->getResponse()->setBody(json_encode($responses));
        }
        
        $transactionId = null;
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('quote_id', $quoteId);
        //Custom Logs
        $this->_logger->debug("PayMe: Collection Query:".$collection->getSelect()); 
        
        if (empty($collection->getData())) {
            return $this->getResponse()->setBody(json_encode($responses));
        }
        
        $status = null;
        $statusCode = null;
        $orderId = null;
        foreach ($collection->getData() as $data) {
            if (empty($data['status_code'])) {
                continue;
            }
            
            $transactionId = $data['transaction_id'];
            $status = $data['status'];
            $statusCode =  $data['status_code'];
            $orderId = $data['order_id'];
        }
        //Custom Logs
        $this->_logger->debug("PayMe: Transaction ID: ".$transactionId); 
        $this->_logger->debug("PayMe: Order ID: ".$orderId); 
        $this->_logger->debug("PayMe: Status Code: ".$statusCode); 

        if($transactionId && $orderId && $statusCode == 'PR005') {
            $this->_updateSession($orderId);
            $responses = ['status'=>'ok', 'message' => __($status)];
            return $this->getResponse()->setBody(json_encode($responses));
        }
        
        $failResponse = ['status'=>'fail', 'message' => __($status)];
        $this->_eventManager->dispatch('payme_payment_fail', ['quote_id' => $quoteId]);
        return $this->getResponse()->setBody(json_encode($failResponse));   
    }
    
    /**
     * 
     * @param int $orderId
     */
    protected function _updateSession($orderId)
    {
        //Custom Logs
        $this->_logger->debug("Updated Session Code: ".$orderId); 
        $order =  $this->orderRepo->get($orderId);
        $this->_checkoutSession->setLastQuoteId($order->getQuoteId())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());
    }
}
