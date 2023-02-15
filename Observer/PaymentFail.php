<?php

namespace Deloitte\PayMe\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Deloitte\PayMe\Model\ResourceModel\PayMe\CollectionFactory AS PayMeCollectionFactory;
use Deloitte\PayMe\Api\PayMeRepositoryInterface;

class PaymentFail implements ObserverInterface
{   
    private $payMeRepository;
    
    private $payMeCollectionFactory;
    
    public function __construct(
        PayMeCollectionFactory $payMeCollectionFactory,
        PayMeRepositoryInterface $payMeRepository
    )
    {
        $this->payMeCollectionFactory = $payMeCollectionFactory;
        $this->payMeRepository = $payMeRepository;
    }


    /**
     * @param Observer $observer
     * @return PayMeSuccess
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $quoteId = $observer->getData('quote_id');
        $collection = $this->payMeCollectionFactory->create();
        $collection->addFieldToFilter("quote_id", $quoteId);
        if (empty($collection->getData())) {
            return $this;
        }
        $this->deleteFailHistory($collection);
        return $this;
    }
    
    /**
     * 
     * @param $paymeFailedHistory
     */
    private function deleteFailHistory($paymeFailedHistory)
    {
        $id = null;
        foreach ($paymeFailedHistory->getData() as $data) {
            $id = $data['id'];
        }
        if (!empty($id)) {
            $this->payMeRepository->deleteById($id);
        }
        
    }
}
