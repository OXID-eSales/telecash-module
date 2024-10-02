<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Error
 */
class Error extends AbstractResponse
{
    public const SOAP_ERROR_SERVER = 'SOAP-ENV:Server';
    public const SOAP_ERROR_CLIENT = 'SOAP-ENV:Client';

    public const SOAP_CLIENT_ERROR_MERCHANT   = 'MerchantException';
    public const SOAP_CLIENT_ERROR_PROCESSING = 'ProcessingException';

    public const ERROR_TYPE_SERVER         = 'Server-Error';
    public const ERROR_TYPE_CLIENT         = 'Client-Error';
    public const ERROR_TYPE_NOT_SUCCESSFUL = 'Not successful';

    /** @var string $errorType */
    private string $errorType;
    /** @var string $errorMessage */
    private string $errorMessage;
    /** @var string $clientErrorType */
    private string $clientErrorType;
    /** @var string $clientErrorDetail */
    private string $clientErrorDetail;

    /**
     * @return string
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getClientErrorType(): string
    {
        return $this->clientErrorType;
    }

    /**
     * @return string
     */
    public function getClientErrorDetail(): string
    {
        return $this->clientErrorDetail;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $exceptionText = "SOAP Error: " . $this->errorType . " ";

        if ($this->errorType === self::ERROR_TYPE_SERVER) {
            $exceptionText .= '(' . $this->errorMessage . ')';
        } else {
            $exceptionText .= '(' . $this->clientErrorType . ': ' . $this->clientErrorDetail . ')';
        }

        return $exceptionText;
    }

    /**
     * @param \DOMDocument $document
     *
     * @return Error|null
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function createFromSoapFault(\DOMDocument $document): Error|null
    {
        $response = null;
        $errorElement = $document->getElementsByTagNameNS(OrderService::NAMESPACE_SOAP, 'Fault');

        if ($errorElement->length > 0) {
            $response = new Error();

            $response->errorMessage = '';
            $faultCode = $document->getElementsByTagName('faultcode');
            $item0     = $document->getElementsByTagName('faultstring')->item(0);
            if ($item0) {
                $response->errorMessage = trim((string)$item0->nodeValue);
            }
            unset($item0);

            $item0 = $faultCode->item(0);
            $faultCodeValue = '';
            if ($item0) {
                $faultCodeValue = trim((string)$item0->nodeValue);
            }
            switch ($faultCodeValue) {
                case self::SOAP_ERROR_SERVER:
                    $response->errorType = self::ERROR_TYPE_SERVER;
                    break;

                case self::SOAP_ERROR_CLIENT:
                    $response->errorType   = self::ERROR_TYPE_CLIENT;
                    $errorDetail = $document->getElementsByTagName('detail');

                    if (strpos($response->errorMessage, ':') !== false) {
                        $response->clientErrorType = substr(
                            $response->errorMessage,
                            0,
                            strpos($response->errorMessage, ':')
                        );
                    } else {
                        $response->clientErrorType = $response->errorMessage;
                    }

                    switch ($response->clientErrorType) {
                        case self::SOAP_CLIENT_ERROR_MERCHANT:
                            $item0 = $errorDetail->item(0);
                            if ($item0) {
                                $response->clientErrorDetail = trim((string)$item0->nodeValue);
                            }
                            break;

                        case self::SOAP_CLIENT_ERROR_PROCESSING:
                            $response->clientErrorDetail = $response->firstElementByTagNSString(
                                $document,
                                OrderService::NAMESPACE_N3,
                                'ErrorMessage'
                            );
                            ;
                            break;

                        default:
                            throw new \Exception(
                                "Undefined SOAP Client Exception: " .
                                $response->clientErrorType .
                                ' (Complete SOAP Fault: ' .
                                $document->saveXML() .
                                ')'
                            );
                    }
                    break;

                default:
                    throw new \Exception("Undefined SOAP Error: (" . $document->saveXML() . ")");
            }
        }

        return $response;
    }
}
