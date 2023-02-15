<?php

namespace Deloitte\PayMe\Model\Data;

use \Magento\Framework\Model\AbstractModel;

class PayMe extends AbstractModel  implements \Deloitte\PayMe\Api\Data\PayMeInterface
{
    /**
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('Deloitte\PayMe\Model\ResourceModel\PayMe');
    }
    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->get(self::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderId()
    {
        return $this->get(self::ORDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteId()
    {
        return $this->get(self::QUOTE_ID);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getTransactionId()
    {
        return $this->get(self::TRANSACTION_ID);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getTransactions()
    {
        return $this->get(self::TRANSACTIONS);
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
        return $this->get(self::CREATED_AT);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->get(self::UPDATED_AT);
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
