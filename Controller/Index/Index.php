<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Controller\Index;

use Exception;
use Magento\Checkout\Model\Session AS CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;
    
    /**
     * @var PageFactory
     */
    private $pageFactory;
    
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * 
     * @param Context $context
     * @param CheckoutSession $_checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context         $context,
        CheckoutSession $_checkoutSession,
        RedirectFactory $redirectFactory,
        PageFactory     $pageFactory
    )
    {
        $this->pageFactory = $pageFactory;
        $this->_checkoutSession = $_checkoutSession;
        $this->resultRedirectFactory = $redirectFactory;
        parent::__construct($context);
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        if (empty($this->_checkoutSession->getQuote())) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $resultPage= $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set('Payme');
        return $resultPage;
    }
}