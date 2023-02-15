<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Model;

class PayMeUnified extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'payme';
    
    protected $_code = 'payme';
}
