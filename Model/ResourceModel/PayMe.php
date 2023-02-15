<?php

namespace Deloitte\PayMe\Model\ResourceModel;

class PayMe extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('deloitte_payme_history', 'id');
    }
}
