<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Gateway\Request;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class CreateRequest
{
    const EFFECTIVE_DURATION = 600;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var LayoutFactory
     */
    private $resultLayoutFactory;
    protected $_logger;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepository $categoryRepository
     * @param LayoutFactory $resultLayoutFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        CartRepositoryInterface    $quoteRepository,
        StoreManagerInterface      $storeManager,
        ProductRepositoryInterface $productRepository,
        CategoryRepository         $categoryRepository,
        LayoutFactory              $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        UrlInterface               $urlBuilder
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_logger = $logger;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createPaymentRequest($quote)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $isMobileRequest = $resultLayout->getLayout()->createBlock('Deloitte\PayMe\Block\Index\Request')->isMobileRequest();
        $requestBody = [
            'totalAmount' => number_format((float)$quote->getGrandTotal(), 2, '.', ''),
            'currencyCode' => $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
            'effectiveDuration' => self::EFFECTIVE_DURATION,
            'notificationUri' => $this->urlBuilder->getUrl('payme/index/notify'),
            "merchantData" => [
                "orderId" => $quote->getQuoteId(),
                "orderDescription" => "",
                "additionalData" => "",
            ]
        ];
        //Custom Logs
        if ($isMobileRequest) {
            $requestBody = array_merge($requestBody, array('appSuccessCallback' => $this->urlBuilder->getUrl('checkout/onepage/success'), 'appFailCallback' => $this->urlBuilder->getUrl('payme/index/error')));
        }
        $this->_logger->debug("PayMe: Payment Request:".print_r($requestBody,true)); 
        return [$requestBody, json_encode($requestBody)];
    }

    /**
     * @param $quoteId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getShoppingCart($quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $productData = [];
        /** @var Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            list($categoryName1, $categoryName2, $categoryName3) = $this->getCategories($quoteItem->getProduct());
            $productData[] = array(
                'category1' => $categoryName1,
                'category2' => $categoryName2,
                'category3' => $categoryName3,
                'quantity' => $quoteItem->getQty(),
                'price' => number_format($quoteItem->getPriceInclTax(), 2, '.', ''),
                'name' => $quoteItem->getName(),
                'sku' => $quoteItem->getSku(),
                'currencyCode' => $this->storeManager->getStore()->getCurrentCurrency()->getCode()
            );
        }
        return $productData;
    }

    /**
     * @param $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCategories($product)
    {
        $categoryName1 = $categoryName2 = $categoryName3 = '';
        $productData = $this->productRepository->getById($product->getEntityId());
        $productCategories = $productData->getCategoryIds();
        $categoryArray = [];
        if (count($productCategories) > 0) {
            foreach ($productCategories as $category) {
                $categoryObj = $this->categoryRepository->get($category);
                $categoryArray[] = $categoryObj->getName();
            }
        }
        if (!empty($categoryArray[0])) {
            $categoryName1 = $categoryArray[0];
        }
        if (!empty($categoryArray[1])) {
            $categoryName2 = $categoryArray[1];
        }
        if (!empty($categoryArray[2])) {
            $categoryName3 = $categoryArray[2];
        }
        return [$categoryName1, $categoryName2, $categoryName3];
    }
}