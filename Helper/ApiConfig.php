<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class ApiConfig extends AbstractHelper
{
    const XML_IS_ENABLE = 'payment/payme/active';
    const XML_DOMAIN_SANDBOX = 'payment/payme/domain';
    const XML_CLIENT_ID = 'payment/payme/client_id';
    const XML_CLIENT_SECRET = 'payment/payme/client_sceret';
    const XML_SIGN_KEY_ID = 'payment/payme/sign_key_id';
    const XML_SIGN_KEY = 'payment/payme/sign_key';
    const XML_DEBUG_MODE = 'payment/payme/debug';
    const XML_REFUND_ALLOWED = 'payment/payme/refund';
    const XML_API_ENDPOINT = 'payment/payme/api_endpoint';
    
    /**
     * Check payme enable function
     *
     * @return boolean
     */
    public function isEnable()
    {
        return $this->scopeConfig->getValue(self::XML_IS_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->scopeConfig->getValue(self::XML_API_ENDPOINT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->scopeConfig->getValue(self::XML_CLIENT_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->scopeConfig->getValue(self::XML_CLIENT_SECRET, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getSignInKeyId()
    {
        return $this->scopeConfig->getValue(self::XML_SIGN_KEY_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getSignKey()
    {
        return $this->scopeConfig->getValue(self::XML_SIGN_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function isDebugMode()
    {
        return $this->scopeConfig->getValue(self::XML_DEBUG_MODE, ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * @return string
     */
    public function refundAllowed()
    {
        return $this->scopeConfig->getValue(self::XML_REFUND_ALLOWED, ScopeInterface::SCOPE_STORE);
    }
}