<?php

namespace Deloitte\PayMe\Model\ResourceModel;

use Deloitte\PayMe\Api\Data;
use Deloitte\PayMe\Api\PayMeRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Deloitte\PayMe\Model\ResourceModel\PayMe\CollectionFactory as PayMeCollectionFactory;
use Deloitte\PayMe\Model\ResourceModel\PayMe as PayMeResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Deloitte\PayMe\Model\PayMeFactory;

class PayMeRepository implements PayMeRepositoryInterface
{
    
    /**
     * @var Data\PayMeSearchResultInterfaceFactory
     */
    protected $searchResultFactory;
    
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
    
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    
    /**
     * @var Data\PayMeInterfaceFactory
     */
    protected $dataPayMeFactory;
    
    /**
     * @var PayMeCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PayMeResource
     */
    protected $resourceModel;
    
    /**
     * @var PayMeFactory
     */
    private $payMeFactory;

    /**
     * 
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param Data\PayMeInterfaceFactory $dataPayMeFactory
     * @param PayMeCollectionFactory $collectionFactory
     * @param PayMeResource $resourceModel
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        Data\PayMeInterfaceFactory $dataPayMeFactory,
        PayMeCollectionFactory $collectionFactory,
        PayMeResource $resourceModel,
        PayMeFactory $payMeFactory
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPayMeFactory = $dataPayMeFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->payMeFactory = $payMeFactory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function save(Data\PayMeInterface $payMe)
    {
        try {
            $this->resourceModel->save($payMe);
        } catch (\Exception $ex) {
            throw new CouldNotSaveException(__("Unable to save the Front"));
        }
        return $payMe;
    }

    /**
     * {@inheritDoc}
     */
    public function getById($id)
    {
        $payMe = $this->dataPayMeFactory->create();
        $this->resourceModel->load($payMe, $id);
        if (!$payMe->getId()) {
            throw new NoSuchEntityException(__('PayMe with id %1 does not exist', $id));
        }
        return $payMe;
    }

    /**
     * {@inheritDoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        
    }

    /**
     * {@inheritDoc}
     */
    public function deleteById($id)
    {
        try {
            $row = $this->payMeFactory->create()->load($id);
            $row->delete();
        } catch (Exception $ex) {
            throw new Exception(__($ex->getMessage()));
        }
        return true;
    }
}
