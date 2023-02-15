<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Model;

use Magento\Framework\Model\AbstractModel;
use Deloitte\PayMe\Api\Data\PayMeInterface;

class PayMe extends AbstractModel implements PayMeInterface
{
    /**
     * @var string
     */
    protected $_cacheTag = 'deloitte_payme_history';

    /**
     * @var string
     */
    protected $_eventPrefix = 'deloitte_payme_history';

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(\Deloitte\PayMe\Model\ResourceModel\PayMe::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getTransactions()
    {
        return $this->getData(self::TRANSACTIONS);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->getData(self::STATUS_CODE);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * {@inheritDoc}
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setTransactions($transactions)
    {
        return $this->setData(self::TRANSACTIONS, $transactions);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setStatusCode($statusCode)
    {
        return $this->setData(self::STATUS_CODE, $statusCode);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
