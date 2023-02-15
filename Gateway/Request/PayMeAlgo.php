<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Gateway\Request;

use DateTime;
use DateTimeZone;
use Deloitte\PayMe\Helper\ApiConfig;
use Deloitte\PayMe\Helper\Uuid;
use Deloitte\PayMe\Model\HttpRequest;
use Exception;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;

class PayMeAlgo
{
    const API_VERSION = 0.12;

    /**
     * @var Uuid
     */
    private $uuid;
    
    /**
     * @var ApiConfig
     */
    private $apiConfig;
    
    /**
     * @var HttpRequest
     */
    private $httpRequest;
    
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var CreateRequest
     */
    private $createRequest;

    /**
     * @param Uuid $uuid
     * @param ApiConfig $apiConfig
     * @param HttpRequest $httpRequest
     * @param SerializerInterface $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Uuid                $uuid,
        ApiConfig           $apiConfig,
        HttpRequest         $httpRequest,
        SerializerInterface $jsonSerializer,
        CreateRequest       $createRequest,
        LoggerInterface     $logger
    )
    {
        $this->uuid = $uuid;
        $this->apiConfig = $apiConfig;
        $this->httpRequest = $httpRequest;
        $this->jsonSerializer = $jsonSerializer;
        $this->createRequest = $createRequest;
        $this->logger = $logger;
    }

    /**
     * 
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool|string|void
     * @throws Exception
     */
    public function buildQR($quote)
    {
        $tokenResponse = $this->getAccessToken();
        
        if(!empty($tokenResponse['errors'])) {
            throw new LocalizedException(__($tokenResponse['errors'][0]['errorDescription']));
        }
        
        if (empty($tokenResponse) || empty($tokenResponse['accessToken'])) {
            throw new LocalizedException(__('PayMe is down, please choose another payment method'));
        }
        
        $accessToken = $tokenResponse['accessToken'];
        //Custom Logs
        $this->logger->debug("PayMe: QR Code Quote ID:".$quote->getId()); 

        list($requestBody, $requestBodyEncoded) = $this->createRequest->createPaymentRequest($quote);
        $strReplaced = str_replace(":", ": ", $requestBodyEncoded);
        list($signatureHeader, $base64Encode, $traceId, $currentDateTime) = $this->preparePaymentRequest($requestBody, $accessToken);

        $headers = [
            'Api-Version: ' . self::API_VERSION,
            'Content-Type: application/json',
            'Accept: application/json',
            "Trace-ID: $traceId",
            "Request-Date-Time: $currentDateTime",
            "Signature: $signatureHeader",
            "Digest: SHA-256=$base64Encode",
            "Authorization: Bearer $accessToken"
        ];

        //Custom Logs
        $this->logger->debug("PayMe: Headers to pass:".print_r($headers,true)); 

        try {
            $response = $this->httpRequest->processCurlRequest($strReplaced, "/payments/paymentrequests", $headers);
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
        
        //Custom Logs
        $this->logger->debug("PayMe: Headers to pass:".print_r($response,true)); 
        $validateResponse = json_decode($response, true);
        if (!empty($validateResponse['errors'])) {
            throw new LocalizedException(__($validateResponse['errors'][0]['errorDescription']));
        }
        
        return $response;
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getAccessToken()
    {
        $payLoad = 'client_id=' . $this->apiConfig->getClientId() . '&client_secret=' . $this->apiConfig->getClientSecret();
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: Application/json',
            'Api-Version: 0.12',
        );
        $response = $this->httpRequest->processCurlRequest($payLoad, "/oauth2/token", $headers);
        $this->jsonSerializer->unserialize($response);
        $debugMode = $this->apiConfig->isDebugMode();
        $apiResponse = $this->jsonSerializer->unserialize($response);
        if (!empty($apiResponse['accessToken'])) {
            if ($debugMode) {
                $this->logger->info('Access Token API response: ' . $apiResponse['accessToken']);
            }
        } else {
            if ($debugMode) {
                $this->logger->info('Access Token API Error: ' . print_r($apiResponse, true));
            }
        }
        return $apiResponse;
    }

    /**
     * @param $requestBody
     * @param $accessToken
     * @return array
     * @throws Exception
     */
    public function preparePaymentRequest($requestBody, $accessToken)
    {
        $now = DateTime::createFromFormat('U.u', (string)microtime(true), new DateTimeZone("UTC"));
        $currentDateTime = $now->format("Y-m-d\TH:i:s.u\Z");
        $traceId = $this->uuid->v1();

        //#a: create digested hashed body
        $requestBodyEncoded = json_encode($requestBody);
        $strReplaced = str_replace(":", ": ", $requestBodyEncoded);
        $hashedData = hash('sha256', $strReplaced);
        $base64Encode = base64_encode(hex2bin($hashedData));

        //#b create signature base string
        $signatureBasedString = "(request-target): post /payments/paymentrequests" . "\n" . "api-version: " . self::API_VERSION . "\n" . "request-date-time: " . $currentDateTime . "\n" . "trace-id: " . $traceId . "\n" . "authorization: Bearer " . $accessToken . "\n" . "accept: application/json" . "\n" . "content-type: application/json" . "\n" . "digest: SHA-256=" . $base64Encode;

        //c. base 64 encoded hashed signining key
        $hexBase64DecodeSigningKey = bin2hex(base64_decode($this->apiConfig->getSignKey()));

        //d. hmac hashing the signature base string with base 64 encoded signing key
        $hashedSting = hash_hmac('sha256', utf8_encode(trim($signatureBasedString)), pack('H*', $hexBase64DecodeSigningKey));
        $hmcHasing = trim(base64_encode(hex2bin($hashedSting)));

        #e. signatureHeader
        $signatureHeader = 'keyId="' . $this->apiConfig->getSignInKeyId() . '",algorithm=' . '"hmac-sha256",headers="(request-target) api-version request-date-time trace-id authorization accept content-type digest",signature="' . $hmcHasing . '"';
        return [$signatureHeader, $base64Encode, $traceId, $currentDateTime];
    }
}
