<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Api\Data;

interface PayMeInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const ORDER_ID          = 'order_id';
    const QUOTE_ID          = 'quote_id';
    const TRANSACTION_ID    = 'transaction_id';
    const TRANSACTIONS      = 'transactions';
    const STATUS            = 'status';
    const STATUS_CODE       = 'status_code';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Order Id
     *
     * @return string
     */
    public function getOrderId();

    /**
     * Get Quote Id
     *
     * @return int
     */
    public function getQuoteId();
    
    /**
     * Get Transaction Id
     *
     * @return string
     */
    public function getTransactionId();
    
    /**
     * Get Transactions
     *
     * @return string
     */
    public function getTransactions();
    
    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();
    
    /**
     * Get Status Code
     *
     * @return string
     */
    public function getStatusCode();
    
    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();
    
    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();
    
    /**
     * Set id
     *
     * @param $id
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Order Id
     *
     * @param $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Set content
     *
     * @param $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);
    
    /**
     * Set transaction id
     *
     * @param $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);
    
    /**
     * Set transactions
     *
     * @param $transactions
     * @return $this
     */
    public function setTransactions($transactions);
    
    /**
     * Set status
     *
     * @param $status
     * @return $this
     */
    public function setStatus($status);
    
    /**
     * Set status
     *
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode);
    
    /**
     * Set created at
     * 
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Set updated at
     *
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

}
