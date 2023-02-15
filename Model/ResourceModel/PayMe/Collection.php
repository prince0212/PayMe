<?php


namespace Deloitte\PayMe\Model\ResourceModel\PayMe;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Deloitte\PayMe\Model\PayMe', 'Deloitte\PayMe\Model\ResourceModel\PayMe');
    }
}
