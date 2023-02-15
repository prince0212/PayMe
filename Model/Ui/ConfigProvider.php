<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Deloitte\PayMe\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Deloitte\PayMe\Gateway\Http\Client\ClientMock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payme';
    
    const LOGO = 'payme/';


    protected $scopeConfig;
    
    protected $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        ClientMock::SUCCESS => __('Success'),
                        ClientMock::FAILURE => __('Fraud')
                    ],
                    'logo' => $this->getLogo()
                ]
            ]
        ];
    }
    
    /**
     * Get logo url from config
     *
     * @param string $code
     *
     * @return string
     */
    protected function getLogo()
    {
        $logoUrl = false;
        $logoPath = $this->scopeConfig->getValue('payment/payme/logo',ScopeInterface::SCOPE_STORE);
        if (!empty($logoPath)) {
            $fileUrl = self::LOGO.$logoPath;
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $logoUrl = $mediaUrl.$fileUrl;
        }
        
        return $logoUrl;
    }
}
