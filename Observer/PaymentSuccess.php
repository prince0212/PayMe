<?php

namespace Deloitte\PayMe\Observer;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Deloitte\PayMe\Model\ResourceModel\PayMe\CollectionFactory;
use Deloitte\PayMe\Api\PayMeRepositoryInterface;
use Deloitte\PayMe\Model\PayMeFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class PaymentSuccess implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    
    /**
     * @var QuoteManagement
     */
    private $quoteManagement;
    
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    
    /**
     * @var BuilderInterface
     */
    private $transBuilder;
    
    /**
     * @var InvoiceService
     */
    private $_invoiceService;
    
    /**
     * @var InvoiceSender
     */
    private $invoiceSender;
    
    /**
     * @var TransactionFactory
     */
    private $transactionFactory;
    
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * @var HistoryFactory
     */
    private $_orderHistoryFactory;
    
    /**
     * @var CartManagementInterface
     */
    private $cartManagmentInterface;
    
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepoInterface;
    protected $_logger;

    /**
     * Initialization
     * 
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteManagement $quoteManagement
     * @param BuilderInterface $transBuilder
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param TransactionFactory $transactionFactory
     * @param CollectionFactory $collectionFactory
     * @param HistoryFactory $_orderHistoryFactory
     * @param PayMeRepositoryInterface $payMeRepository
     * @param PayMeFactory $payMeFactory
     * @param CartManagementInterface $cartManagmentInterface
     * @param OrderRepositoryInterface $orderRepoInterface
     */
    public function __construct(
        CheckoutSession          $checkoutSession,
        CartRepositoryInterface  $quoteRepository,
        QuoteManagement          $quoteManagement,
        BuilderInterface         $transBuilder,
        InvoiceService           $invoiceService,
        InvoiceSender            $invoiceSender,
        TransactionFactory       $transactionFactory,
        CollectionFactory        $collectionFactory,
        HistoryFactory           $_orderHistoryFactory,
        PayMeRepositoryInterface $payMeRepository,
        PayMeFactory             $payMeFactory,
        CartManagementInterface $cartManagmentInterface,
        \Psr\Log\LoggerInterface $logger,
        OrderRepositoryInterface $orderRepoInterface
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->transBuilder = $transBuilder;
        $this->_invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->_orderHistoryFactory = $_orderHistoryFactory;
        $this->payMeFactory = $payMeFactory;
        $this->payMeRepository = $payMeRepository;
        $this->cartManagmentInterface = $cartManagmentInterface;
        $this->_logger = $logger;
        $this->orderRepoInterface = $orderRepoInterface;
    }


    /**
     * @param Observer $observer
     * @return PayMeSuccess
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $returnArray = $observer->getData('returnArray');
        //Custom Logs
        $this->_logger->debug("PayMe: Content: ".print_r($returnArray,true)); 

        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $observer->getData('quote');
        
        $transactionId = $returnArray['transactions'][0]['transactionId'];
        $totalAmount = $returnArray['totalAmount'];

        $order = $this->handleOrder($quote);
        
        $this->createPayMeHistory($order->getId(), $transactionId, $returnArray);
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter("quote_id", $quote->getId());
        $collection->addFieldToFilter("status", "SUCCESS");
        //Custom Logs
        $this->_logger->debug("PayMe: Collection Query: ".$collection->getSelect()); 

        if (empty($collection->getData())) {
            $history = $this->_orderHistoryFactory->create()->setStatus("processing")->setComment("Payment was Accepted. Payment Ref: " . $transactionId)->setEntityName('order')->setOrder($order);
            $history->save();
        }

        $this->createTransaction($order, $transactionId, $totalAmount, json_encode($returnArray));
        $this->createOrderInvoice($order);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object|null
     * @throws LocalizedException
     */
    public function handleOrder($quote)
    {
        try {
            //Custom Logs
            $this->_logger->debug("PayMe: Quote ID: ".$quote->getId()); 
            $orderId = $this->cartManagmentInterface->placeOrder($quote->getId());
            //Custom Logs
            $this->_logger->debug("PayMe: Order ID: ".$orderId); 
            $order = $this->orderRepoInterface->get($orderId);
            $order->setStatus(Order::STATE_PROCESSING);
            $order->setState(Order::STATE_PROCESSING);
            $order->save();
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
        return $order;
    }

    /**
     * @param $order
     * @param $transactionId
     * @param $paymentAmount
     * @param $paymentData
     * @return int|void
     */
    private function createTransaction($order, $transactionId, $paymentAmount, $paymentData)
    {
        try {
            //Custom Logs
            $this->_logger->debug("PayMe: Transaction ID: ".$transactionId); 
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setTransactionId($transactionId);
            $payment->setAdditionalInformation(
                [Transaction::RAW_DETAILS => $paymentData]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $paymentAmount
            );

            $message = __('The authorized & captured amount is %1.', $formatedPrice);

            $transaction = $this->transBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => $paymentData]
                )
                ->setFailSafe(true)
                ->build(Transaction::TYPE_PAYMENT);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
        } catch (Exception $e) {
            throw new Exception(__($e->getMessage()));
        }
    }

    /**
     * @param Order $order
     * @throws LocalizedException
     * @throws Exception
     */
    public function createOrderInvoice($order)
    {
        if ($order->canInvoice()) {
            //Custom Logs
            $this->_logger->debug("PayMe: Invoice Creation: "); 
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            //Custom Logs
            $this->_logger->debug("PayMe: Invoice ID: ".$invoice->getId()); 
            //Send Invoice mail to customer
            $order->addStatusHistoryComment(
                __('Notified customer about invoice creation #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->save();
        }
    }
    
    /**
     * 
     * @param int $orderId
     * @param string $transactionId
     * @param [] $returnArray
     */
    public function createPayMeHistory($orderId, $transactionId, $returnArray)
    {
        //Custom Logs
        $this->_logger->debug("Create PayMe History"); 
        $paymeData = $this->payMeFactory->create();
        $paymeData->setQuoteId($returnArray['orderId']);
        $paymeData->setOrderId($orderId);
        $paymeData->setTransactionId($transactionId);
        $paymeData->setTransactions(json_encode($returnArray));
        $paymeData->setStatus($returnArray['statusDescription']);
        $paymeData->setStatusCode($returnArray['statusCode']);
        $paymeData->setCreatedAt(date("Y-m-d h:i:s"));
        $paymeData->setUpdatedAt(date("Y-m-d h:i:s"));
        $this->payMeRepository->save($paymeData);
    }
}
