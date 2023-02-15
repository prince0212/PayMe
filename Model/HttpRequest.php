<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Model;

use Deloitte\PayMe\Helper\ApiConfig;

class HttpRequest
{
    /**
     * @var ApiConfig
     */
    public $apiConfig;

    /**
     * Initialization
     *
     * @param ApiConfig $apiConfig
     */
    public function __construct(
        ApiConfig $apiConfig
    )
    {
        $this->apiConfig = $apiConfig;
    }

    /**
     * Process the curl request
     *
     * @param mixed $payLoad
     * @param string $apiUrl
     * @param $headers
     * @return bool|string
     */
    public function processCurlRequest($payLoad, string $apiUrl, $headers)
    {
        // Initialize the curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiConfig->getDomain() . $apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        //checking the condition for the payload
        if (!empty($payLoad)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payLoad);
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);

        if ($result === false) {
            return curl_error($ch);
        }
        return $result;
    }
}
