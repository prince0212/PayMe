<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Helper;

use DateTime;
use DateTimeZone;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Algoritham extends AbstractHelper
{
    const API_VERSION = 0.12;

    private $uuid;

    /**
     * @param Context $context
     * @param Uuid $uuid
     */
    public function __construct(
        Context $context,
        Uuid    $uuid
    )
    {
        $this->uuid = $uuid;
        parent::__construct($context);
    }

    /**
     * @param $requestBody
     * @return string
     */
    public function createHashBody($requestBody)
    {
        $requestBodyEncoded = json_encode($requestBody, true);
        $strReplaced = str_replace(":", ": ", $requestBodyEncoded);
        $hashedData = hash('sha256', $strReplaced);
        $base64Encode = base64_encode(hex2bin($hashedData));
        return $base64Encode;
    }

    /**
     * @param $accessToken
     * @param $base64Encode
     * @return string
     * @throws \Exception
     */
    public function createSignatureBasedString($accessToken, $base64Encode)
    {
        return "(request-target): post /payments/paymentrequests" . "\n" . "api-version: " . self::API_VERSION . "\n" . "request-date-time: " . $this->getRequestTime() . "\n" . "trace-id: " . $this->uuid->v1() . "\n" . "authorization: Bearer " . $accessToken . "\n" . "accept: application/json" . "\n" . "content-type: application/json" . "\n" . "digest: SHA-256=" . $base64Encode;
    }

    /**
     * @return string
     */
    public function getRequestTime()
    {
        $now = DateTime::createFromFormat('U.u', microtime(true), new DateTimeZone("UTC"));
        return $now->format("Y-m-d\TH:i:s.u\Z");
    }

    /**
     * @param $signingKey
     * @return string
     */
    public function convertBase64($signingKey)
    {
        return bin2hex(base64_decode($signingKey));
    }

    /**
     * @param $hexBase64DecodeSigningKey
     * @param $signatureBasedString
     * @return string
     */
    public function hmacHashing($hexBase64DecodeSigningKey, $signatureBasedString)
    {
        $hashedSting = hash_hmac('sha256', utf8_encode(trim($signatureBasedString)), pack('H*',$hexBase64DecodeSigningKey));
        return base64_encode(hex2bin($hashedSting));
    }

    /**
     * @param $signingKeyId
     * @param $hmcHasing
     * @return string
     */
    public function formSignatureHeader($signingKeyId, $hmcHasing)
    {
        return 'keyId="'.$signingKeyId.'",algorithm='.'"hmac-sha256",headers="(request-target) api-version request-date-time trace-id authorization accept content-type digest",signature="'.$hmcHasing.'"';
    }
}
