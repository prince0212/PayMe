<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;

class Error extends Action
{
    protected $_messageManager;
    
    public function __construct(
        Context $context,
        ManagerInterface $_messageManager
    ) {
        parent::__construct($context);
        $this->_messageManager = $_messageManager;
    }
    public function execute()
    {
        $this->_messageManager->addErrorMessage(__('Payment failed!'));
        $this->_redirect('checkout/cart');
    }
}
